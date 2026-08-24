<?php

namespace App\Http\Controllers;

use App\Models\KegiatanHarian;
use App\Models\Karyawan;
use App\Models\PenggunaKaryawan;
use Illuminate\Http\Request;

class KegiatanHarianController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        $linked = PenggunaKaryawan::where('username', $user->username)
            ->with('karyawan.divisi', 'karyawan.jabatan')
            ->first();
        $currentKaryawan = $linked?->karyawan;

        $query = KegiatanHarian::with('karyawan');

        // ===== Karyawan biasa: cuma liat laporan kegiatan punya sendiri =====
        if ($user->hasRole('User')) {
            $query->when($currentKaryawan, fn ($q) => $q->where('karyawan_id', $currentKaryawan->id))
                  ->when(!$currentKaryawan, fn ($q) => $q->whereRaw('1 = 0'));

            if ($request->filled('tanggal')) {
                $query->whereDate('tanggal', $request->tanggal);
            }

            $kegiatan = $query->orderBy('tanggal', 'desc')->paginate(10)->withQueryString();

            return view('kegiatan_harian.index', compact('kegiatan', 'currentKaryawan'))
                ->with('pageTitle', 'Kegiatan Harian')
                ->with('isAdminView', false);
        }

        // ===== Superadmin / role lain: liat & filter semua karyawan =====
        if ($request->filled('karyawan_id')) {
            $query->where('karyawan_id', $request->karyawan_id);
        }
        if ($request->filled('tanggal')) {
            $query->whereDate('tanggal', $request->tanggal);
        }

        $kegiatan = $query->orderBy('tanggal', 'desc')->paginate(10)->withQueryString();
        $karyawans = Karyawan::orderBy('nama')->get();

        return view('kegiatan_harian.index', compact('kegiatan', 'karyawans', 'currentKaryawan'))
            ->with('pageTitle', 'Kegiatan Harian')
            ->with('isAdminView', true);
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        if ($user->hasRole('User')) {
            // Karyawan cuma boleh lapor buat dirinya sendiri —
            // karyawan_id diambil dari akun yang login, bukan dari form.
            $linked = PenggunaKaryawan::where('username', $user->username)->with('karyawan')->first();

            if (!$linked?->karyawan) {
                return back()->with('error', 'Data karyawan untuk akun Anda tidak ditemukan. Hubungi admin.');
            }

            $data = $request->validate([
                'tanggal'  => 'required|date',
                'kegiatan' => 'required|string',
            ]);

            $data['karyawan_id'] = $linked->karyawan->id;
        } else {
            $data = $request->validate([
                'karyawan_id' => 'required|exists:karyawans,id',
                'tanggal'     => 'required|date',
                'kegiatan'    => 'required|string',
            ]);
        }

        KegiatanHarian::create($data);

        return back()->with('success', 'Laporan kegiatan berhasil dibuat.');
    }

    public function update(Request $request, KegiatanHarian $kegiatan)
    {
        $this->denyIfNotOwnerAdmin($kegiatan);

        $data = $request->validate([
            'karyawan_id' => 'required|exists:karyawans,id',
            'tanggal' => 'required|date',
            'kegiatan' => 'required|string',
        ]);

        $kegiatan->update($data);

        return back()->with('success', 'Laporan kegiatan berhasil diperbarui.');
    }

    public function destroy(KegiatanHarian $kegiatan)
    {
        $this->denyIfNotOwnerAdmin($kegiatan);

        $kegiatan->delete();
        return back()->with('success', 'Laporan kegiatan berhasil dihapus.');
    }

    /**
     * Karyawan (role User) cuma boleh update/hapus laporan miliknya sendiri.
     * Superadmin/role lain bebas.
     */
    protected function denyIfNotOwnerAdmin(KegiatanHarian $kegiatan): void
    {
        $user = auth()->user();

        if (!$user->hasRole('User')) {
            return;
        }

        $linked = PenggunaKaryawan::where('username', $user->username)->first();

        if (!$linked || $kegiatan->karyawan_id !== $linked->karyawan_id) {
            abort(403, 'Anda tidak punya akses ke laporan kegiatan ini.');
        }
    }
}