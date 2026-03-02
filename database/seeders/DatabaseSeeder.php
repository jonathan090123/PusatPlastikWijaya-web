<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // Akun Admin
        User::create([
            'name' => 'Admin',
            'email' => 'admin@plastikwijaya.com',
            'phone' => '081234567890',
            'role' => 'admin',
            'password' => Hash::make('admin123'),
        ]);

        // Akun Customer contoh
        User::create([
            'name' => 'Customer Test',
            'email' => 'customer@test.com',
            'phone' => '089876543210',
            'role' => 'customer',
            'password' => Hash::make('customer123'),
        ]);
    }
}
