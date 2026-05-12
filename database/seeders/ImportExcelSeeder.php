<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ImportExcelSeeder extends Seeder
{
    public function run(): void
    {
        // Disable foreign key checks for truncation
        Schema::disableForeignKeyConstraints();

        // Clear existing product-related data
        DB::table('product_units')->truncate();
        DB::table('cart_items')->truncate();
        DB::table('order_items')->truncate();
        DB::table('products')->truncate();
        DB::table('categories')->truncate();

        Schema::enableForeignKeyConstraints();

        $this->command->info('Cleared existing categories, products and product_units.');

        $dataDir = __DIR__ . '/data';

        // ── Import Categories ─────────────────────────────────────────
        $categoriesJson = file_get_contents($dataDir . '/categories.json');
        $categories     = json_decode($categoriesJson, true);

        foreach ($categories as $cat) {
            DB::table('categories')->insert([
                'name'        => $cat['name'],
                'slug'        => $cat['slug'],
                'description' => $cat['description'],
                'is_active'   => true,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }

        $this->command->info('Categories imported: ' . count($categories));

        // Build category name → id map
        $catMap = DB::table('categories')->pluck('id', 'name')->toArray();

        // ── Import Products ───────────────────────────────────────────
        $productsJson = file_get_contents($dataDir . '/products.json');
        $products     = json_decode($productsJson, true);

        $productBatch  = [];
        $unitBatch     = [];
        $now           = now()->toDateTimeString();
        $chunkSize     = 200;
        $productCount  = 0;
        $unitCount     = 0;

        foreach ($products as $p) {
            $categoryId = $catMap[$p['category']] ?? null;
            if (!$categoryId) {
                $this->command->warn("Category not found: {$p['category']} — skipping product {$p['name']}");
                continue;
            }

            // Ensure slug uniqueness within this batch
            $slug = Str::slug($p['name']) . '-' . $p['no'];

            $productId = DB::table('products')->insertGetId([
                'category_id'    => $categoryId,
                'product_code'   => $p['product_code'],
                'name'           => $p['name'],
                'slug'           => $slug,
                'unit'           => $p['unit'],
                'description'    => null,
                'price'          => $p['price'],
                'discount_price' => null,
                'weight'         => 0,
                'stock'          => $p['stock'],
                'stock_alert'    => 5,
                'is_active'      => $p['is_active'],
                'created_at'     => $now,
                'updated_at'     => $now,
            ]);

            $productCount++;

            foreach ($p['units'] as $u) {
                $unitBatch[] = [
                    'product_id'       => $productId,
                    'unit'             => $u['unit'],
                    'conversion_value' => $u['conversion_value'],
                    'price'            => $u['price'],
                    'created_at'       => $now,
                    'updated_at'       => $now,
                ];
                $unitCount++;
            }

            // Flush unit batch periodically to keep memory in check
            if (count($unitBatch) >= $chunkSize) {
                DB::table('product_units')->insert($unitBatch);
                $unitBatch = [];
            }
        }

        // Insert remaining units
        if (!empty($unitBatch)) {
            DB::table('product_units')->insert($unitBatch);
        }

        $catCount = DB::table('categories')->count();
        $this->command->info("Imported: {$catCount} categories, {$productCount} products, {$unitCount} product units.");
    }
}
