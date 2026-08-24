<?php

namespace App\Http\Controllers;

use App\Models\Divisi;
use App\Models\Jabatan;
use Illuminate\Http\Request;

class DivisiJabatanController extends Controller
{
    public function index()
    {
        $divisis  = Divisi::withCount('karyawans')->orderBy('nama_divisi')->get();
        $jabatans = Jabatan::with('divisi')->orderBy('nama_jabatan')->get();

        return view('divisi_jabatan.index', compact('divisis', 'jabatans'));
    }

    // ===== Divisi =====

    public function storeDivisi(Request $request)
    {
        $data = $request->validate([
            'nama_divisi' => 'required|string|max:100',
        ]);

        Divisi::create($data);

        return back()->with('success', 'Divisi berhasil ditambahkan.');
    }

    public function updateDivisi(Request $request, Divisi $divisi)
    {
        $data = $request->validate([
            'nama_divisi' => 'required|string|max:100',
        ]);

        $divisi->update($data);

        return back()->with('success', 'Divisi berhasil diperbarui.');
    }

    public function destroyDivisi(Divisi $divisi)
    {
        $divisi->delete();

        return back()->with('success', 'Divisi berhasil dihapus.');
    }

    // ===== Jabatan =====

    public function storeJabatan(Request $request)
    {
        $data = $request->validate([
            'nama_jabatan' => 'required|string|max:100',
            'divisi_id'    => 'nullable|exists:divisis,id',
        ]);

        Jabatan::create($data);

        return back()->with('success', 'Jabatan berhasil ditambahkan.');
    }

    public function updateJabatan(Request $request, Jabatan $jabatan)
    {
        $data = $request->validate([
            'nama_jabatan' => 'required|string|max:100',
            'divisi_id'    => 'nullable|exists:divisis,id',
        ]);

        $jabatan->update($data);

        return back()->with('success', 'Jabatan berhasil diperbarui.');
    }

    public function destroyJabatan(Jabatan $jabatan)
    {
        $jabatan->delete();

        return back()->with('success', 'Jabatan berhasil dihapus.');
    }
}