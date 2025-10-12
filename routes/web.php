<?php

use Illuminate\Support\Facades\Route;

// redirect root ke dashboard
Route::get('/', fn () => redirect()->route('dashboard'));

// route dashboard (halaman utama)
Route::get('/dashboard', function () {
    return view('dashboard.index');
})->name('dashboard');

// route inventory
Route::get('/inventory', function () {
    return view('inventory.index');
})->name('inventory');

// route kasir
Route::get('/kasir', function () {
    return view('kasir.index');
})->name('cashier');

// route riwayat
Route::get('/riwayat', function () {
    return view('riwayat.riwayat');
});

