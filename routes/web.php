<?php

use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('inventory'));

Route::get('/inventory', function () {
    return view('inventory.index');
})->name('inventory');

Route::get('/kasir', function () {
    return view('kasir.index');
})->name('cashier');
