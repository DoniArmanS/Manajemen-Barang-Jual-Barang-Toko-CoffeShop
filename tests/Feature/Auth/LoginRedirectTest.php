<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginRedirectTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function admin_diarahkan_ke_dashboard_setelah_login()
    {
        // Arrange: buat user admin
        $user = User::create([
            'name'     => 'Admin Test',
            'email'    => 'admin@test.local',
            'password' => Hash::make('secret123'),
            'role'     => 'admin',
        ]);

        // Act: kirim POST ke /login
        $response = $this->post('/login', [
            'email'    => 'admin@test.local',
            'password' => 'secret123',
        ]);

        // Assert: redirect ke dashboard dan user ter-auth
        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    /** @test */
    public function cashier_diarahkan_ke_halaman_kasir_setelah_login()
    {
        // Arrange: buat user cashier
        $user = User::create([
            'name'     => 'Cashier Test',
            'email'    => 'cashier@test.local',
            'password' => Hash::make('secret123'),
            'role'     => 'cashier',
        ]);

        // Act
        $response = $this->post('/login', [
            'email'    => 'cashier@test.local',
            'password' => 'secret123',
        ]);

        // Assert: redirect ke route cashier
        $response->assertRedirect(route('cashier'));
        $this->assertAuthenticatedAs($user);
    }
}
