<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * Halaman gateway — landing page untuk memilih portal.
     * Jika sudah login, redirect ke dashboard sesuai role.
     */
    public function gateway()
    {
        if (Auth::check()) {
            return $this->redirectByRole(Auth::user());
        }

        return view('gateway');
    }

    /**
     * Tampilkan form login admin (Blade).
     * Middleware 'guest' sudah menangani redirect jika sudah login.
     */
    public function showLogin()
    {
        return view('auth.login');
    }

    /**
     * Proses login admin via Blade form.
     * Menggunakan Breeze LoginRequest (rate limiting bawaan).
     * Hanya admin yang diizinkan — mandor/staff diarahkan ke SPA.
     */
    public function login(LoginRequest $request)
    {
        $request->authenticate();

        $user = Auth::user();

        // Hanya admin yang boleh login via blade form
        if (!$user->hasEffectiveRole(User::ROLE_ADMIN)) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect('/user/#/login')
                ->withErrors(['email' => 'Akun ini bukan Administrator. Silakan login melalui Portal User.']);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('pendapatan.index'))
            ->with('success', 'Login berhasil. Selamat datang, ' . $user->name . '!');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('gateway')->with('success', 'Anda telah berhasil logout.');
    }

    /**
     * Redirect user ke dashboard sesuai role-nya.
     * - admin        → halaman pendapatan (admin panel blade)
     * - mandor/staff → user portal SPA
     */
    private function redirectByRole(User $user)
    {
        if ($user->hasEffectiveRole(User::ROLE_ADMIN)) {
            return redirect()->intended(route('pendapatan.index'));
        }

        if ($user->hasEffectiveRole([User::ROLE_MANDOR, User::ROLE_STAFF_OPERATOR])) {
            return redirect('/user/#/dashboard');
        }

        return redirect()->route('gateway');
    }
}
