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

        /**
         * Struktur: [kategori, kode, nama, satuan_dasar, harga_dasar, konversi_satuan]
         * konversi_satuan: [[unit, rasio, harga], ...]
         * Rasio = berapa satuan dasar per 1 satuan ini
         * Data di-parse dari file CSV client (item 2 ls 2.csv), deduplicate by kode barang
         */
        $products = [
            // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
            // KRESEK â€” LOS (satuan dasar KG)
            // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
            ['cat' => 'Kresek', 'code' => 'KRS-H35-ATL', 'name' => 'Kresek Los Hitam 35 AT',         'unit' => 'KG',  'price' => 33000,  'stock' => 50,   'units' => [['BAL', 25, 800000]]],
            ['cat' => 'Kresek', 'code' => 'KRS-H40-ATL', 'name' => 'Kresek Los Hitam 40 AT',         'unit' => 'KG',  'price' => 33000,  'stock' => 40,   'units' => [['BAL', 25, 800000]]],
            ['cat' => 'Kresek', 'code' => 'KW1-N15-LEO', 'name' => 'Kresek Los Natural 15 Leo K1',   'unit' => 'KG',  'price' => 27500,  'stock' => 75,   'units' => [['BAL', 25, 675000]]],
            ['cat' => 'Kresek', 'code' => 'KW1-N15-DAU', 'name' => 'Kresek Los Natural 15 Daun K1',  'unit' => 'KG',  'price' => 26500,  'stock' => 60,   'units' => [['BAL', 25, 637500]]],
            ['cat' => 'Kresek', 'code' => 'KW1-N24-DAU', 'name' => 'Kresek Los Natural 24 Daun K1',  'unit' => 'KG',  'price' => 26500,  'stock' => 80,   'units' => [['BAL', 25, 637500]]],
            ['cat' => 'Kresek', 'code' => 'KW1-N24-LEO', 'name' => 'Kresek Los Natural 24 Leo K1',   'unit' => 'KG',  'price' => 27500,  'stock' => 70,   'units' => [['BAL', 25, 675000]]],
            ['cat' => 'Kresek', 'code' => 'KW1-N28-DAU', 'name' => 'Kresek Los Natural 28 Daun K1',  'unit' => 'KG',  'price' => 26500,  'stock' => 25,   'units' => [['BAL', 25, 637500]]],
            ['cat' => 'Kresek', 'code' => 'KW1-N28-HPY', 'name' => 'Kresek Los Natural 28 Happy K1', 'unit' => 'KG',  'price' => 28500,  'stock' => 20,   'units' => [['BAL', 25, 675000]]],
            ['cat' => 'Kresek', 'code' => 'KW1-N28-LEO', 'name' => 'Kresek Los Natural 28 Leo K1',   'unit' => 'KG',  'price' => 27500,  'stock' => 55,   'units' => [['BAL', 25, 675000]]],
            ['cat' => 'Kresek', 'code' => 'KRS-P15-ATL', 'name' => 'Kresek Los Putih 15 AT',         'unit' => 'KG',  'price' => 33000,  'stock' => 30,   'units' => [['BAL', 25, 800000]]],
            ['cat' => 'Kresek', 'code' => 'KRS-P24-ATL', 'name' => 'Kresek Los Putih 24 AT',         'unit' => 'KG',  'price' => 33000,  'stock' => 20,   'units' => [['BAL', 25, 800000]]],
            ['cat' => 'Kresek', 'code' => 'KRS-P28-ATL', 'name' => 'Kresek Los Putih 28 AT',         'unit' => 'KG',  'price' => 33000,  'stock' => 15,   'units' => [['BAL', 25, 800000]]],
            ['cat' => 'Kresek', 'code' => 'KRS-P32-ATL', 'name' => 'Kresek Los Putih 32 AT',         'unit' => 'KG',  'price' => 33000,  'stock' => 35,   'units' => [['BAL', 25, 800000]]],
            ['cat' => 'Kresek', 'code' => 'KRS-P35-ATL', 'name' => 'Kresek Los Putih 35 AT',         'unit' => 'KG',  'price' => 33000,  'stock' => 40,   'units' => [['BAL', 25, 800000]]],
            ['cat' => 'Kresek', 'code' => 'KRS-P40-ATL', 'name' => 'Kresek Los Putih 40 AT',         'unit' => 'KG',  'price' => 33000,  'stock' => 45,   'units' => [['BAL', 25, 800000]]],
            ['cat' => 'Kresek', 'code' => 'KRS-W24-ATL', 'name' => 'Kresek Los Warna 24 AT',         'unit' => 'KG',  'price' => 33000,  'stock' => 20,   'units' => [['BAL', 25, 800000]]],
            ['cat' => 'Kresek', 'code' => 'KRS-W28-ATL', 'name' => 'Kresek Los Warna 28 AT',         'unit' => 'KG',  'price' => 33000,  'stock' => 30,   'units' => [['BAL', 25, 800000]]],
            ['cat' => 'Kresek', 'code' => 'KW2-N15-LEO', 'name' => 'Kresek Los Natural 15 Leo K2',   'unit' => 'KG',  'price' => 24000,  'stock' => 100,  'units' => [['BAL', 25, 575000]]],
            ['cat' => 'Kresek', 'code' => 'KW2-N24-LEO', 'name' => 'Kresek Los Natural 24 Leo K2',   'unit' => 'KG',  'price' => 24000,  'stock' => 90,   'units' => [['BAL', 25, 575000]]],
            ['cat' => 'Kresek', 'code' => 'KW2-N24-PRS', 'name' => 'Kresek Los Natural 24 Preston K2','unit' => 'KG', 'price' => 29000,  'stock' => 25,   'units' => [['BAL', 25, 712500]]],
            ['cat' => 'Kresek', 'code' => 'KW2-N28-HPY', 'name' => 'Kresek Los Natural 28 Happy K2', 'unit' => 'KG',  'price' => 22500,  'stock' => 30,   'units' => [['BAL', 25, 543750]]],
            ['cat' => 'Kresek', 'code' => 'KW2-N28-LEO', 'name' => 'Kresek Los Natural 28 Leo K2',   'unit' => 'KG',  'price' => 24000,  'stock' => 80,   'units' => [['BAL', 25, 575000]]],

            // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
            // KRESEK â€” PAK (satuan dasar PAK)
            // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
            ['cat' => 'Kresek', 'code' => 'KRS-L28-DEL', 'name' => 'Kresek Lorek 28 Deal',                   'unit' => 'PAK', 'price' => 10000,  'stock' => 50,   'units' => []],
            ['cat' => 'Kresek', 'code' => 'KRS-L32-BOY', 'name' => 'Kresek Lorek 32 Boy',                    'unit' => 'PAK', 'price' => 8000,   'stock' => 30,   'units' => [['IKT', 10, 76000], ['BAL', 80, 600000]]],
            ['cat' => 'Kresek', 'code' => 'KRS-L32-UFU', 'name' => 'Kresek Lorek Ungu 32 FU',                'unit' => 'PAK', 'price' => 8000,   'stock' => 60,   'units' => [['IKT', 5, 37500], ['BAL', 110, 803000]]],
            ['cat' => 'Kresek', 'code' => 'KRS-M15-CJD', 'name' => 'Kresek Merah 15 Cahaya Jadi',            'unit' => 'PAK', 'price' => 4500,   'stock' => 100,  'units' => [['IKT', 10, 40000], ['BAL', 250, 950000]]],
            ['cat' => 'Kresek', 'code' => 'KRS-M24-CJD', 'name' => 'Kresek Merah 24 Cahaya Jadi',            'unit' => 'PAK', 'price' => 5000,   'stock' => 40,   'units' => []],
            ['cat' => 'Kresek', 'code' => 'KRS-M28-CJD', 'name' => 'Kresek Merah 28 Cahaya Jadi',            'unit' => 'PAK', 'price' => 10000,  'stock' => 25,   'units' => [['IKT', 10, 92000], ['BAL', 100, 900000]]],
            ['cat' => 'Kresek', 'code' => 'KRS-M15-MRC', 'name' => 'Kresek Merah 15 Mercu',                  'unit' => 'PAK', 'price' => 2000,   'stock' => 200,  'units' => [['IKT', 10, 17500], ['BAL', 500, 825000]]],
            ['cat' => 'Kresek', 'code' => 'KRS-N15-BNK', 'name' => 'Kresek Natural 15 Bhineka',              'unit' => 'PAK', 'price' => 2000,   'stock' => 300,  'units' => [['IKT', 10, 14000], ['BAL', 500, 650000]]],
            ['cat' => 'Kresek', 'code' => 'KRS-N15-BYO', 'name' => 'Kresek Natural 15 Boyo',                 'unit' => 'PAK', 'price' => 7000,   'stock' => 80,   'units' => []],
            ['cat' => 'Kresek', 'code' => 'KRS-N15-JGO', 'name' => 'Kresek Natural 15 Jago Merah',           'unit' => 'PAK', 'price' => 6000,   'stock' => 150,  'units' => [['IKT', 5, 28000], ['BAL', 150, 810000]]],
            ['cat' => 'Kresek', 'code' => 'KRS-N15-MBU', 'name' => 'Kresek Natural 15 Mobil Ungu',           'unit' => 'PAK', 'price' => 1500,   'stock' => 200,  'units' => [['IKT', 10, 12000], ['BAL', 600, 690000]]],
            ['cat' => 'Kresek', 'code' => 'KRS-N21-MBU', 'name' => 'Kresek Natural 21 Mobil Ungu',           'unit' => 'PAK', 'price' => 2500,   'stock' => 100,  'units' => [['IKT', 10, 21000], ['BAL', 400, 800000]]],
            ['cat' => 'Kresek', 'code' => 'KRS-N21-MRC', 'name' => 'Kresek Natural 21 Mercu',                'unit' => 'PAK', 'price' => 3000,   'stock' => 180,  'units' => [['IKT', 10, 25000], ['BAL', 400, 960000]]],
            ['cat' => 'Kresek', 'code' => 'KRS-N24-BYO', 'name' => 'Kresek Natural 24 Boyo',                 'unit' => 'PAK', 'price' => 7000,   'stock' => 70,   'units' => []],
            ['cat' => 'Kresek', 'code' => 'KRS-N24-JGO', 'name' => 'Kresek Natural 24 Jago Merah',           'unit' => 'PAK', 'price' => 6000,   'stock' => 120,  'units' => [['IKT', 5, 28000], ['BAL', 150, 810000]]],
            ['cat' => 'Kresek', 'code' => 'KRS-N28-CMR', 'name' => 'Kresek Natural 28 Camar',                'unit' => 'PAK', 'price' => 7500,   'stock' => 40,   'units' => [['IKT', 5, 35000]]],
            ['cat' => 'Kresek', 'code' => 'KRS-N28-JGO', 'name' => 'Kresek Natural 28 Jago Merah',           'unit' => 'PAK', 'price' => 6000,   'stock' => 100,  'units' => [['IKT', 5, 28000], ['BAL', 150, 810000]]],
            ['cat' => 'Kresek', 'code' => 'KRS-OZW-MRC', 'name' => 'Kresek OZ 1 Warna Mercu',               'unit' => 'PAK', 'price' => 4000,   'stock' => 60,   'units' => [['IKT', 5, 15500], ['BAL', 200, 600000]]],
            ['cat' => 'Kresek', 'code' => 'KRS-OZ1-MRC', 'name' => 'Kresek OZ 1 Mercu',                      'unit' => 'PAK', 'price' => 4000,   'stock' => 120,  'units' => [['IKT', 5, 16000], ['BAL', 200, 620000]]],
            ['cat' => 'Kresek', 'code' => 'KRS-OZ1-SOL', 'name' => 'Kresek OZ 1 Saola',                      'unit' => 'PAK', 'price' => 4000,   'stock' => 250,  'units' => [['IKT', 10, 32500], ['BAL', 300, 960000]]],
            ['cat' => 'Kresek', 'code' => 'KRS-OZ2-SOL', 'name' => 'Kresek OZ 2 Saola',                      'unit' => 'PAK', 'price' => 4000,   'stock' => 230,  'units' => [['IKT', 10, 32500], ['BAL', 300, 960000]]],
            ['cat' => 'Kresek', 'code' => 'KRS-P15-BMA', 'name' => 'Kresek Putih 15 Bima',                   'unit' => 'PAK', 'price' => 6000,   'stock' => 80,   'units' => [['IKT', 5, 25000], ['BAL', 200, 940000]]],
            ['cat' => 'Kresek', 'code' => 'KRS-P15-CJD', 'name' => 'Kresek Putih 15 Cahaya Jadi',            'unit' => 'PAK', 'price' => 4500,   'stock' => 50,   'units' => [['IKT', 10, 40000], ['BAL', 250, 950000]]],
            ['cat' => 'Kresek', 'code' => 'KRS-P21-BMA', 'name' => 'Kresek Putih 21 Bima',                   'unit' => 'PAK', 'price' => 8000,   'stock' => 90,   'units' => [['IKT', 5, 35000], ['BAL', 125, 812500]]],
            ['cat' => 'Kresek', 'code' => 'KRS-P24-CJD', 'name' => 'Kresek Putih 24 Cahaya Jadi',            'unit' => 'PAK', 'price' => 7500,   'stock' => 30,   'units' => [['IKT', 10, 70000], ['BAL', 150, 1020000]]],
            ['cat' => 'Kresek', 'code' => 'KRS-P2B-BMA', 'name' => 'Kresek Putih 28 Bima',                   'unit' => 'PAK', 'price' => 10000,  'stock' => 60,   'units' => [['IKT', 5, 49000], ['BAL', 80, 752000]]],
            ['cat' => 'Kresek', 'code' => 'KRS-10B-RYL', 'name' => 'Kresek Putih R10B Royal',                'unit' => 'PAK', 'price' => 17500,  'stock' => 40,   'units' => [['IKT', 5, 85000], ['BAL', 20, 330000]]],
            ['cat' => 'Kresek', 'code' => 'KRS-10K-RYL', 'name' => 'Kresek Putih R10K Royal',                'unit' => 'PAK', 'price' => 13500,  'stock' => 50,   'units' => [['IKT', 5, 65000], ['BAL', 30, 375000]]],
            ['cat' => 'Kresek', 'code' => 'KRS-RR8-RYL', 'name' => 'Kresek Putih R8 Royal',                  'unit' => 'PAK', 'price' => 12500,  'stock' => 60,   'units' => [['IKT', 5, 60000], ['BAL', 30, 345000]]],
            ['cat' => 'Kresek', 'code' => 'KRS-P28-KRX', 'name' => 'Kresek Putih 28 Kresx',                  'unit' => 'PAK', 'price' => 12000,  'stock' => 35,   'units' => [['IKT', 10, 105000], ['BAL', 50, 475000]]],
            ['cat' => 'Kresek', 'code' => 'KRS-P15-STR', 'name' => 'Kresek Pink 15 Str Pink',                'unit' => 'PAK', 'price' => 2500,   'stock' => 45,   'units' => [['IKT', 10, 25000]]],
            ['cat' => 'Kresek', 'code' => 'KRS-P15-BYB', 'name' => 'Kresek Putih 15 Boyo Biru',              'unit' => 'PAK', 'price' => 3500,   'stock' => 80,   'units' => [['IKT', 10, 29000], ['BAL', 240, 648000]]],
            ['cat' => 'Kresek', 'code' => 'KRS-P15-DRG', 'name' => 'Kresek Putih 15 Dragon',                 'unit' => 'PAK', 'price' => 3000,   'stock' => 200,  'units' => [['IKT', 10, 27000], ['BAL', 500, 1300000]]],
            ['cat' => 'Kresek', 'code' => 'KRS-P15-KDA', 'name' => 'Kresek Putih 15 Kuda',                   'unit' => 'PAK', 'price' => 2000,   'stock' => 250,  'units' => [['IKT', 10, 13000], ['BAL', 600, 720000]]],
            ['cat' => 'Kresek', 'code' => 'KRS-P15-MRC', 'name' => 'Kresek Putih 15 Mercu',                  'unit' => 'PAK', 'price' => 2000,   'stock' => 220,  'units' => [['IKT', 10, 17500], ['BAL', 500, 825000]]],
            ['cat' => 'Kresek', 'code' => 'KRS-P21-BYB', 'name' => 'Kresek Putih 21 Boyo Biru',              'unit' => 'PAK', 'price' => 5000,   'stock' => 70,   'units' => [['IKT', 10, 42000], ['BAL', 200, 800000]]],
            ['cat' => 'Kresek', 'code' => 'KRS-P24-BYB', 'name' => 'Kresek Putih 24 Boyo Biru',              'unit' => 'PAK', 'price' => 6000,   'stock' => 80,   'units' => [['IKT', 10, 52000], ['BAL', 200, 1000000]]],
            ['cat' => 'Kresek', 'code' => 'KRS-P24-DRG', 'name' => 'Kresek Putih 24 Dragon',                 'unit' => 'PAK', 'price' => 5000,   'stock' => 150,  'units' => [['IKT', 10, 45000], ['BAL', 200, 870000]]],
            ['cat' => 'Kresek', 'code' => 'KRS-P24-MRC', 'name' => 'Kresek Putih 24 Mercu',                  'unit' => 'PAK', 'price' => 4000,   'stock' => 160,  'units' => [['IKT', 10, 36000], ['BAL', 250, 875000]]],
            ['cat' => 'Kresek', 'code' => 'KRS-P28-DRG', 'name' => 'Kresek Putih 28 Dragon',                 'unit' => 'PAK', 'price' => 8000,   'stock' => 100,  'units' => [['IKT', 10, 72000], ['BAL', 120, 840000]]],
            ['cat' => 'Kresek', 'code' => 'KRS-P28-MRC', 'name' => 'Kresek Putih 28 Mercu',                  'unit' => 'PAK', 'price' => 6000,   'stock' => 110,  'units' => [['IKT', 10, 57000], ['BAL', 150, 840000]]],
            ['cat' => 'Kresek', 'code' => 'KRS-P30-MRC', 'name' => 'Kresek Putih 30 Mercu',                  'unit' => 'PAK', 'price' => 11000,  'stock' => 60,   'units' => [['IKT', 10, 100000], ['BAL', 100, 980000]]],
            ['cat' => 'Kresek', 'code' => 'KRS-P35-MRC', 'name' => 'Kresek Putih 35 Mercu',                  'unit' => 'PAK', 'price' => 15000,  'stock' => 50,   'units' => [['IKT', 10, 145000], ['BAL', 75, 1012500]]],
            ['cat' => 'Kresek', 'code' => 'KRS-P35-RMW', 'name' => 'Kresek Putih 35 Romawi',                 'unit' => 'PAK', 'price' => 11000,  'stock' => 40,   'units' => [['IKT', 10, 105000], ['BAL', 80, 800000]]],
            ['cat' => 'Kresek', 'code' => 'KRS-SHT-SBT', 'name' => 'Kresek Suju Hitam Sabit',               'unit' => 'PAK', 'price' => 46000,  'stock' => 10,   'units' => [['BAL', 30, 1350000]]],
            ['cat' => 'Kresek', 'code' => 'KRS-SJH-SBT', 'name' => 'Kresek Suju Hijau Sabit',               'unit' => 'PAK', 'price' => 50000,  'stock' => 10,   'units' => [['BAL', 30, 1470000]]],
            ['cat' => 'Kresek', 'code' => 'KRS-U28-SAO', 'name' => 'Kresek Ungu 28 Saola',                   'unit' => 'PAK', 'price' => 6000,   'stock' => 75,   'units' => [['IKT', 5, 28000], ['BAL', 150, 825000]]],
            ['cat' => 'Kresek', 'code' => 'KRS-W15-MRC', 'name' => 'Kresek Warna 15 Mercu',                  'unit' => 'PAK', 'price' => 2000,   'stock' => 120,  'units' => [['IKT', 10, 17500], ['BAL', 500, 825000]]],
            ['cat' => 'Kresek', 'code' => 'KRS-W24-MRC', 'name' => 'Kresek Warna 24 Mercu',                  'unit' => 'PAK', 'price' => 4000,   'stock' => 90,   'units' => [['IKT', 10, 36000], ['BAL', 250, 875000]]],
            ['cat' => 'Kresek', 'code' => 'KRS-OZW-STB', 'name' => 'Kresek OZ Warna Stabilo',               'unit' => 'PAK', 'price' => 6000,   'stock' => 50,   'units' => [['BAL', 50, 250000]]],

            // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
            // PLASTIK
            // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
            ['cat' => 'Plastik', 'code' => 'WRP-F45-JSX', 'name' => 'Wrap Film 45x500 JS',                'unit' => 'ROL', 'price' => 200000, 'stock' => 20,   'units' => [['BAL', 6, 1140000]]],
            ['cat' => 'Plastik', 'code' => 'BTL-15L-AQA', 'name' => 'Botol Aqua 1.5L',                    'unit' => 'BAL', 'price' => 92800,  'stock' => 15,   'units' => []],
            ['cat' => 'Plastik', 'code' => 'BTL-330-AQA', 'name' => 'Botol Aqua 330ML',                   'unit' => 'BAL', 'price' => 70200,  'stock' => 25,   'units' => []],
            ['cat' => 'Plastik', 'code' => 'MBX-BBR-KRS', 'name' => 'Mika Box Bubur Coklat 3A Karisma',   'unit' => 'BH',  'price' => 1000,   'stock' => 300,  'units' => [['PAK', 20, 16000], ['DOS', 600, 450000]]],

            // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
            // KERTAS
            // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
            ['cat' => 'Kertas', 'code' => 'CUP-MTF-CRT', 'name' => 'Cup Agr Motif Cristy',                'unit' => 'PAK', 'price' => 6500,   'stock' => 80,   'units' => [['PRS', 12, 72000]]],
            ['cat' => 'Kertas', 'code' => 'CUP-M38-FRA', 'name' => 'Cup Bruder 3.8x4 Motif Fora',        'unit' => 'ROL', 'price' => 42500,  'stock' => 30,   'units' => [['DOS', 64, 2688000]]],
            ['cat' => 'Kertas', 'code' => 'CUP-BKR-BLT', 'name' => 'Cup Bakery Bulat',                    'unit' => 'SAP', 'price' => 5000,   'stock' => 150,  'units' => [['ROL', 20, 45000]]],
            ['cat' => 'Kertas', 'code' => 'CUP-BKR-GLS', 'name' => 'Cup Bakery Gelas',                    'unit' => 'SAP', 'price' => 5500,   'stock' => 100,  'units' => [['ROL', 10, 45000]]],
            ['cat' => 'Kertas', 'code' => 'CUP-BOV-BEF', 'name' => 'Cup Bakery Oval BF',                  'unit' => 'SAP', 'price' => 5000,   'stock' => 200,  'units' => [['ROL', 20, 45000]]],
            ['cat' => 'Kertas', 'code' => 'CUP-BOM-BEF', 'name' => 'Cup Bakery Oval BF M',                'unit' => 'SAP', 'price' => 5000,   'stock' => 60,   'units' => [['ROL', 20, 56000]]],
            ['cat' => 'Kertas', 'code' => 'CUP-BOS-BEF', 'name' => 'Cup Bakery Oval BF S',                'unit' => 'ROL', 'price' => 28000,  'stock' => 25,   'units' => [['BAL', 30, 780000]]],
            ['cat' => 'Kertas', 'code' => 'CUP-H55-GRD', 'name' => 'Cup Hitam 5.5 Grade',                 'unit' => 'ROL', 'price' => 17500,  'stock' => 80,   'units' => [['BAL', 50, 825000]]],
            ['cat' => 'Kertas', 'code' => 'CUP-ICE-BSR', 'name' => 'Cup Ice Cream Besar',                 'unit' => 'PAK', 'price' => 7500,   'stock' => 60,   'units' => [['BAL', 40, 270000]]],
            ['cat' => 'Kertas', 'code' => 'CUP-ICE-KCL', 'name' => 'Cup Ice Cream Kecil',                 'unit' => 'PAK', 'price' => 7000,   'stock' => 90,   'units' => [['BAL', 60, 390000]]],
            ['cat' => 'Kertas', 'code' => 'CUP-M55-BEF', 'name' => 'Cup Muffin 5.5 BF (50)',              'unit' => 'ROL', 'price' => 18000,  'stock' => 35,   'units' => [['BAL', 68, 1156000]]],
            ['cat' => 'Kertas', 'code' => 'CUP-M63-IFN', 'name' => 'Cup Muffin 6.3 Ifana',               'unit' => 'ROL', 'price' => 30000,  'stock' => 20,   'units' => [['BAL', 25, 725000]]],
            ['cat' => 'Kertas', 'code' => 'CUP-M63-BEF', 'name' => 'Cup Muffin 6.3 BF (50)',              'unit' => 'ROL', 'price' => 20500,  'stock' => 40,   'units' => [['BAL', 60, 1170000]]],
            ['cat' => 'Kertas', 'code' => 'CUP-MFN-MDM', 'name' => 'Cup Muffin Medium',                   'unit' => 'ROL', 'price' => 21000,  'stock' => 30,   'units' => [['BAL', 60, 1200000]]],
            ['cat' => 'Kertas', 'code' => 'CUP-MFN-SML', 'name' => 'Cup Muffin Small',                    'unit' => 'ROL', 'price' => 20000,  'stock' => 25,   'units' => [['BAL', 60, 1080000]]],
            ['cat' => 'Kertas', 'code' => 'CUP-MNI-BGA', 'name' => 'Cup Mini Bunga',                      'unit' => 'P100','price' => 6500,   'stock' => 100,  'units' => [['PAK', 12, 72000]]],
            ['cat' => 'Kertas', 'code' => 'CUP-W95-TLP', 'name' => 'Cup Warna 9.5 Tulip',                 'unit' => 'SAP', 'price' => 550,    'stock' => 500,  'units' => [['ROL', 40, 18000]]],
        ];

        foreach ($products as $p) {
            $productId = DB::table('products')->insertGetId([
                'category_id'    => $catId($p['cat']),
                'product_code'   => $p['code'],
                'name'           => $p['name'],
                'slug'           => Str::slug($p['name']) . '-' . strtolower(Str::random(4)),
                'unit'           => $p['unit'],
                'description'    => null,
                'price'          => $p['price'],
                'discount_price' => null,
                'weight'         => 0,
                'stock'          => $p['stock'],
                'stock_alert'    => 5,
                'is_active'      => true,
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);

            // Insert unit conversions
            foreach ($p['units'] as [$unit, $conversionValue, $price]) {
                DB::table('product_units')->insert([
                    'product_id'       => $productId,
                    'unit'             => $unit,
                    'conversion_value' => $conversionValue,
                    'price'            => $price,
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ]);
            }
        }
    }
}
