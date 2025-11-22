<?php

namespace Tests\Feature\Api;

use App\Models\Item;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryApiTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function api_ping_mengembalikan_status_ok()
    {
        $response = $this->getJson('/api/ping');

        $response
            ->assertStatus(200)
            ->assertJsonPath('status', 'ok');
    }

    /** @test */
    public function api_inventory_mengembalikan_data_item_dari_database()
    {
        // Arrange: buat 1 item di DB
        Item::create([
            'name'      => 'Komponen',
            'sku'       => 'KK',
            'category'  => 'kk',
            'stock'     => 10000,
            'min_stock' => 0,
            'unit'      => 'pcs',
            'note'      => null,
        ]);

        // Act
        $response = $this->getJson('/api/v1/inventory');

        // Assert
        $response
            ->assertStatus(200)
            ->assertJsonPath('status', 'ok')
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.sku', 'KK')
            ->assertJsonPath('data.0.stock', 10000);
    }

    /** @test */
    public function api_inventory_sync_menyimpan_data_dari_localstorage_ke_database()
    {
        // Pastikan awalnya kosong
        $this->assertDatabaseCount('items', 0);

        // Payload meniru struktur localStorage inv_items_v1
        $payload = [
            'items' => [
                [
                    'name'         => 'Gelas Plastik 16oz',
                    'sku'          => 'CUP-16',
                    'category'     => 'Perlengkapan',
                    'stock'        => 100,
                    'min'          => 50,
                    'unit'         => 'pcs',
                    'note'         => 'Untuk minuman dingin',
                    'default_cost' => 0,
                ],
            ],
        ];

        // Act: kirim ke /api/v1/inventory/sync
        $response = $this->postJson('/api/v1/inventory/sync', $payload);

        // Assert response
        $response
            ->assertStatus(200)
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('synced', 1);

        // Assert DB: data benar-benar tersimpan
        $this->assertDatabaseHas('items', [
            'sku'       => 'CUP-16',
            'name'      => 'Gelas Plastik 16oz',
            'category'  => 'Perlengkapan',
            'stock'     => 100,
            'min_stock' => 50,
            'unit'      => 'pcs',
        ]);
    }
}
