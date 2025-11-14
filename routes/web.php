<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DashboardActivityController;
use App\Http\Controllers\CashierManageController;

// Root → langsung ke login atau redirect sesuai auth
Route::get('/', function () {
    return redirect()->route('login');
});

// Semua route yang butuh login
Route::middleware('auth')->group(function () {

    // Redirect setelah login (lihat langkah 5)
    Route::get('/home', function () {
        $user = auth()->user();
        if ($user->role === 'cashier') {
            return redirect()->route('cashier');
        }

        // default admin
        return redirect()->route('dashboard');
    })->name('home');

    // ====== ADMIN ONLY ======
    Route::middleware('role:admin')->group(function () {

        Route::get('/dashboard', [DashboardController::class, 'index'])
            ->name('dashboard');
        Route::get('/dashboard/stats', [DashboardController::class, 'stats'])
            ->name('dashboard.stats');

        Route::get('/dashboard/activity', [DashboardActivityController::class, 'index'])
            ->name('dashboard.activity');
        Route::post('/dashboard/activity', [DashboardActivityController::class, 'store'])
            ->name('dashboard.activity.store');
        Route::get('/dashboard/activity/export', [DashboardActivityController::class, 'export'])
            ->name('dashboard.activity.export');

        // INVENTORY
        Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
        Route::post('/inventory', [InventoryController::class, 'store'])->name('inventory.store');
        Route::put('/inventory/{item}', [InventoryController::class, 'update'])->name('inventory.update');
        Route::delete('/inventory/{item}', [InventoryController::class, 'destroy'])->name('inventory.destroy');
        Route::post('/inventory/{item}/adjust', [InventoryController::class, 'adjust'])->name('inventory.adjust');
        Route::get('/inventory/summary', [InventoryController::class, 'summary'])->name('inventory.summary');
        Route::get('/inventory/json', [InventoryController::class, 'json'])->name('inventory.json');

        // Cashier Management + Riwayat hanya admin
        Route::get('/kasir/manage', [CashierManageController::class, 'index'])->name('cashier.manage');
        Route::view('/riwayat', 'riwayat.riwayat')->name('riwayat');
    });

    // ====== ADMIN & CASHIER (halaman kasir) ======
    Route::middleware('role:admin|cashier')->group(function () {
        Route::view('/kasir', 'kasir.index')->name('cashier');
    });
});

// Rute login/logout dari Breeze
require __DIR__.'/auth.php';
