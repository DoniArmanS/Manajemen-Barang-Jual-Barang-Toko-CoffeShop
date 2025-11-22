<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class InventoryController extends Controller
{
    // ====== VIEW ======
    public function index()
    {
        $items = Item::latest()->get();
        return view('inventory.index', compact('items'));
    }

    // ====== CRUD ======
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'      => ['required', 'string', 'max:150'],
            'sku'       => ['required', 'string', 'max:100', 'unique:items,sku'],
            'category'  => ['required', 'string', 'max:100'],
            'stock'     => ['required', 'integer', 'min:0'],
            'min_stock' => ['required', 'integer', 'min:0'],
            'unit'      => ['nullable', 'string', 'max:20'],
        ]);

        Item::create($data);
        return back()->with('ok', 'Item ditambahkan');
    }

    public function update(Request $request, Item $item)
    {
        $data = $request->validate([
            'name'      => ['required', 'string', 'max:150'],
            'sku'       => ['required', 'string', 'max:100', Rule::unique('items', 'sku')->ignore($item->id)],
            'category'  => ['required', 'string', 'max:100'],
            'stock'     => ['required', 'integer', 'min:0'],
            'min_stock' => ['required', 'integer', 'min:0'],
            'unit'      => ['nullable', 'string', 'max:20'],
        ]);

        $item->update($data);
        return back()->with('ok', 'Item diupdate');
    }

    public function destroy(Item $item)
    {
        $item->delete();
        return back()->with('ok', 'Item dihapus');
    }

    public function adjust(Request $request, Item $item)
    {
        $request->validate([
            'delta' => ['required', 'integer'], // boleh +/-
        ]);

        $item->increment('stock', (int)$request->delta);
        if ($item->stock < 0) $item->update(['stock' => 0]);

        return back()->with('ok', 'Stock disesuaikan');
    }

    // ====== JSON UNTUK DASHBOARD (LAMA, BIARKAN) ======
    public function summary()
    {
        $items = Item::select('stock', 'min_stock')->get();

        $ready = 0; $low = 0; $out = 0;
        foreach ($items as $it) {
            if ($it->stock <= 0) $out++;
            elseif ($it->stock <= $it->min_stock) $low++;
            else $ready++;
        }

        return response()->json([
            'total' => $items->count(),
            'ready' => $ready,
            'low'   => $low,
            'out'   => $out,
        ]);
    }

    // dipakai route inventory.json → dashboard donut
    public function json()
    {
        // pakai model Item yang sudah di-use di atas
        $items = Item::all();

        return response()->json([
            'status' => 'ok',
            'data'   => $items,
        ]);
    }

    // =========================================
    // ============= BAGIAN API BARU ===========
    // =========================================

    // GET /api/v1/inventory
    // Ambil data inventory dari DB dalam bentuk JSON (buat Postman / mobile / dsb)
    public function apiIndex()
    {
        $items = Item::orderBy('name')->get();

        return response()->json([
            'status' => 'ok',
            'data'   => $items,
        ]);
    }

    // POST /api/v1/inventory/sync
    // Terima isi localStorage (array items) lalu upsert ke DB berdasarkan SKU
    public function apiSync(Request $request)
    {
        $payload = $request->validate([
            'items'                => ['required', 'array'],
            'items.*.name'         => ['required', 'string', 'max:150'],
            'items.*.sku'          => ['required', 'string', 'max:100'],
            'items.*.category'     => ['required', 'string', 'max:100'],
            'items.*.stock'        => ['required', 'integer', 'min:0'],
            'items.*.min'          => ['required', 'integer', 'min:0'],
            'items.*.unit'         => ['nullable', 'string', 'max:20'],
            'items.*.note'         => ['nullable', 'string'],
            'items.*.default_cost' => ['nullable', 'numeric'], // kalau ada di localStorage
        ]);

        $items = $payload['items'];

        foreach ($items as $data) {
            Item::updateOrCreate(
                ['sku' => $data['sku']], // kunci unik
                [
                    'name'      => $data['name'],
                    'category'  => $data['category'],
                    'stock'     => $data['stock'],
                    'min_stock' => $data['min'],           // map dari "min" localStorage
                    'unit'      => $data['unit'] ?? 'pcs',
                    'note'      => $data['note'] ?? null,
                ]
            );
        }

        return response()->json([
            'status' => 'ok',
            'synced' => count($items),
        ]);
    }
}
