<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'super@coffeeshop.local'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('secret123'),
            ]
        );
    }
}
