<?php

namespace App\Http\Controllers;

use App\Models\Divisi;
use App\Models\Jabatan;
use App\Models\Karyawan;
use Illuminate\Http\Request;

class KaryawanController extends Controller
{
    public function index()
    {
        $karyawans = Karyawan::with(['divisi', 'jabatan', 'user'])->orderBy('nama')->get();
        $divisis   = Divisi::orderBy('nama_divisi')->get()->unique('nama_divisi');
        $jabatans  = Jabatan::orderBy('nama_jabatan')->get()->unique('nama_jabatan');

        return view('karyawan.index', compact('karyawans', 'divisis', 'jabatans'));
    }

    /**
     * Menampilkan halaman form tambah karyawan.
     */
    public function create()
    {
        $divisis  = Divisi::orderBy('nama_divisi')->get()->unique('nama_divisi');
        $jabatans = Jabatan::orderBy('nama_jabatan')->get()->unique('nama_jabatan');

        return view('karyawan.create', compact('divisis', 'jabatans'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama'          => 'required|string|max:150',
            'email'         => 'required|email|unique:karyawans,email',
            'no_pegawai'    => 'nullable|string|max:50|unique:karyawans,no_pegawai',
            'no_hp'         => 'nullable|string|max:20',
            'tanggal_lahir' => 'nullable|date',
            'alamat'        => 'nullable|string',
            'divisi_id'     => 'nullable|exists:divisis,id',
            'jabatan_id'    => 'nullable|exists:jabatans,id',
            'status'        => 'required|in:Aktif,Nonaktif',
        ]);

        Karyawan::create($data);

        return redirect()->route('karyawan.index')->with('success', 'Data karyawan berhasil ditambahkan.');
    }

    public function update(Request $request, Karyawan $karyawan)
    {
        $data = $request->validate([
            'nama'          => 'required|string|max:150',
            'email'         => 'required|email|unique:karyawans,email,' . $karyawan->id,
            'no_pegawai'    => 'nullable|string|max:50|unique:karyawans,no_pegawai,' . $karyawan->id,
            'no_hp'         => 'nullable|string|max:20',
            'tanggal_lahir' => 'nullable|date',
            'alamat'        => 'nullable|string',
            'divisi_id'     => 'nullable|exists:divisis,id',
            'jabatan_id'    => 'nullable|exists:jabatans,id',
            'status'        => 'required|in:Aktif,Nonaktif',
        ]);

        $karyawan->update($data);

        return back()->with('success', 'Data karyawan berhasil diperbarui.');
    }

    public function destroy(Karyawan $karyawan)
    {
        $karyawan->delete();

        return back()->with('success', 'Data karyawan berhasil dihapus.');
    }

    /**
     * Export ke Excel (.xls berisi tabel HTML — dibaca rapi oleh Excel
     * di semua pengaturan regional, tidak tergantung delimiter CSV).
     * Tidak butuh package composer tambahan.
     */
    public function exportExcel()
    {
        $karyawans = Karyawan::with(['divisi', 'jabatan'])->orderBy('nama')->get();

        $filename = 'data-karyawan-' . now()->format('Y-m-d') . '.xls';

        $html = '<html xmlns:x="urn:schemas-microsoft-com:office:excel">';
        $html .= '<head><meta charset="UTF-8">';
        $html .= '<!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet>';
        $html .= '<x:Name>Data Karyawan</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions>';
        $html .= '</x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]-->';
        $html .= '<style>
                    table { border-collapse: collapse; font-family: "Segoe UI", Calibri, Arial, sans-serif; font-size: 20px; }
                    th, td {
                        border: 1px solid #999;
                        padding: 20px 24px;
                        text-align: left;
                        vertical-align: middle;
                        white-space: normal;
                        mso-number-format: "\@";
                    }
                    th {
                        background: #172554;
                        color: #ffffff;
                        font-weight: bold;
                        font-size: 22px;
                        text-align: center;
                    }
                    td { height: 48px; }
                    col { mso-width-source: userset; }
                  </style></head><body>';

        // Lebar kolom pakai atribut HTML "width" (dalam pixel) — ini yang dipatuhi Excel,
        // CSS width saja sering diabaikan / di-autofit ulang oleh Excel.
        $html .= '<table>';
        $html .= '<colgroup>'
            . '<col width="100">'   // No
            . '<col width="260">'  // No Pegawai
            . '<col width="420">'  // Nama
            . '<col width="480">'  // Email
            . '<col width="300">'  // Bagian
            . '<col width="300">'  // Jabatan
            . '<col width="200">'  // Status
            . '<col width="280">'  // No HP
            . '</colgroup>';

        $html .= '<thead><tr style="height:50px; mso-height-source:userset;">
                    <th>No</th><th>No Pegawai</th><th>Nama</th><th>Email</th>
                    <th>Bagian</th><th>Jabatan</th><th>Status</th><th>No HP</th>
                    <th>Tanggal Lahir</th><th>Alamat</th>
                  </tr></thead><tbody>';

        foreach ($karyawans as $i => $k) {
            $html .= '<tr style="height:60px; mso-height-source:userset;">'
                . '<td style="text-align:center;">' . ($i + 1) . '</td>'
                . '<td>' . e($k->no_pegawai ?? '-') . '</td>'
                . '<td>' . e($k->nama) . '</td>'
                . '<td>' . e($k->email) . '</td>'
                . '<td>' . e($k->divisi->nama_divisi ?? '-') . '</td>'
                . '<td>' . e($k->jabatan->nama_jabatan ?? '-') . '</td>'
                . '<td style="text-align:center;">' . e($k->status) . '</td>'
                . '<td>' . e($k->no_hp ?? '-') . '</td>'
                . '<td>' . ($k->tanggal_lahir ? $k->tanggal_lahir->format('Y-m-d') : '-') . '</td>'
                . '<td>' . e($k->alamat ?? '-') . '</td>'
                . '</tr>';
        }

        $html .= '</tbody></table></body></html>';

        return response($html, 200, [
            'Content-Type'        => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ]);
    }

    /**
     * Halaman cetak (untuk di-print / "Save as PDF" lewat browser).
     * Tidak butuh package composer tambahan (dompdf, dll).
     */
    public function exportPdf()
    {
        $karyawans = Karyawan::with(['divisi', 'jabatan'])->orderBy('nama')->get();

        $html = view('karyawan.print', compact('karyawans'))->render();

        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        return response($dompdf->output(), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="data-karyawan-ibp.pdf"',
        ]);
    }
}