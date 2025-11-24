<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_inventory_page(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $response = $this
            ->actingAs($admin)
            ->get('/inventory');

        $response->assertStatus(200);
        $response->assertSee('Inventory'); // Sesuaikan text di halaman
    }

    public function test_cashier_cannot_access_inventory_page(): void
    {
        $cashier = User::factory()->create([
            'role' => 'cashier',
        ]);

        $response = $this
            ->actingAs($cashier)
            ->get('/inventory');

        $response->assertStatus(403);
    }
}
