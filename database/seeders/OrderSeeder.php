<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        // ── Helper: get user_id by email ────────────────────────────────────
        $uid = fn(string $email) => DB::table('users')->where('email', $email)->value('id');

        // ── Helper: get product by name ─────────────────────────────────────
        $prod = fn(string $name) => DB::table('products')->where('name', 'like', "%{$name}%")->first();

        // ── Shorthand user refs ─────────────────────────────────────────────
        $u1 = $uid('budi.santoso@email.com');
        $u2 = $uid('siti.rahayu@email.com');
        $u3 = $uid('ahmad.fauzi@email.com');
        $u4 = $uid('dewi.lestari@email.com');
        $u5 = $uid('hendra.wijaya@email.com');
        $u6 = $uid('rina.kusuma@email.com');
        $u7 = $uid('doni.prasetyo@email.com');
        $u8 = $uid('wulan.sari@email.com');

        // ── Orders definition ───────────────────────────────────────────────
        // Each order: [user_id, date, status, shipping_type, items[]]
        // items: [product_name_keyword, qty]
        $orders = [
            // January 2026
            ['user' => $u1, 'date' => '2026-01-05', 'status' => 'completed',       'ship_fee' => 0,     'ship_name' => 'Pickup',      'addr' => 'Jl. Veteran No. 12, Blitar',
             'items' => [['Kantong Plastik HD 24', 3], ['Kantong Kresek', 2]]],

            ['user' => $u2, 'date' => '2026-01-08', 'status' => 'completed',       'ship_fee' => 15000, 'ship_name' => 'Kurir Toko',  'addr' => 'Jl. Merdeka No. 5, Blitar',
             'items' => [['Sendok Makan Plastik (1 lusin)', 4], ['Garpu Plastik', 4], ['Sendok Teh Plastik', 2]]],

            ['user' => $u3, 'date' => '2026-01-12', 'status' => 'cancelled',       'ship_fee' => 15000, 'ship_name' => 'Kurir Toko',  'addr' => 'Jl. Sudirman No. 88, Blitar',
             'items' => [['Toples Plastik Bulat', 4]]],

            ['user' => $u4, 'date' => '2026-01-15', 'status' => 'completed',       'ship_fee' => 0,     'ship_name' => 'Pickup',      'addr' => 'Jl. Diponegoro No. 23, Malang',
             'items' => [['Set Sendok Garpu Plastik', 2], ['Kantong Sampah Hitam', 1]]],

            ['user' => $u5, 'date' => '2026-01-18', 'status' => 'completed',       'ship_fee' => 15000, 'ship_name' => 'Kurir Toko',  'addr' => 'Jl. Cokroaminoto No. 7, Blitar',
             'items' => [['Sedotan Plastik Bening', 5], ['Sedotan Plastik Jumbo', 3], ['Tusuk Gigi', 2]]],

            ['user' => $u1, 'date' => '2026-01-22', 'status' => 'completed',       'ship_fee' => 0,     'ship_name' => 'Pickup',      'addr' => 'Jl. Veteran No. 12, Blitar',
             'items' => [['Kantong Plastik HD 30', 2], ['Kantong Zipper', 3]]],

            ['user' => $u6, 'date' => '2026-01-25', 'status' => 'completed',       'ship_fee' => 0,     'ship_name' => 'Pickup',      'addr' => 'Jl. Ahmad Yani No. 45, Surabaya',
             'items' => [['Botol Plastik 600ml', 2], ['Toples Plastik Kotak', 2]]],

            ['user' => $u7, 'date' => '2026-01-28', 'status' => 'completed',       'ship_fee' => 15000, 'ship_name' => 'Kurir Toko',  'addr' => 'Jl. Imam Bonjol No. 3, Blitar',
             'items' => [['Centong Nasi Plastik', 2], ['Centong Sayur', 2], ['Spatula Plastik', 1]]],

            // February 2026
            ['user' => $u8, 'date' => '2026-02-02', 'status' => 'completed',       'ship_fee' => 0,     'ship_name' => 'Pickup',      'addr' => 'Jl. Hayam Wuruk No. 11, Kediri',
             'items' => [['Kantong Plastik HD 30', 2], ['Kantong Plastik Plong', 3]]],

            ['user' => $u2, 'date' => '2026-02-05', 'status' => 'completed',       'ship_fee' => 15000, 'ship_name' => 'Kurir Toko',  'addr' => 'Jl. Merdeka No. 5, Blitar',
             'items' => [['Sendok Sup Plastik', 3], ['Sendok Es Krim', 4], ['Garpu Buah', 2]]],

            ['user' => $u3, 'date' => '2026-02-08', 'status' => 'completed',       'ship_fee' => 0,     'ship_name' => 'Pickup',      'addr' => 'Jl. Sudirman No. 88, Blitar',
             'items' => [['Botol Plastik 1 Liter', 2], ['Wadah Serbaguna', 1]]],

            ['user' => $u4, 'date' => '2026-02-10', 'status' => 'completed',       'ship_fee' => 0,     'ship_name' => 'Pickup',      'addr' => 'Jl. Diponegoro No. 23, Malang',
             'items' => [['Toples Plastik Tabung', 2], ['Toples Plastik Kotak', 2], ['Botol Plastik 250ml', 1]]],

            ['user' => $u5, 'date' => '2026-02-12', 'status' => 'completed',       'ship_fee' => 15000, 'ship_name' => 'Kurir Toko',  'addr' => 'Jl. Cokroaminoto No. 7, Blitar',
             'items' => [['Set Sendok Garpu Plastik', 3], ['Sendok Makan Plastik (1 lusin)', 2]]],

            ['user' => $u6, 'date' => '2026-02-15', 'status' => 'waiting_payment', 'ship_fee' => 0,     'ship_name' => 'Pickup',      'addr' => 'Jl. Ahmad Yani No. 45, Surabaya',
             'items' => [['Plastik PE Transparan', 2], ['Kantong Plastik HD 40', 1]]],

            ['user' => $u7, 'date' => '2026-02-18', 'status' => 'completed',       'ship_fee' => 15000, 'ship_name' => 'Kurir Toko',  'addr' => 'Jl. Imam Bonjol No. 3, Blitar',
             'items' => [['Kantong Plastik HD 24', 4], ['Kantong Sampah Hitam', 2]]],

            ['user' => $u1, 'date' => '2026-02-20', 'status' => 'shipped',         'ship_fee' => 15000, 'ship_name' => 'Kurir Toko',  'addr' => 'Jl. Veteran No. 12, Blitar',
             'items' => [['Sedotan Plastik Jumbo', 5], ['Tusuk Gigi', 3]]],

            ['user' => $u8, 'date' => '2026-02-23', 'status' => 'completed',       'ship_fee' => 0,     'ship_name' => 'Pickup',      'addr' => 'Jl. Hayam Wuruk No. 11, Kediri',
             'items' => [['Sendok Bayi', 2], ['Sendok Makan Plastik Besar', 3]]],

            ['user' => $u2, 'date' => '2026-02-25', 'status' => 'ready_for_pickup','ship_fee' => 0,     'ship_name' => 'Pickup',      'addr' => 'Jl. Merdeka No. 5, Blitar',
             'items' => [['Botol Plastik 600ml', 3], ['Botol Plastik 250ml', 2]]],

            // March 2026
            ['user' => $u3, 'date' => '2026-03-02', 'status' => 'paid',            'ship_fee' => 15000, 'ship_name' => 'Kurir Toko',  'addr' => 'Jl. Sudirman No. 88, Blitar',
             'items' => [['Sendok Teh Plastik', 3], ['Garpu Plastik', 3]]],

            ['user' => $u4, 'date' => '2026-03-05', 'status' => 'completed',       'ship_fee' => 0,     'ship_name' => 'Pickup',      'addr' => 'Jl. Diponegoro No. 23, Malang',
             'items' => [['Kantong Plastik HD 30', 3], ['Kantong Kresek', 2]]],

            ['user' => $u5, 'date' => '2026-03-07', 'status' => 'completed',       'ship_fee' => 15000, 'ship_name' => 'Kurir Toko',  'addr' => 'Jl. Cokroaminoto No. 7, Blitar',
             'items' => [['Centong Nasi Plastik', 3], ['Spatula Plastik', 2], ['Sendok Sup Plastik', 2]]],

            ['user' => $u6, 'date' => '2026-03-10', 'status' => 'processing',      'ship_fee' => 15000, 'ship_name' => 'Kurir Toko',  'addr' => 'Jl. Ahmad Yani No. 45, Surabaya',
             'items' => [['Sedotan Plastik Bening', 8], ['Sedotan Plastik Jumbo', 4]]],

            ['user' => $u7, 'date' => '2026-03-12', 'status' => 'waiting_payment', 'ship_fee' => 0,     'ship_name' => 'Pickup',      'addr' => 'Jl. Imam Bonjol No. 3, Blitar',
             'items' => [['Toples Plastik Tabung', 2], ['Wadah Serbaguna', 1]]],

            ['user' => $u8, 'date' => '2026-03-15', 'status' => 'ready_for_pickup','ship_fee' => 0,     'ship_name' => 'Pickup',      'addr' => 'Jl. Hayam Wuruk No. 11, Kediri',
             'items' => [['Set Sendok Garpu Plastik', 5], ['Tusuk Gigi', 3]]],

            ['user' => $u1, 'date' => '2026-03-17', 'status' => 'processing',      'ship_fee' => 15000, 'ship_name' => 'Kurir Toko',  'addr' => 'Jl. Veteran No. 12, Blitar',
             'items' => [['Plastik PE Transparan', 3], ['Kantong Zipper', 4], ['Kantong Plastik HD 40', 1]]],
        ];

        // ── Insert orders ────────────────────────────────────────────────────
        $counters = []; // track invoice sequence per date

        foreach ($orders as $i => $o) {
            $date = $o['date'];
            $counters[$date] = ($counters[$date] ?? 0) + 1;
            $invoiceNumber   = 'INV' . str_replace('-', '', $date) . str_pad($counters[$date], 4, '0', STR_PAD_LEFT);
            $orderDate       = Carbon::parse($date . ' ' . sprintf('%02d:%02d:00', rand(8, 17), rand(0, 59)));

            // Calculate subtotals from items
            $subtotal = 0;
            $itemRows = [];
            foreach ($o['items'] as [$keyword, $qty]) {
                $p = $prod($keyword);
                if (!$p) continue;
                $price    = $p->discount_price ?? $p->price;
                $lineTotal = $price * $qty;
                $subtotal += $lineTotal;
                $itemRows[] = [
                    'product_id'    => $p->id,
                    'product_name'  => $p->name,
                    'product_price' => $price,
                    'quantity'      => $qty,
                    'subtotal'      => $lineTotal,
                ];
            }

            $total = $subtotal + $o['ship_fee'];

            // Insert order
            $orderId = DB::table('orders')->insertGetId([
                'invoice_number'  => $invoiceNumber,
                'user_id'         => $o['user'],
                'shipping_cost_id'=> null,
                'shipping_name'   => $o['ship_name'],
                'recipient_name'  => DB::table('users')->where('id', $o['user'])->value('name'),
                'recipient_phone' => DB::table('users')->where('id', $o['user'])->value('phone'),
                'shipping_address'=> $o['addr'],
                'subtotal'        => $subtotal,
                'discount_amount' => 0,
                'points_used'     => 0,
                'points_discount' => 0,
                'shipping_fee'    => $o['ship_fee'],
                'total'           => $total,
                'status'          => $o['status'],
                'notes'           => null,
                'created_at'      => $orderDate,
                'updated_at'      => $orderDate,
            ]);

            // Insert order items
            foreach ($itemRows as $item) {
                DB::table('order_items')->insert(array_merge($item, [
                    'order_id'   => $orderId,
                    'created_at' => $orderDate,
                    'updated_at' => $orderDate,
                ]));
            }

            // Insert payment for completed & paid & ready_for_pickup & shipped orders
            if (in_array($o['status'], ['completed', 'paid', 'ready_for_pickup', 'shipped', 'processing'])) {
                $paidAt = $o['status'] === 'completed'
                    ? $orderDate->copy()->addMinutes(rand(5, 30))
                    : ($o['status'] === 'paid' ? $orderDate->copy()->addMinutes(rand(5, 60)) : null);

                $methods = ['gopay', 'qris'];
                $method  = $methods[array_rand($methods)];

                DB::table('payments')->insert([
                    'order_id'           => $orderId,
                    'payment_type'       => $method,
                    'transaction_id'     => 'TXN-' . strtoupper($invoiceNumber) . '-' . rand(1000, 9999),
                    'transaction_status' => $o['status'] === 'completed' ? 'settlement' : 'capture',
                    'gross_amount'       => $total,
                    'snap_token'         => null,
                    'payment_url'        => null,
                    'payment_detail'     => null,
                    'paid_at'            => $paidAt,
                    'created_at'         => $orderDate,
                    'updated_at'         => $orderDate,
                ]);
            }
        }
    }
}
