<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PointHistory;
use App\Models\ShippingCost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    public function index()
    {
        $cart = Cart::where('user_id', Auth::id())->first();

        if (!$cart || $cart->items->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Keranjang belanja kosong.');
        }

        $cart->load('items.product.category', 'items.product.productUnits');

        // Load all active shipping methods from DB
        $shippingMethods = ShippingCost::whereIn('type', ['pickup', 'local', 'outside'])
            ->get()
            ->keyBy('type');
        $user = Auth::user();

        return view('customer.checkout.index', compact('cart', 'shippingMethods', 'user'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'recipient_name' => 'required|string|max:255',
            'recipient_phone' => 'required|string|max:20',
            'shipping_city_type' => 'required|in:blitar,outside',
            'shipping_address' => 'required|string|max:1000',
            'shipping_type' => 'required|in:pickup,local,outside',
            'notes' => 'nullable|string|max:500',
            'use_points' => 'nullable|integer|min:0',
        ]);

        // Luar Kota Blitar tidak boleh pakai Kurir Toko
        if ($request->shipping_city_type === 'outside' && $request->shipping_type === 'local') {
            return back()->with('error', 'Kurir Toko hanya tersedia untuk area Kota Blitar.');
        }

        $cart = Cart::where('user_id', Auth::id())->first();

        if (!$cart || $cart->items->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Keranjang belanja kosong.');
        }

        $cart->load('items.product.productUnits');

        // Validate stock availability (unit-aware)
        foreach ($cart->items as $item) {
            if (!$item->product->is_active) {
                return back()->with('error', "Produk \"{$item->product->name}\" sudah tidak tersedia.");
            }
            $conv = 1;
            if ($item->unit && $item->unit !== $item->product->unit) {
                $pu = $item->product->productUnits->firstWhere('unit', $item->unit);
                if ($pu) $conv = (int) $pu->conversion_value;
            }
            $required = $item->quantity * $conv;
            if ($item->product->stock < $required) {
                $avail     = $conv > 1 ? (int) floor($item->product->stock / $conv) : $item->product->stock;
                $unitLabel = $item->unit ? strtoupper($item->unit) : strtoupper($item->product->unit);
                return back()->with('error', "Stok \"{$item->product->name}\" tidak mencukupi. Tersisa {$avail} {$unitLabel}.");
            }
        }

        // Determine shipping
        $shippingType = $request->shipping_type;
        $method = ShippingCost::where('type', $shippingType)->first();

        if (!$method || !$method->is_active) {
            return back()->with('error', 'Metode pengiriman yang dipilih tidak tersedia.');
        }

        $shippingFee = $method->cost;
        $shippingName = $method->name;
        $shippingCostId = $method->id;

        // Determine points to use
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $requestedPoints = (int) ($request->use_points ?? 0);
        $pointsToUse = min($requestedPoints, $user->points);

        try {
            $order = DB::transaction(function () use ($cart, $shippingFee, $shippingName, $shippingCostId, $request, $pointsToUse, $user) {
                // Calculate subtotal (unit-aware pricing)
                $subtotal = 0;
                foreach ($cart->items as $item) {
                    $subtotal += $item->product->getPriceForUnit($item->unit) * $item->quantity;
                }

                // Cap points discount so total never goes below 0
                $pointsDiscount = min($pointsToUse, $subtotal + $shippingFee);
                $pointsUsed = (int) $pointsDiscount; // 1 poin = Rp 1

                $total = $subtotal - $pointsDiscount + $shippingFee;

                // Create order
                $order = Order::create([
                    'invoice_number' => Order::generateInvoiceNumber(),
                    'user_id' => Auth::id(),
                    'shipping_cost_id' => $shippingCostId,
                    'shipping_name' => $shippingName,
                    'recipient_name' => $request->recipient_name,
                    'recipient_phone' => $request->recipient_phone,
                    'shipping_address' => $request->shipping_address,
                    'subtotal' => $subtotal,
                    'discount_amount' => 0,
                    'points_used' => $pointsUsed,
                    'points_discount' => $pointsDiscount,
                    'shipping_fee' => $shippingFee,
                    'total' => $total,
                    'status' => 'waiting_payment',
                    'notes' => $request->notes,
                ]);

                // Create order items & immediately reserve (deduct) stock
                foreach ($cart->items as $item) {
                    $price = $item->product->getPriceForUnit($item->unit);

                    OrderItem::create([
                        'order_id'      => $order->id,
                        'product_id'    => $item->product->id,
                        'product_name'  => $item->product->name,
                        'product_price' => $price,
                        'unit'          => $item->unit,
                        'quantity'      => $item->quantity,
                        'subtotal'      => $price * $item->quantity,
                    ]);

                    // Deduct stock immediately (restored on expire / cancel)
                    $conv = 1;
                    if ($item->unit && $item->unit !== $item->product->unit) {
                        $pu = $item->product->productUnits->firstWhere('unit', $item->unit);
                        if ($pu) $conv = (int) $pu->conversion_value;
                    }
                    $item->product->decrement('stock', $item->quantity * $conv);
                }

                // Clear cart
                $cart->items()->delete();

                // Deduct used points and record history
                if ($pointsUsed > 0) {
                    $user->decrement('points', $pointsUsed);
                    PointHistory::create([
                        'user_id'     => $user->id,
                        'order_id'    => $order->id,
                        'type'        => 'used',
                        'amount'      => $pointsUsed,
                        'description' => 'Penukaran poin untuk pesanan ' . $order->invoice_number,
                    ]);
                }

                return $order;
            });

            return redirect()->route('payment.show', $order)->with('success', 'Pesanan berhasil dibuat! Silakan selesaikan pembayaran.');
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan saat membuat pesanan. Silakan coba lagi.');
        }
    }
}
