<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * Generates ~80 random dummy orders spread across Jan–Apr 2026
 * for scroll / filter testing.  Run with:
 *   php artisan db:seed --class=DummyOrderSeeder
 */
class DummyOrderSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [
            'completed', 'completed', 'completed', 'completed', // weight towards common ones
            'processing', 'processing',
            'shipped', 'shipped',
            'ready_for_pickup',
            'waiting_payment',
            'cancelled',
            'expired',
            'pending',
        ];

        $shippingOptions = [
            ['fee' => 0,     'name' => 'Pickup',     'type' => 'pickup'],
            ['fee' => 15000, 'name' => 'Kurir Toko', 'type' => 'local'],
            ['fee' => 25000, 'name' => 'JNE REG',    'type' => 'outside'],
            ['fee' => 20000, 'name' => 'J&T Express', 'type' => 'outside'],
        ];

        $addresses = [
            'Jl. Veteran No. 12, Blitar',
            'Jl. Merdeka No. 5, Blitar',
            'Jl. Sudirman No. 88, Blitar',
            'Jl. Diponegoro No. 23, Malang',
            'Jl. Cokroaminoto No. 7, Blitar',
            'Jl. Ahmad Yani No. 45, Surabaya',
            'Jl. Imam Bonjol No. 3, Blitar',
            'Jl. Hayam Wuruk No. 11, Kediri',
            'Jl. Gajah Mada No. 19, Blitar',
            'Jl. Raya Tulungagung No. 55, Tulungagung',
        ];

        // Grab all customer user IDs
        $userIds = DB::table('users')->where('role', 'customer')->pluck('id')->toArray();
        if (empty($userIds)) {
            $this->command->warn('No customer users found. Run UserSeeder first.');
            return;
        }

        // Grab products
        $products = DB::table('products')->where('is_active', true)->get()->toArray();
        if (empty($products)) {
            $this->command->warn('No products found. Run ProductSeeder first.');
            return;
        }

        $counters = [];

        // Spread over Jan 1 – Apr 14 2026 (roughly 104 days → ~80 orders)
        $start = Carbon::parse('2026-01-01');
        $end   = Carbon::parse('2026-04-14');
        $totalDays = $start->diffInDays($end);

        // Shuffle day offsets so orders aren't uniformly spaced
        $dayOffsets = range(0, $totalDays);
        shuffle($dayOffsets);
        $dayOffsets = array_slice($dayOffsets, 0, 80);
        sort($dayOffsets);

        foreach ($dayOffsets as $dayOffset) {
            $date      = $start->copy()->addDays($dayOffset);
            $dateKey   = $date->format('Y-m-d');
            $userId    = $userIds[array_rand($userIds)];
            $user      = DB::table('users')->where('id', $userId)->first();
            $status    = $statuses[array_rand($statuses)];
            $shipping  = $shippingOptions[array_rand($shippingOptions)];
            $address   = $addresses[array_rand($addresses)];

            $orderTime = $date->copy()->setTime(rand(8, 17), rand(0, 59), rand(0, 59));

            // Build 1–3 random items
            shuffle($products);
            $selectedProducts = array_slice($products, 0, rand(1, 3));
            $subtotal = 0;
            $itemRows = [];
            foreach ($selectedProducts as $p) {
                $qty       = rand(1, 5);
                $price     = $p->discount_price ?? $p->price;
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

            $total = $subtotal + $shipping['fee'];

            // Invoice number — prefix DUM to avoid conflicts with OrderSeeder
            $counters[$dateKey] = ($counters[$dateKey] ?? 0) + 1;
            $invoiceNumber = 'DUM' . str_replace('-', '', $dateKey) . str_pad($counters[$dateKey], 4, '0', STR_PAD_LEFT);

            // Insert order
            $orderId = DB::table('orders')->insertGetId([
                'invoice_number'   => $invoiceNumber,
                'user_id'          => $userId,
                'shipping_cost_id' => null,
                'shipping_name'    => $shipping['name'],
                'recipient_name'   => $user->name,
                'recipient_phone'  => $user->phone ?? '081234567890',
                'shipping_address' => $address,
                'subtotal'         => $subtotal,
                'discount_amount'  => 0,
                'points_used'      => 0,
                'points_discount'  => 0,
                'shipping_fee'     => $shipping['fee'],
                'total'            => $total,
                'status'           => $status,
                'notes'            => null,
                'created_at'       => $orderTime,
                'updated_at'       => $orderTime,
            ]);

            // Insert order items
            foreach ($itemRows as $item) {
                DB::table('order_items')->insert(array_merge($item, [
                    'order_id'   => $orderId,
                    'created_at' => $orderTime,
                    'updated_at' => $orderTime,
                ]));
            }

            // Insert payment for paid statuses
            if (in_array($status, ['completed', 'processing', 'shipped', 'ready_for_pickup'])) {
                $paidAt  = $orderTime->copy()->addMinutes(rand(5, 60));
                $methods = ['gopay', 'qris', 'bank_transfer'];
                $method  = $methods[array_rand($methods)];

                DB::table('payments')->insert([
                    'order_id'           => $orderId,
                    'payment_type'       => $method,
                    'transaction_id'     => 'TXN-' . strtoupper(str_replace('-', '', $invoiceNumber)) . '-' . rand(1000, 9999),
                    'transaction_status' => $status === 'completed' ? 'settlement' : 'capture',
                    'gross_amount'       => $total,
                    'snap_token'         => null,
                    'payment_url'        => null,
                    'payment_detail'     => null,
                    'paid_at'            => $paidAt,
                    'created_at'         => $orderTime,
                    'updated_at'         => $orderTime,
                ]);
            }
        }

        $this->command->info('DummyOrderSeeder: inserted 80 dummy orders.');
    }
}
