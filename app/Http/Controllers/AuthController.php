<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect('/dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $login    = $request->input('username');
        $password = $request->input('password');

        // Cari user berdasarkan username ATAU email, sama seperti versi lama
        $user = User::where(function ($q) use ($login) {
                $q->where('username', $login)
                  ->orWhere('email', $login);
            })
            ->where('status', 'active')
            ->first();

        if (!$user || !Hash::check($password, $user->password)) {
            return back()
                ->withErrors(['login' => 'Username atau password salah.'])
                ->withInput($request->except('password'));
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended('/dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}