<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        // Super Admin
        User::updateOrCreate(
            ['email' => 'super@coffeeshop.local'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('secret123'),
                'role' => 'admin', // <— penting
            ]
        );

        // 3 akun karyawan (cashier)
        $cashiers = [
            ['name' => 'Cashier 1', 'email' => 'cashier1@coffeeshop.local'],
            ['name' => 'Cashier 2', 'email' => 'cashier2@coffeeshop.local'],
            ['name' => 'Cashier 3', 'email' => 'cashier3@coffeeshop.local'],
        ];

        foreach ($cashiers as $c) {
            User::updateOrCreate(
                ['email' => $c['email']],
                [
                    'name' => $c['name'],
                    'password' => Hash::make('secret123'),
                    'role' => 'cashier',
                ]
            );
        }
    }
}
