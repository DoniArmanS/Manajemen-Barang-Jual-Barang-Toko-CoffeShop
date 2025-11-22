<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\InventoryItem; // GANTI dengan model inventory milikmu

class InventoryApiController extends Controller
{
    // GET /api/inventory
    public function index()
    {
        $items = InventoryItem::all();

        return response()->json([
            'status' => 'ok',
            'data'   => $items,
        ]);
    }

    // POST /api/inventory
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'  => 'required|string|max:255',
            'stock' => 'required|numeric|min:0',
            'unit'  => 'nullable|string|max:50',
            'price' => 'nullable|numeric|min:0',
        ]);

        $item = InventoryItem::create($data);

        return response()->json([
            'status' => 'created',
            'data'   => $item,
        ], 201);
    }

    // GET /api/inventory/{id}
    public function show($id)
    {
        $item = InventoryItem::findOrFail($id);

        return response()->json([
            'status' => 'ok',
            'data'   => $item,
        ]);
    }

    // PUT/PATCH /api/inventory/{id}
    public function update(Request $request, $id)
    {
        $item = InventoryItem::findOrFail($id);

        $data = $request->validate([
            'name'  => 'sometimes|required|string|max:255',
            'stock' => 'sometimes|required|numeric|min:0',
            'unit'  => 'nullable|string|max:50',
            'price' => 'nullable|numeric|min:0',
        ]);

        $item->update($data);

        return response()->json([
            'status' => 'updated',
            'data'   => $item,
        ]);
    }

    // DELETE /api/inventory/{id}
    public function destroy($id)
    {
        $item = InventoryItem::findOrFail($id);
        $item->delete();

        return response()->json([
            'status'  => 'deleted',
            'message' => 'Item deleted',
        ]);
    }
}
