<?php

namespace App\Http\Controllers;

use App\Models\Divisi;
use App\Models\Karyawan;
use App\Models\PengajuanCuti;
use App\Models\PenggunaKaryawan;
use Illuminate\Http\Request;

class CutiController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        $linked = PenggunaKaryawan::where('username', $user->username)
            ->with('karyawan')
            ->first();
        $currentKaryawan = $linked?->karyawan;

        // ===== Karyawan biasa: cuma liat & ajukan cuti punya sendiri =====
        if ($user->hasRole('User')) {
            $query = PengajuanCuti::with('karyawan')
                ->when($currentKaryawan, fn ($q) => $q->where('karyawan_id', $currentKaryawan->id))
                ->when(!$currentKaryawan, fn ($q) => $q->whereRaw('1 = 0')); // belum terhubung ke karyawan -> kosong

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            $items = (clone $query)->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

            $baseCount = fn () => PengajuanCuti::when($currentKaryawan, fn ($q) => $q->where('karyawan_id', $currentKaryawan->id))
                ->when(!$currentKaryawan, fn ($q) => $q->whereRaw('1 = 0'));

            $total    = $baseCount()->count();
            $waiting  = (clone $baseCount())->where('status', 'Menunggu')->count();
            $approved = (clone $baseCount())->where('status', 'Disetujui')->count();
            $rejected = (clone $baseCount())->where('status', 'Ditolak')->count();

            return view('cuti.ajukan', compact(
                'items', 'currentKaryawan', 'total', 'waiting', 'approved', 'rejected'
            ))->with('pageTitle', 'Pengajuan Cuti');
        }

        // ===== Superadmin / role lain: kelola semua pengajuan =====
        $query = PengajuanCuti::with('karyawan');

        if ($request->filled('karyawan_id')) {
            $query->where('karyawan_id', $request->karyawan_id);
        }
        if ($request->filled('divisi_id')) {
            $query->whereHas('karyawan.divisi', fn($q) => $q->where('id', $request->divisi_id));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $items = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();
        $karyawans = Karyawan::orderBy('nama')->get();
        $divisis = Divisi::orderBy('nama_divisi')->get();

        $total = PengajuanCuti::count();
        $waiting = PengajuanCuti::where('status', 'Menunggu')->count();
        $approved = PengajuanCuti::where('status', 'Disetujui')->count();
        $rejected = PengajuanCuti::where('status', 'Ditolak')->count();

        return view('cuti.index', compact('items', 'karyawans', 'divisis', 'total', 'waiting', 'approved', 'rejected'))
            ->with('pageTitle', 'Manajemen Cuti');
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        if ($user->hasRole('User')) {
            // Karyawan cuma boleh ngajuin cuti buat dirinya sendiri —
            // karyawan_id TIDAK diambil dari form, tapi dari akun yang login.
            $linked = PenggunaKaryawan::where('username', $user->username)->with('karyawan')->first();

            if (!$linked?->karyawan) {
                return back()->with('error', 'Data karyawan untuk akun Anda tidak ditemukan. Hubungi admin.');
            }

            $data = $request->validate([
                'tanggal_mulai'   => 'required|date',
                'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
                'alasan'          => 'nullable|string',
            ]);

            $data['karyawan_id'] = $linked->karyawan->id;
        } else {
            $data = $request->validate([
                'karyawan_id'     => 'required|exists:karyawans,id',
                'tanggal_mulai'   => 'required|date',
                'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
                'alasan'          => 'nullable|string',
            ]);
        }

        $data['durasi'] = \Carbon\Carbon::parse($data['tanggal_mulai'])->diffInDays(\Carbon\Carbon::parse($data['tanggal_selesai'])) + 1;
        $data['status'] = 'Menunggu';

        PengajuanCuti::create($data);

        return back()->with('success', 'Pengajuan cuti berhasil dibuat.');
    }

    public function update(Request $request, PengajuanCuti $item)
    {
        $this->denyIfNotOwnerAdmin($item);

        $data = $request->validate([
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'alasan' => 'nullable|string',
            'status' => 'required|string',
        ]);

        $data['durasi'] = \Carbon\Carbon::parse($data['tanggal_mulai'])->diffInDays(\Carbon\Carbon::parse($data['tanggal_selesai'])) + 1;

        $item->update($data);

        return back()->with('success','Pengajuan cuti berhasil diperbarui.');
    }

    /**
     * Tampilkan surat cuti untuk dicetak / diunduh PDF
     */
    public function surat(PengajuanCuti $item)
    {
        $this->denyIfNotOwner($item);

        return view('cuti.surat', compact('item'))
            ->with('pageTitle', 'Surat Izin Cuti');
    }

    /**
     * Download surat cuti sebagai PDF
     */
    public function pdf(PengajuanCuti $item)
    {
        $this->denyIfNotOwner($item);

        $namaKaryawan = str_replace(' ', '-', strtolower($item->karyawan->nama ?? 'karyawan'));
        $filename = "surat-cuti-{$namaKaryawan}-" . \Carbon\Carbon::parse($item->tanggal_mulai)->format('d-m-Y') . ".pdf";

        $html = view('cuti.pdf_surat', ['item' => $item])->render();

        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return response($dompdf->output(), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Setujui pengajuan cuti — cuma boleh superadmin/non-User.
     */
    public function approve(PengajuanCuti $item)
    {
        if (auth()->user()->hasRole('User')) {
            abort(403);
        }

        $item->update(['status' => 'Disetujui']);

        return back()->with('success', 'Pengajuan cuti ' . ($item->karyawan->nama ?? '') . ' disetujui.');
    }

    /**
     * Tolak pengajuan cuti — cuma boleh superadmin/non-User.
     */
    public function reject(PengajuanCuti $item)
    {
        if (auth()->user()->hasRole('User')) {
            abort(403);
        }

        $item->update(['status' => 'Ditolak']);

        return back()->with('success', 'Pengajuan cuti ' . ($item->karyawan->nama ?? '') . ' ditolak.');
    }

    public function destroy(PengajuanCuti $item)
    {
        $this->denyIfNotOwnerAdmin($item);

        $item->delete();
        return back()->with('success','Pengajuan cuti berhasil dihapus.');
    }

    /**
     * Karyawan (role User) cuma boleh update/hapus pengajuan miliknya sendiri,
     * dan cuma kalau masih berstatus "Menunggu" (belum diproses admin).
     * Superadmin/role lain bebas.
     */
    protected function denyIfNotOwnerAdmin(PengajuanCuti $item): void
    {
        $user = auth()->user();

        if (!$user->hasRole('User')) {
            return; // admin/role lain, bebas
        }

        $linked = PenggunaKaryawan::where('username', $user->username)->first();

        if (!$linked || $item->karyawan_id !== $linked->karyawan_id) {
            abort(403, 'Anda tidak punya akses ke pengajuan cuti ini.');
        }

        if ($item->status !== 'Menunggu') {
            abort(403, 'Pengajuan yang sudah diproses tidak bisa diubah/dihapus.');
        }
    }

    /**
     * Cuma ngecek ownership saja, bebas status apa pun (untuk lihat surat/pdf).
     */
    protected function denyIfNotOwner(PengajuanCuti $item): void
    {
        $user = auth()->user();
        if (!$user->hasRole('User')) return;

        $linked = PenggunaKaryawan::where('username', $user->username)->first();
        if (!$linked || $item->karyawan_id !== $linked->karyawan_id) {
            abort(403, 'Anda tidak punya akses ke pengajuan cuti ini.');
        }
    }
}