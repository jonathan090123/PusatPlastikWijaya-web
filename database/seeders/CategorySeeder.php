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
            ['name' => 'Kantong Plastik', 'description' => 'Berbagai jenis kantong plastik HD, kresek, sampah, dan PE kiloan'],
            ['name' => 'Botol & Toples', 'description' => 'Botol plastik, toples, dan wadah penyimpanan serbaguna'],
            ['name' => 'Sendok',         'description' => 'Sendok plastik, garpu plastik, dan perlengkapan makan plastik'],
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
