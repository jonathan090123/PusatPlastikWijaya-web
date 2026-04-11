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
            ['name' => 'Gelas', 'description' => 'Gelas plastik berbagai ukuran untuk minuman'],
            ['name' => 'Kertas Bungkus', 'description' => 'Produk kertas bungkus dan kemasan kertas'],
            ['name' => 'Kresek', 'description' => 'Kantong kresek berbagai ukuran, warna, dan merek'],
            ['name' => 'Plastik', 'description' => 'Produk plastik umum: PE, PP, HDPE, dan kemasan plastik lainnya'],
            ['name' => 'Thinwall', 'description' => 'Wadah thinwall food container berbagai ukuran'],
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
