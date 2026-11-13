<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // Menampilkan halaman login
    public function showLogin()
    {
        if (Auth::check()) {
            return $this->redirectByRole();
        }

        return view('auth.login');
    }

    // Memproses login
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Password wajib diisi.',
        ]);

        // Hanya akun yang aktif yang boleh login
        $loginCredentials = $credentials + [
            'is_active' => true,
        ];

        if (Auth::attempt($loginCredentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            return $this->redirectByRole();
        }

        return back()
            ->withErrors([
                'email' => 'Email atau password salah, atau akun Anda sedang dinonaktifkan.',
            ])
            ->onlyInput('email');
    }

    // Mengarahkan pengguna sesuai role
    private function redirectByRole()
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Memastikan akun masih aktif
        if (!$user->is_active) {
            Auth::logout();

            return redirect()
                ->route('login')
                ->withErrors([
                    'email' => 'Akun Anda sedang dinonaktifkan. Silakan hubungi Kepala Toko.',
                ]);
        }

        if ($user->role === 'kepala_toko') {
            return redirect()->route('kepala-toko.dashboard');
        }

        if ($user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        Auth::logout();

        return redirect()
            ->route('login')
            ->withErrors([
                'email' => 'Role akun tidak dikenali.',
            ]);
    }

    // Memproses logout
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('login')
            ->with('success', 'Anda berhasil keluar dari sistem.');
    }
}
