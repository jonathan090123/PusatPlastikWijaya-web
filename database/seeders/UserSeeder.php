<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $customers = [
            [
                'name'      => 'Budi Santoso',
                'email'     => 'budi.santoso@email.com',
                'phone'     => '081234500001',
                'address'   => 'Jl. Veteran No. 12, Kel. Sananwetan, Blitar',
                'city_type' => 'blitar',
            ],
            [
                'name'      => 'Siti Rahayu',
                'email'     => 'siti.rahayu@email.com',
                'phone'     => '081234500002',
                'address'   => 'Jl. Merdeka No. 5, Kel. Kepanjenkidul, Blitar',
                'city_type' => 'blitar',
            ],
            [
                'name'      => 'Ahmad Fauzi',
                'email'     => 'ahmad.fauzi@email.com',
                'phone'     => '081234500003',
                'address'   => 'Jl. Sudirman No. 88, Kel. Sukorejo, Blitar',
                'city_type' => 'blitar',
            ],
            [
                'name'      => 'Dewi Lestari',
                'email'     => 'dewi.lestari@email.com',
                'phone'     => '081234500004',
                'address'   => 'Jl. Diponegoro No. 23, Lowokwaru, Malang',
                'city_type' => 'outside',
            ],
            [
                'name'      => 'Hendra Wijaya',
                'email'     => 'hendra.wijaya@email.com',
                'phone'     => '081234500005',
                'address'   => 'Jl. Cokroaminoto No. 7, Kel. Gedog, Blitar',
                'city_type' => 'blitar',
            ],
            [
                'name'      => 'Rina Kusuma',
                'email'     => 'rina.kusuma@email.com',
                'phone'     => '081234500006',
                'address'   => 'Jl. Ahmad Yani No. 45, Gubeng, Surabaya',
                'city_type' => 'outside',
            ],
            [
                'name'      => 'Doni Prasetyo',
                'email'     => 'doni.prasetyo@email.com',
                'phone'     => '081234500007',
                'address'   => 'Jl. Imam Bonjol No. 3, Kel. Tanjungsari, Blitar',
                'city_type' => 'blitar',
            ],
            [
                'name'      => 'Wulan Sari',
                'email'     => 'wulan.sari@email.com',
                'phone'     => '081234500008',
                'address'   => 'Jl. Hayam Wuruk No. 11, Mojoroto, Kediri',
                'city_type' => 'outside',
            ],
        ];

        foreach ($customers as $c) {
            DB::table('users')->insert([
                'name'       => $c['name'],
                'email'      => $c['email'],
                'phone'      => $c['phone'],
                'address'    => $c['address'],
                'city_type'  => $c['city_type'],
                'role'       => 'customer',
                'points'     => 0,
                'password'   => Hash::make('customer123'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
