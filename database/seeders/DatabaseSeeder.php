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
        User::firstOrCreate(
            ['email' => 'admin@plastikwijaya.com'],
            [
                'name'     => 'Admin',
                'phone'    => '081234567890',
                'role'     => 'admin',
                'password' => Hash::make('admin123'),
            ]
        );

        // Akun Customer contoh
        User::firstOrCreate(
            ['email' => 'customer@test.com'],
            [
                'name'     => 'Customer Test',
                'phone'    => '089876543210',
                'role'     => 'customer',
                'password' => Hash::make('customer123'),
            ]
        );

        // Data dummy
        $this->call([
            CategorySeeder::class,
            ProductSeeder::class,
            UserSeeder::class,
            OrderSeeder::class,
        ]);
    }
}
