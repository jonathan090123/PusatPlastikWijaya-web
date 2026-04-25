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
                'name'              => 'Admin',
                'phone'             => '081234567890',
                'role'              => 'admin',
                'password'          => Hash::make('admin123'),
                'email_verified_at' => now(),
            ]
        );

        // Akun Admin Sistem
        $adminAccounts = [
            ['email' => 'cv.pjl01@gmail.com', 'name' => 'Admin PJL 01', 'password' => 'cv.pjl01'],
            ['email' => 'cv.pjl02@gmail.com', 'name' => 'Admin PJL 02', 'password' => 'cv.pjl02'],
            ['email' => 'cv.pjl03@gmail.com', 'name' => 'Admin PJL 03', 'password' => 'cv.pjl03'],
        ];
        foreach ($adminAccounts as $admin) {
            User::firstOrCreate(
                ['email' => $admin['email']],
                [
                    'name'              => $admin['name'],
                    'phone'             => null,
                    'role'              => 'admin',
                    'password'          => Hash::make($admin['password']),
                    'email_verified_at' => now(),
                ]
            );
        }

        // Akun Customer contoh
        User::firstOrCreate(
            ['email' => 'customer@test.com'],
            [
                'name'              => 'Customer Test',
                'phone'             => '089876543210',
                'role'              => 'customer',
                'password'          => Hash::make('customer123'),
                'email_verified_at' => now(),
            ]
        );

        // Data dummy
        $this->call([
            CategorySeeder::class,
            ProductSeeder::class,
            PromoSeeder::class,
            UserSeeder::class,
            OrderSeeder::class,
        ]);
    }
}
