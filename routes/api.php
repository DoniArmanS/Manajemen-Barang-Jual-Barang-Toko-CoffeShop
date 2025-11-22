<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InventoryController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::get('/ping', function () {
    return response()->json([
        'status'  => 'ok',
        'message' => 'API is online',
    ]);
});

// Versi 1 API kita
Route::prefix('v1')->group(function () {

    // GET /api/v1/inventory → ambil data dari DB
    Route::get('/inventory', [InventoryController::class, 'apiIndex'])
        ->name('api.inventory.index');

    // POST /api/v1/inventory/sync → sinkron dari localStorage ke DB
    Route::post('/inventory/sync', [InventoryController::class, 'apiSync'])
        ->name('api.inventory.sync');
});
