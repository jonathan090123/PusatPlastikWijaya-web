<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Kresek',   'description' => 'Kantong kresek berbagai ukuran, warna, dan merek'],
            ['name' => 'Plastik',  'description' => 'Produk plastik umum: wrap film, botol, mika box, dll'],
            ['name' => 'Kertas',   'description' => 'Cup kertas, cup muffin, cup bakery, dan produk kertas lainnya'],
        ];

        foreach ($categories as $cat) {
            DB::table('categories')->insert([
                'name'        => $cat['name'],
                'slug'        => Str::slug($cat['name']),
                'description' => $cat['description'],
                'is_active'   => true,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }
    }
}
