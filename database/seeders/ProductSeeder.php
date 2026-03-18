<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $catId = fn(string $name) => DB::table('categories')->where('name', $name)->value('id');

        $products = [
            // ── Kantong Plastik (8) ──────────────────────────────────────────
            ['cat' => 'Kantong Plastik', 'name' => 'Kantong Plastik HD 24 (1 kg)',       'price' => 15000, 'discount_price' => null,   'stock' => 120, 'weight' => 1000, 'desc' => 'Kantong plastik HD ukuran 24, per kilogram isi ±80 lembar.'],
            ['cat' => 'Kantong Plastik', 'name' => 'Kantong Plastik HD 30 (1 kg)',       'price' => 22000, 'discount_price' => 20000,  'stock' => 95,  'weight' => 1000, 'desc' => 'Kantong plastik HD ukuran 30, per kilogram isi ±60 lembar.'],
            ['cat' => 'Kantong Plastik', 'name' => 'Kantong Plastik HD 40 (1 kg)',       'price' => 30000, 'discount_price' => null,   'stock' => 70,  'weight' => 1000, 'desc' => 'Kantong plastik HD ukuran 40, per kilogram isi ±45 lembar.'],
            ['cat' => 'Kantong Plastik', 'name' => 'Kantong Kresek Putih (1 kg)',        'price' => 18000, 'discount_price' => null,   'stock' => 80,  'weight' => 1000, 'desc' => 'Kantong kresek putih bersih, cocok untuk kemasan produk.'],
            ['cat' => 'Kantong Plastik', 'name' => 'Kantong Sampah Hitam 60x100 (1 kg)', 'price' => 25000,'discount_price' => null,   'stock' => 60,  'weight' => 1000, 'desc' => 'Kantong sampah hitam tebal ukuran 60×100 cm.'],
            ['cat' => 'Kantong Plastik', 'name' => 'Plastik PE Transparan Roll',         'price' => 35000, 'discount_price' => 32000,  'stock' => 45,  'weight' => 1500, 'desc' => 'Plastik PE bening roll untuk pembungkus dan kemasan.'],
            ['cat' => 'Kantong Plastik', 'name' => 'Kantong Plastik Plong (1 kg)',       'price' => 12000, 'discount_price' => null,   'stock' => 150, 'weight' => 1000, 'desc' => 'Kantong plastik plong tipis serbaguna, per kilogram.'],
            ['cat' => 'Kantong Plastik', 'name' => 'Kantong Zipper Plastik Bening',      'price' => 20000, 'discount_price' => 18000,  'stock' => 90,  'weight' => 500,  'desc' => 'Kantong plastik klip zipper bening, isi 100 pcs, kedap udara.'],

            // ── Botol & Toples (7) ───────────────────────────────────────────
            ['cat' => 'Botol & Toples', 'name' => 'Botol Plastik 600ml (1 lusin)',      'price' => 42000, 'discount_price' => 38000,  'stock' => 55,  'weight' => 600,  'desc' => 'Botol plastik bening 600ml, isi 12 pcs per lusin.'],
            ['cat' => 'Botol & Toples', 'name' => 'Botol Plastik 1 Liter (1 lusin)',    'price' => 60000, 'discount_price' => null,   'stock' => 40,  'weight' => 900,  'desc' => 'Botol plastik bening 1 liter, isi 12 pcs per lusin.'],
            ['cat' => 'Botol & Toples', 'name' => 'Botol Plastik 250ml (1 lusin)',      'price' => 30000, 'discount_price' => null,   'stock' => 60,  'weight' => 400,  'desc' => 'Botol plastik mini 250ml, cocok untuk saus, sambal, atau minuman.'],
            ['cat' => 'Botol & Toples', 'name' => 'Toples Plastik Bulat 500 gr',        'price' => 15000, 'discount_price' => null,   'stock' => 70,  'weight' => 200,  'desc' => 'Toples plastik bulat kapasitas 500 gram, tutup rapat.'],
            ['cat' => 'Botol & Toples', 'name' => 'Toples Plastik Kotak 1 Liter',       'price' => 22000, 'discount_price' => 20000,  'stock' => 60,  'weight' => 280,  'desc' => 'Toples plastik kotak 1 liter, bening dan kedap udara.'],
            ['cat' => 'Botol & Toples', 'name' => 'Toples Plastik Tabung 2 Liter',      'price' => 32000, 'discount_price' => null,   'stock' => 45,  'weight' => 400,  'desc' => 'Toples plastik tabung 2 liter, ideal untuk kue kering dan camilan.'],
            ['cat' => 'Botol & Toples', 'name' => 'Wadah Serbaguna Tutup Rapat',        'price' => 28000, 'discount_price' => null,   'stock' => 50,  'weight' => 350,  'desc' => 'Wadah plastik serbaguna dengan tutup kunci 4 sisi.'],

            // ── Sendok (15) ──────────────────────────────────────────────────
            ['cat' => 'Sendok', 'name' => 'Sendok Makan Plastik (1 lusin)',              'price' => 7000,  'discount_price' => null,   'stock' => 200, 'weight' => 100,  'desc' => 'Sendok makan plastik isi 12 pcs, ringan dan higienis.'],
            ['cat' => 'Sendok', 'name' => 'Garpu Plastik (1 lusin)',                     'price' => 7000,  'discount_price' => null,   'stock' => 180, 'weight' => 100,  'desc' => 'Garpu plastik isi 12 pcs, cocok untuk pesta atau katering.'],
            ['cat' => 'Sendok', 'name' => 'Sendok Teh Plastik (1 lusin)',                'price' => 5000,  'discount_price' => null,   'stock' => 220, 'weight' => 80,   'desc' => 'Sendok teh plastik isi 12 pcs, serbaguna untuk minuman dan dessert.'],
            ['cat' => 'Sendok', 'name' => 'Sendok Sup Plastik (1 lusin)',                'price' => 10000, 'discount_price' => 9000,   'stock' => 150, 'weight' => 150,  'desc' => 'Sendok sup besar plastik isi 12 pcs, ideal untuk kuah dan sup.'],
            ['cat' => 'Sendok', 'name' => 'Sendok Es Krim Plastik (1 lusin)',            'price' => 8000,  'discount_price' => null,   'stock' => 160, 'weight' => 90,   'desc' => 'Sendok es krim plastik kecil isi 12 pcs, cocok untuk tester atau porsi kecil.'],
            ['cat' => 'Sendok', 'name' => 'Set Sendok Garpu Plastik (isi 50 pasang)',    'price' => 45000, 'discount_price' => 40000,  'stock' => 80,  'weight' => 600,  'desc' => 'Set sendok dan garpu plastik isi 50 pasang, hemat untuk acara / katering.'],
            ['cat' => 'Sendok', 'name' => 'Centong Nasi Plastik',                        'price' => 8000,  'discount_price' => null,   'stock' => 120, 'weight' => 120,  'desc' => 'Centong nasi plastik panjang, anti lengket dan mudah dibersihkan.'],
            ['cat' => 'Sendok', 'name' => 'Centong Sayur Plastik',                       'price' => 9000,  'discount_price' => null,   'stock' => 100, 'weight' => 140,  'desc' => 'Centong sayur / lauk plastik bertangkai panjang, tahan panas.'],
            ['cat' => 'Sendok', 'name' => 'Spatula Plastik Masak',                       'price' => 12000, 'discount_price' => null,   'stock' => 90,  'weight' => 150,  'desc' => 'Spatula plastik serbaguna untuk mengaduk dan membalik masakan.'],
            ['cat' => 'Sendok', 'name' => 'Sendok Bayi Plastik (set 3 pcs)',             'price' => 15000, 'discount_price' => 13000,  'stock' => 70,  'weight' => 80,   'desc' => 'Set sendok bayi plastik lembut berisi 3 ukuran, BPA free.'],
            ['cat' => 'Sendok', 'name' => 'Sendok Makan Plastik Besar (1 lusin)',        'price' => 9000,  'discount_price' => null,   'stock' => 140, 'weight' => 130,  'desc' => 'Sendok makan plastik ukuran besar isi 12 pcs, cocok untuk menu prasmanan.'],
            ['cat' => 'Sendok', 'name' => 'Tusuk Gigi Plastik (kotak 1000 pcs)',         'price' => 12000, 'discount_price' => null,   'stock' => 200, 'weight' => 200,  'desc' => 'Tusuk gigi plastik kotak isi 1000 pcs, bersih dan higienis.'],
            ['cat' => 'Sendok', 'name' => 'Sedotan Plastik Bening (1 pack)',             'price' => 8000,  'discount_price' => null,   'stock' => 300, 'weight' => 150,  'desc' => 'Sedotan plastik bening isi 100 pcs per pack, diameter standar.'],
            ['cat' => 'Sendok', 'name' => 'Sedotan Plastik Jumbo (1 pack)',              'price' => 10000, 'discount_price' => 9000,   'stock' => 200, 'weight' => 180,  'desc' => 'Sedotan jumbo plastik isi 50 pcs, cocok untuk jus dan bubble tea.'],
            ['cat' => 'Sendok', 'name' => 'Garpu Buah Plastik Kecil (1 lusin)',          'price' => 6000,  'discount_price' => null,   'stock' => 160, 'weight' => 70,   'desc' => 'Garpu buah / kue plastik kecil isi 12 pcs, serbaguna untuk dessert.'],
        ];

        foreach ($products as $p) {
            DB::table('products')->insert([
                'category_id'    => $catId($p['cat']),
                'name'           => $p['name'],
                'slug'           => Str::slug($p['name']) . '-' . Str::random(4),
                'description'    => $p['desc'],
                'price'          => $p['price'],
                'discount_price' => $p['discount_price'],
                'weight'         => $p['weight'],
                'stock'          => $p['stock'],
                'stock_alert'    => 5,
                'is_active'      => true,
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);
        }
    }
}
