<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InventoryController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| Semua route di sini otomatis punya prefix "api"
| Jadi URL aslinya:
|   /api/...
*/

// Cek cepat apakah API hidup
Route::get('/ping', function () {
    return response()->json([
        'status'  => 'ok',
        'message' => 'API is online',
    ]);
});

// Versi 1 dari API kita
Route::prefix('v1')->group(function () {

    // GET /api/v1/inventory
    // Ambil data inventory dalam bentuk JSON
    Route::get('/inventory', [InventoryController::class, 'json'])
        ->name('api.inventory.index');

});

// (Optional) route bawaan kalau pakai Sanctum, boleh dihapus kalau nggak dipakai
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
