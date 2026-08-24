<?php

namespace App\Http\Controllers;

use App\Models\PenggunaKaryawan;
use Illuminate\Http\Request;

class PenggunaKaryawanController extends Controller
{
    public function index(Request $request)
    {
        $penggunas = PenggunaKaryawan::with('karyawan')
            ->when($request->filled('cari'), function ($q) use ($request) {
                $q->where('username', 'like', '%' . $request->query('cari') . '%');
            })
            ->when($request->filled('role'), function ($q) use ($request) {
                $q->where('role', $request->query('role'));
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('pengguna.index', compact('penggunas'));
    }

    /**
     * Halaman "Tambah User" â€” dibuat langsungoleh superadmin.
     * Akun berdiri sendiri (username, password,role), belum tentu
     * langsung terhubung ke data Karyawan.
     */
    public function create()
    {
        return view('pengguna.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'username' => 'required|string|max:50|unique:pengguna_karyawans,username',
            'password' => 'required|string|min:6',
            'role'     => 'required|in:user,superadmin',
        ]);

        // Password disimpan apa adanya (tanpa hashing), mengikuti
        // implementasi login yang sudah ada.
        PenggunaKaryawan::create($data);

        return redirect()->route('pengguna.index')->with('success', 'Akun pengguna berhasil dibuat.');
    }

    public function update(Request $request, PenggunaKaryawan $pengguna)
    {
        $data = $request->validate([
            'username' => 'required|string|max:50|unique:pengguna_karyawans,username,' . $pengguna->id,
            'role'     => 'required|in:user,superadmin',
            'password' => 'nullable|string|min:6',
        ]);

        if (empty($data['password'])) {
            unset($data['password']); // kosong = tidak diganti
        }

        $pengguna->update($data);

        return back()->with('success', 'Akun pengguna berhasil diperbarui.');
    }

    public function destroy(PenggunaKaryawan $pengguna)
    {
        $pengguna->delete();

        return back()->with('success', 'Akun pengguna berhasil dihapus.');
    }

    /**
     * Export ke Excel (.xls berisi tabel HTML â€” dibaca rapi oleh Excel
     * di semua pengaturan regional, tidak tergantung delimiter CSV).
     * Tidak butuh package composer tambahan.
     */
    public function exportExcel(Request $request)
    {
        $penggunas = PenggunaKaryawan::with('karyawan')
            ->when($request->filled('cari'), fn ($q) => $q->where('username', 'like', '%' . $request->query('cari') . '%'))
            ->when($request->filled('role'), fn ($q) => $q->where('role', $request->query('role')))
            ->latest()
            ->get();

        $filename = 'data-pengguna-' . now()->format('Y-m-d') . '.xls';

        $html = '<html xmlns:x="urn:schemas-microsoft-com:office:excel">';
        $html .= '<head><meta charset="UTF-8">';
        $html .= '<!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet>';
        $html .= '<x:Name>Data Pengguna</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions>';
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

        $html .= '<table>';
        $html .= '<colgroup>'
            . '<col width="80">'   // No
            . '<col width="280">' // Username
            . '<col width="380">' // Nama Karyawan
            . '<col width="200">' // Role
            . '<col width="200">' // Dibuat
            . '</colgroup>';

        $html .= '<thead><tr style="height:50px;mso-height-source:userset;">
                    <th>No</th><th>Username</th><th>Nama Karyawan</th><th>Role</th><th>Dibuat</th>
                  </tr></thead><tbody>';

        foreach ($penggunas as $i => $p) {
            $html .= '<tr style="height:60px; mso-height-source:userset;">'
                . '<td style="text-align:center;">' . ($i + 1) . '</td>'
                . '<td>' . e($p->username) . '</td>'
                . '<td>' . e($p->karyawan->nama ?? '-') . '</td>'
                . '<td style="text-align:center;">' . e(ucfirst($p->role ?? 'user')) . '</td>'
                . '<td style="text-align:center;">' . e($p->created_at->format('d/m/Y')) . '</td>'
                . '</tr>';
        }

        $html .= '</tbody></table></body></html>';

        return response($html, 200, [
            'Content-Type'        => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ]);
    }
}
