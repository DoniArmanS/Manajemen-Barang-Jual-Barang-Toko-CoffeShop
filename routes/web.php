<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DashboardActivityController;
use App\Http\Controllers\CashierManageController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
| Root diarahkan ke halaman login. Semua halaman aplikasi wajib login.
| Rute auth (login & logout) dimuat dari routes/auth.php (Breeze).
|--------------------------------------------------------------------------
*/

// Redirect ke halaman login kalau user ke "/"
Route::get('/', fn () => redirect()->route('login'));

// ====== ROUTE YANG WAJIB LOGIN ======
Route::middleware('auth')->group(function () {

    /**
     * Dashboard → resources/views/dashboard/index.blade.php
     * Disarankan pakai controller supaya data dashboard (donut inventory dll) bisa
     * diisi dari database. Namun kalau kamu masih ingin pakai view statis, tinggal
     * ganti ke Route::view('/dashboard', 'dashboard.index')->name('dashboard');
     */
    Route::get('/dashboard', [DashboardController::class, 'index'])
    ->name('dashboard');

    Route::get('/dashboard/inventory-summary', [DashboardController::class, 'inventorySummary'])
    ->name('dashboard.inventory.summary');

                // Activity JSON (dashboard)
            Route::get('/dashboard/activity', [DashboardActivityController::class, 'index'])
                ->name('dashboard.activity');

            // Export CSV activity harian
            Route::get('/dashboard/activity/export', [DashboardActivityController::class, 'export'])
                ->name('dashboard.activity.export');

            // Notes harian
            Route::get('/dashboard/notes', [DashboardActivityController::class, 'getNote'])
                ->name('dashboard.notes.get');
            Route::post('/dashboard/notes', [DashboardActivityController::class, 'saveNote'])
                ->name('dashboard.notes.save');
            Route::post('/dashboard/notes/import', [DashboardActivityController::class, 'importNoteCsv'])
                ->name('dashboard.notes.import');

            // Endpoint untuk Inventory mengirim aktivitas
            Route::post('/activity', [DashboardActivityController::class, 'store'])
                ->name('activity.store');

            Route::prefix('kasir')->group(function () {
            Route::get('/',        [CashierController::class,      'index'])->name('cashier');          // /kasir
            Route::get('/manage',  [CashierManageController::class, 'index'])->name('cashier.manage');   // /kasir/manage
        });

    /**
     * INVENTORY (DB)
     * Controller ini menangani list, store, update, delete, dan adjust stok.
     * View utama: resources/views/inventory/index.blade.php
     */
    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory');
    Route::post('/inventory', [InventoryController::class, 'store'])->name('inventory.store');
    Route::patch('/inventory/{item}', [InventoryController::class, 'update'])->name('inventory.update');
    Route::delete('/inventory/{item}', [InventoryController::class, 'destroy'])->name('inventory.destroy');
    Route::post('/inventory/{item}/adjust', [InventoryController::class, 'adjust'])->name('inventory.adjust');

    /**
     * KASIR & RIWAYAT (sementara view statis kamu).
     * View:
     *  - resources/views/kasir/index.blade.php
     *  - resources/views/riwayat/riwayat.blade.php
     */
    Route::view('/kasir', 'kasir.index')->name('cashier');
    Route::view('/riwayat', 'riwayat.riwayat')->name('riwayat');
});

// ====== RUTE AUTENTIKASI (LOGIN & LOGOUT) ======
require __DIR__.'/auth.php';
