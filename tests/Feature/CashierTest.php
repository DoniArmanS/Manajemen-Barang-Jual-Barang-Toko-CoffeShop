<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CashierTest extends TestCase
{
    use RefreshDatabase;

    public function test_cashier_can_access_cashier_page(): void
    {
        $cashier = User::factory()->create([
            'role' => 'cashier',
        ]);

        $response = $this
            ->actingAs($cashier)
            ->get('/kasir');

        $response->assertStatus(200);
        $response->assertSee('Cashier'); // sesuai isi blade
    }

    public function test_admin_cannot_access_cashier_page(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $response = $this
            ->actingAs($admin)
            ->get('/kasir');

        // Sesuaikan dengan aplikasi: admin diarahkan ke /kasir
        $response->assertRedirect('/kasir');
    }
}
