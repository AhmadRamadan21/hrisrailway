<?php

namespace App\Http\Controllers;

use App\Models\Bagian;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with(['role', 'bagian'])->latest()->get();
        return view('users.index', compact('users'));
    }

    public function create()
    {
        $roles     = Role::orderBy('name')->get();
        $bagians   = Bagian::orderBy('nama_bagian')->get();
        $karyawans = \App\Models\Karyawan::orderBy('nama')->get();
        return view('users.create', compact('roles', 'bagians', 'karyawans'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'      => 'required|string|max:255',
            'username'  => 'required|string|max:255|unique:users,username',
            'email'     => 'required|email|unique:users,email',
            'password'  => 'required|string|min:6',
            'role_id'   => 'nullable|exists:roles,id',
            'bagian_id' => 'nullable|exists:bagians,id',
            'status'    => 'required|in:active,inactive',
        ]);

        User::create([
            'name'      => $request->name,
            'username'  => $request->username,
            'email'     => $request->email,
            'password'  => $request->password, // otomatis di-hash (cast 'hashed' di Model)
            'role_id'   => $request->role_id,
            'bagian_id' => $request->bagian_id,
            'status'    => $request->status,
        ]);

        return redirect('/users')->with('success', 'User berhasil ditambahkan.');
    }

    public function edit(User $user)
    {
        $roles   = Role::orderBy('name')->get();
        $bagians = Bagian::orderBy('nama_bagian')->get();
        return view('users.edit', compact('user', 'roles', 'bagians'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name'      => 'required|string|max:255',
            'username'  => 'required|string|max:255|unique:users,username,' . $user->id,
            'email'     => 'required|email|unique:users,email,' . $user->id,
            'password'  => 'nullable|string|min:6',
            'role_id'   => 'nullable|exists:roles,id',
            'bagian_id' => 'nullable|exists:bagians,id',
            'status'    => 'required|in:active,inactive',
        ]);

        $user->name      = $request->name;
        $user->username  = $request->username;
        $user->email     = $request->email;
        $user->role_id   = $request->role_id;
        $user->bagian_id = $request->bagian_id;
        $user->status    = $request->status;

        if ($request->filled('password')) {
            $user->password = $request->password;
        }

        $user->save();

        return redirect('/users')->with('success', 'User berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak bisa menghapus akun Anda sendiri.');
        }

        $user->delete();

        return redirect('/users')->with('success', 'User berhasil dihapus.');
    }
}