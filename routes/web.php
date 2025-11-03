<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Root diarahkan ke halaman login.
| Semua halaman aplikasi wajib login.
| Rute auth (login & logout) dimuat dari routes/auth.php (Breeze).
|--------------------------------------------------------------------------
*/

// Redirect ke halaman login kalau belum login
Route::get('/', function () {
    return redirect()->route('login');
});

// ====== ROUTE YANG WAJIB LOGIN ======
Route::middleware(['auth'])->group(function () {

    // Dashboard → resources/views/dashboard/index.blade.php
    Route::get('/dashboard', function () {
        return view('dashboard.index');  // <--- diarahkan ke folder dashboard/index.blade.php
    })->name('dashboard');

    // Inventory → resources/views/inventory/index.blade.php
    Route::get('/inventory', function () {
        return view('inventory.index');
    })->name('inventory');

    // Kasir → resources/views/kasir/index.blade.php
    Route::get('/kasir', function () {
        return view('kasir.index');
    })->name('cashier');

    // Riwayat → resources/views/riwayat/index.blade.php
    Route::get('/riwayat', function () {
        return view('riwayat.index');
    })->name('riwayat');
});

// ====== RUTE AUTENTIKASI (LOGIN & LOGOUT) ======
require __DIR__.'/auth.php';
