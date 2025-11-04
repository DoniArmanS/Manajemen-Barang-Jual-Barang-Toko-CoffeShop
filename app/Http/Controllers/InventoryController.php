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

    // ====== JSON UNTUK DASHBOARD ======
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

    public function json()
    {
        return response()->json(Item::orderBy('name')->get());
    }
}
