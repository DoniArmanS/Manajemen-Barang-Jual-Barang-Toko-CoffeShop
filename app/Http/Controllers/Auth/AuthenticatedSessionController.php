<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Tampilkan tampilan login.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Menangani permintaan autentikasi masuk.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();  // Autentikasi pengguna

        $request->session()->regenerate();  // Regenerasi session untuk keamanan

        $user = Auth::user();  // Mendapatkan pengguna yang sedang login

        // Mengarahkan berdasarkan role
        if ($user->role === 'cashier') {
            // Jika role adalah cashier, arahkan ke halaman kasir
            return redirect()->route('cashier');
        }

        // Default arahkan ke dashboard jika role adalah admin
        return redirect()->route('dashboard');
    }

    /**
     * Hancurkan session yang sudah terautentikasi.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();  // Logout pengguna

        $request->session()->invalidate();  // Hapus session

        $request->session()->regenerateToken();  // Regenerasi token CSRF

        return redirect('/');
    }
}
