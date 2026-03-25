<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PromoSeeder extends Seeder
{
    /**
     * Memilih produk secara acak lalu memberi discount_price (diskon 5%–30%).
     * Sekitar 30% dari total produk akan mendapat promo.
     */
    public function run(): void
    {
        $products = DB::table('products')->get(['id', 'price']);

        if ($products->isEmpty()) {
            $this->command->warn('Tidak ada produk ditemukan. Jalankan ProductSeeder terlebih dahulu.');
            return;
        }

        // Reset semua diskon terlebih dahulu
        DB::table('products')->update(['discount_price' => null]);

        // Ambil ~30% produk secara acak
        $promoCount  = (int) ceil($products->count() * 0.30);
        $promoIds    = $products->shuffle()->take($promoCount)->pluck('id');

        // Persentase diskon yang tersedia (lebih natural, kelipatan 5%)
        $discountTiers = [5, 10, 15, 20, 25, 30];

        foreach ($products->whereIn('id', $promoIds->all()) as $product) {
            $discountPct  = $discountTiers[array_rand($discountTiers)];
            $rawDiscount  = $product->price * (1 - $discountPct / 100);

            // Bulatkan ke bawah ke kelipatan 500 paling dekat agar harga terlihat rapi
            $discountPrice = floor($rawDiscount / 500) * 500;

            // Pastikan harga diskon lebih kecil dari harga asli
            if ($discountPrice >= $product->price) {
                $discountPrice = $product->price - 500;
            }

            if ($discountPrice > 0) {
                DB::table('products')
                    ->where('id', $product->id)
                    ->update(['discount_price' => $discountPrice]);
            }
        }

        $this->command->info("Promo diterapkan ke {$promoCount} dari {$products->count()} produk.");
    }
}
