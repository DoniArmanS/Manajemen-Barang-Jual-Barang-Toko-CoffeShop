<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Tampilkan halaman dashboard.
     */
    public function index()
    {
        // Tidak perlu kirim data inventory di sini,
        // karena donut & ringkasan akan fetch via AJAX ke inventorySummary().
        return view('dashboard.index');
    }

    /**
     * Ringkasan inventory untuk donut + angka.
     * JSON: { total, ready, low, out }
     */
    public function inventorySummary(): JsonResponse
    {
        // Hitung total item
        $total = Item::count();

        // Ready: stock > min
        $ready = Item::whereColumn('stock', '>', 'min')->count();

        // Low: 1..min
        $low = Item::where('stock', '>', 0)
            ->whereColumn('stock', '<=', 'min')
            ->count();

        // Out: 0
        $out = Item::where('stock', 0)->count();

        return response()->json([
            'total' => (int) $total,
            'ready' => (int) $ready,
            'low'   => (int) $low,
            'out'   => (int) $out,
        ]);
    }
}
