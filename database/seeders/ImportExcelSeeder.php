<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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

        // Re-seed categories and products from Excel data
        $this->call([
            CategorySeeder::class,
            ProductSeeder::class,
        ]);

        $catCount = DB::table('categories')->count();
        $prodCount = DB::table('products')->count();
        $unitCount = DB::table('product_units')->count();

        $this->command->info("Imported: {$catCount} categories, {$prodCount} products, {$unitCount} product units.");
    }
}
