<?php

namespace Database\Seeders;

use App\Models\Item;
use Illuminate\Database\Seeder;

class ItemSeeder extends Seeder
{
    public function run(): void
    {
        Item::updateOrCreate(['sku' => 'BEAN-AR'], [
            'name' => 'Biji Kopi Arabica',
            'category' => 'Bahan',
            'stock' => 12,
            'min' => 5,
            'unit' => 'kg',
            'note' => null,
        ]);

        Item::updateOrCreate(['sku' => 'MILK-FC'], [
            'name' => 'Susu Full Cream',
            'category' => 'Bahan',
            'stock' => 4,
            'min' => 6,
            'unit' => 'L',
        ]);

        Item::updateOrCreate(['sku' => 'CUP-12'], [
            'name' => 'Gelas Cup 12oz',
            'category' => 'Perlengkapan',
            'stock' => 0,
            'min' => 50,
            'unit' => 'pcs',
        ]);
    }
}
