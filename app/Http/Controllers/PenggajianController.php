<?php

namespace App\Http\Controllers;

use App\Models\Divisi;
use App\Models\Karyawan;
use App\Models\Penggajian;
use App\Models\PenggunaKaryawan;
use Illuminate\Http\Request;

class PenggajianController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $month = $request->input('bulan', now()->month);
        $year  = $request->input('tahun', now()->year);

        $linked = PenggunaKaryawan::where('username', $user->username)
            ->with('karyawan.divisi', 'karyawan.jabatan')
            ->first();
        $currentKaryawan = $linked?->karyawan;

        // ===== Karyawan biasa: cuma liat slip gaji miliknya sendiri =====
        if ($user->hasRole('User')) {
            $query = Penggajian::where('karyawan_id', $currentKaryawan->id ?? 0);

            if ($request->filled('bulan')) {
                $query->where('bulan', $request->bulan);
            }
            if ($request->filled('tahun')) {
                $query->where('tahun', $request->tahun);
            }

            $items = $query->orderByDesc('tahun')->orderByDesc('bulan')->paginate(10)->withQueryString();

            $slipBulanIni = Penggajian::where('karyawan_id', $currentKaryawan->id ?? 0)
                ->where('bulan', $month)
                ->where('tahun', $year)
                ->first();

            return view('penggajian.slip', compact('items', 'currentKaryawan', 'slipBulanIni', 'month', 'year'))
                ->with('pageTitle', 'Slip Gaji');
        }

        // ===== Superadmin / role lain: kelola semua =====
        $query = Penggajian::with('karyawan.divisi', 'karyawan.jabatan')
            ->where('bulan', $month)
            ->where('tahun', $year);

        if ($request->filled('karyawan_id')) {
            $query->where('karyawan_id', $request->karyawan_id);
        }

        if ($request->filled('divisi_id')) {
            $query->whereHas('karyawan.divisi', fn($q) => $q->where('id', $request->divisi_id));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $perPage = $request->input('per_page', 10);
        $items = $query->paginate($perPage)->withQueryString();

        $totalGaji = Penggajian::where('bulan', $month)->where('tahun', $year)->sum('total');
        $countGenerated = Penggajian::where('bulan', $month)->where('tahun', $year)->where('status', 'Generated')->count();
        $countPending = Penggajian::where('bulan', $month)->where('tahun', $year)->where('status', 'Pending')->count();
        $totalKaryawan = Karyawan::count();

        $karyawans = Karyawan::orderBy('nama')->get();
        $divisis = Divisi::orderBy('nama_divisi')->get();

        return view('penggajian.index', compact('items', 'karyawans', 'divisis', 'month', 'year', 'totalGaji', 'countGenerated', 'countPending', 'totalKaryawan'))->with('pageTitle', 'Penggajian');
    }

    /**
     * Menampilkan halaman Preview Data Penggajian sebelum generate final.
     */
    public function preview(Request $request)
    {
        if (auth()->user()->hasRole('User')) {
            abort(403);
        }

        $bulan = $request->input('bulan', now()->month);
        $tahun = $request->input('tahun', now()->year);
        $tipe  = $request->input('tipe_generate', 'semua');
        $karyawanId = $request->input('karyawan_id');

        $query = Karyawan::query();

        // Jika opsi 'Satu Karyawan' dipilih pada Modal Form
        if ($tipe === 'satu' && $karyawanId) {
            $query->where('id', $karyawanId);
        }

        $karyawans = $query->orderBy('nama')->get();

        $existingGaji = Penggajian::where('bulan', $bulan)
            ->where('tahun', $tahun)
            ->get()
            ->keyBy('karyawan_id');

        return view('penggajian.preview', compact('karyawans', 'bulan', 'tahun', 'existingGaji'))
            ->with('pageTitle', 'Preview Data Penggajian');
    }

    /**
     * Menyimpan data penggajian secara permanen setelah diklik "Generate Final".
     */
    public function store(Request $request)
    {
        if (auth()->user()->hasRole('User')) {
            abort(403);
        }

        $bulan = $request->input('bulan', now()->month);
        $tahun = $request->input('tahun', now()->year);
        $gajiData = $request->input('gaji', []);

        $savedCount = 0;

        foreach ($gajiData as $row) {
            $karyawanId = $row['karyawan_id'] ?? null;
            if (!$karyawanId) continue;

            $gajiPokok       = floatval($row['gaji_pokok'] ?? 0);
            $makanTransport  = floatval($row['makan_transport'] ?? 0);
            $tjJabatan       = floatval($row['tj_jabatan'] ?? 0);
            $bonus           = floatval($row['bonus'] ?? 0);
            $thr             = floatval($row['thr'] ?? 0);

            $potDplk         = floatval($row['pot_dplk'] ?? 0);
            $potKoperasi     = floatval($row['pot_koperasi'] ?? 0);
            $potAbsensi      = floatval($row['pot_absensi'] ?? 0);
            $potLainnya      = floatval($row['pot_lainnya'] ?? 0);

            // Perhitungan Otomatis BPJS Kesehatan (1%) & Ketenagakerjaan (3%) dari Gaji Pokok
            $bpjsKesehatan = $gajiPokok * 0.01;
            $bpjsTk        = $gajiPokok * 0.03;

            // Total Tunjangan & Potongan
            $totalTunjangan = $makanTransport + $tjJabatan + $bonus + $thr;
            $totalPotongan  = $potDplk + $potKoperasi + $potAbsensi + $potLainnya + $bpjsKesehatan + $bpjsTk;
            $totalGajiNetto = $gajiPokok + $totalTunjangan - $totalPotongan;

            Penggajian::updateOrCreate(
                [
                    'karyawan_id' => $karyawanId,
                    'bulan'       => $bulan,
                    'tahun'       => $tahun,
                ],
                [
                    'gaji_pokok'      => $gajiPokok,
                    'makan_transport' => $makanTransport,
                    'tj_jabatan'      => $tjJabatan,
                    'bonus'           => $bonus,
                    'thr'             => $thr,
                    'pot_bpjs'        => $bpjsKesehatan + $bpjsTk,
                    'pot_dplk'        => $potDplk,
                    'pot_koperasi'    => $potKoperasi,
                    'pot_absensi'     => $potAbsensi,
                    'pot_lainnya'     => $potLainnya,
                    'tunjangan'       => $totalTunjangan,
                    'potongan'        => $totalPotongan,
                    'total'           => $totalGajiNetto,
                    'status'          => 'Generated',
                ]
            );

            $savedCount++;
        }

        return redirect()->route('penggajian.index', ['bulan' => $bulan, 'tahun' => $tahun])
            ->with('success', "Berhasil melakukan Generate Final untuk $savedCount karyawan.");
    }

    public function generate(Request $request)
    {
        if (auth()->user()->hasRole('User')) {
            abort(403);
        }

        $month = $request->input('bulan', now()->month);
        $year  = $request->input('tahun', now()->year);

        $karyawans = Karyawan::all();
        $created = 0;
        foreach ($karyawans as $k) {
            $exists = Penggajian::where('karyawan_id', $k->id)->where('bulan', $month)->where('tahun', $year)->exists();
            if ($exists) continue;

            $gajiPokok = $k->gaji_pokok ?? 0;
            $tunjangan = $k->tunjangan ?? 0;
            $potongan  = 0;
            $total      = $gajiPokok + $tunjangan - $potongan;

            Penggajian::create([
                'karyawan_id' => $k->id,
                'bulan'       => $month,
                'tahun'       => $year,
                'gaji_pokok'  => $gajiPokok,
                'tunjangan'   => $tunjangan,
                'potongan'    => $potongan,
                'total'       => $total,
                'status'      => 'Pending',
            ]);
            $created++;
        }

        return back()->with('success', "Generate selesai. $created data dibuat.");
    }

    public function reset(Request $request)
    {
        if (auth()->user()->hasRole('User')) {
            abort(403);
        }

        $month = $request->input('bulan', now()->month);
        $year  = $request->input('tahun', now()->year);

        $deleted = Penggajian::where('bulan', $month)->where('tahun', $year)->delete();

        return back()->with('success', "Reset selesai. $deleted data dihapus.");
    }

    public function destroy($id)
    {
        if (auth()->user()->hasRole('User')) {
            abort(403);
        }

        $penggajian = Penggajian::findOrFail($id);
        $penggajian->delete();

        return back()->with('success', 'Data penggajian berhasil dihapus.');
    }

    public function show($id)
    {
        $penggajian = Penggajian::with('karyawan.divisi', 'karyawan.jabatan')->findOrFail($id);
        return view('penggajian.show', compact('penggajian'))->with('pageTitle', 'Lihat Slip Gaji');
    }

    public function exportPdfItem($id)
    {
        $penggajian = Penggajian::with('karyawan.divisi', 'karyawan.jabatan')->findOrFail($id);

        $user = auth()->user();
        if ($user->hasRole('User')) {
            $linked = PenggunaKaryawan::where('username', $user->username)->first();
            if (!$linked || $linked->karyawan_id != $penggajian->karyawan_id) {
                abort(403, 'Anda tidak memiliki hak akses untuk mengunduh slip gaji ini.');
            }
        }

        $namaKaryawan = str_replace(' ', '-', strtolower($penggajian->karyawan->nama ?? 'karyawan'));
        $filename = "slip-gaji-{$namaKaryawan}-{$penggajian->bulan}-{$penggajian->tahun}.pdf";

        // Pass isPdf = true so the Cetak / Save PDF button is excluded inside PDF file
        $html = view('penggajian.print_slip', ['penggajian' => $penggajian, 'isPdf' => true])->render();

        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return response($dompdf->output(), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function exportWordItem($id)
    {
        $penggajian = Penggajian::with('karyawan.divisi', 'karyawan.jabatan')->findOrFail($id);
        $namaKaryawan = str_replace(' ', '-', strtolower($penggajian->karyawan->nama ?? 'karyawan'));
        $filename = "slip-gaji-{$namaKaryawan}-{$penggajian->bulan}-{$penggajian->tahun}.doc";

        // Render identical official slip gaji layout as PDF for Word document
        $html = view('penggajian.print_slip', ['penggajian' => $penggajian, 'isPdf' => true])->render();

        return response($html, 200, [
            'Content-Type'        => 'application/msword; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function exportExcelItem($id)
    {
        $penggajian = Penggajian::with('karyawan.divisi', 'karyawan.jabatan')->findOrFail($id);
        $namaKaryawan = str_replace(' ', '-', strtolower($penggajian->karyawan->nama ?? 'karyawan'));
        $filename = "slip-gaji-{$namaKaryawan}-{$penggajian->bulan}-{$penggajian->tahun}.xls";

        return $this->generateExcelResponse([$penggajian], $filename);
    }

    public function exportExcel(Request $request)
    {
        $month = $request->input('bulan', now()->month);
        $year  = $request->input('tahun', now()->year);

        $items = Penggajian::with('karyawan.divisi', 'karyawan.jabatan')
            ->where('bulan', $month)
            ->where('tahun', $year)
            ->get();

        $filename = 'rekap-penggajian-' . $month . '-' . $year . '.xls';

        return $this->generateExcelResponse($items, $filename);
    }

    private function generateExcelResponse($items, $filename)
    {
        $html = '<html xmlns:x="urn:schemas-microsoft-com:office:excel">';
        $html .= '<head><meta charset="UTF-8">';
        $html .= '<!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet>';
        $html .= '<x:Name>Data Penggajian</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions>';
        $html .= '</x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]-->';
        $html .= '<style>
                    table { border-collapse: collapse; font-family: "Segoe UI", Arial, sans-serif; font-size: 12px; }
                    th, td { border: 1px solid #000; padding: 6px 10px; text-align: left; vertical-align: middle; }
                    th { background: #ffffff; color: #000000; font-weight: bold; text-align: center; }
                    .num { text-align: right; }
                    .center { text-align: center; }
                  </style></head><body>';
        $html .= '<table><thead><tr>
                    <th>No</th>
                    <th>Nama Karyawan</th>
                    <th>Divisi</th>
                    <th>Bulan</th>
                    <th>Tahun</th>
                    <th>Gaji Pokok</th>
                    <th>Tunjangan</th>
                    <th>Bonus</th>
                    <th>Potongan</th>
                    <th>Total Gaji</th>
                    <th>Status</th>
                  </tr></thead><tbody>';

        foreach ($items as $i => $row) {
            $gajiPokok = $row->gaji_pokok ?? 0;
            $tunjangan = $row->makan_transport ?? $row->tunjangan ?? 0;
            $bonus = $row->bonus ?? 0;
            $potongan = $row->potongan ?? 0;
            $totalGaji = $row->total ?? ($gajiPokok + $tunjangan + $bonus - $potongan);

            $html .= '<tr>'
                . '<td class="center">' . ($i + 1) . '</td>'
                . '<td>' . e(strtoupper($row->karyawan->nama ?? '-')) . '</td>'
                . '<td>' . e(strtoupper(optional($row->karyawan->divisi)->nama_divisi ?? 'OPERASIONAL')) . '</td>'
                . '<td class="center">' . e($row->bulan) . '</td>'
                . '<td class="center">' . e($row->tahun) . '</td>'
                . '<td class="num">Rp' . number_format($gajiPokok, 0, ',', '.') . '</td>'
                . '<td class="num">Rp' . number_format($tunjangan, 0, ',', '.') . '</td>'
                . '<td class="num">Rp' . number_format($bonus, 0, ',', '.') . '</td>'
                . '<td class="num">Rp' . number_format($potongan, 0, ',', '.') . '</td>'
                . '<td class="num">Rp' . number_format($totalGaji, 0, ',', '.') . '</td>'
                . '<td class="center">' . e($row->status) . '</td>'
                . '</tr>';
        }

        $html .= '</tbody></table></body></html>';

        return response($html, 200, [
            'Content-Type'        => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}