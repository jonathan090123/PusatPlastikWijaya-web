<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
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

        $cart->load('items.product.category');

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
        ]);

        // Luar Kota Blitar tidak boleh pakai Kurir Toko
        if ($request->shipping_city_type === 'outside' && $request->shipping_type === 'local') {
            return back()->with('error', 'Kurir Toko hanya tersedia untuk area Kota Blitar.');
        }

        $cart = Cart::where('user_id', Auth::id())->first();

        if (!$cart || $cart->items->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Keranjang belanja kosong.');
        }

        $cart->load('items.product');

        // Validate stock availability
        foreach ($cart->items as $item) {
            if (!$item->product->is_active) {
                return back()->with('error', "Produk \"{$item->product->name}\" sudah tidak tersedia.");
            }
            if ($item->product->stock < $item->quantity) {
                return back()->with('error', "Stok \"{$item->product->name}\" tidak mencukupi. Tersisa {$item->product->stock}.");
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

        try {
            $order = DB::transaction(function () use ($cart, $shippingFee, $shippingName, $shippingCostId, $request) {
                // Calculate subtotal
                $subtotal = 0;
                foreach ($cart->items as $item) {
                    $subtotal += $item->product->getEffectivePrice() * $item->quantity;
                }

                $total = $subtotal + $shippingFee;

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
                    'points_used' => 0,
                    'points_discount' => 0,
                    'shipping_fee' => $shippingFee,
                    'total' => $total,
                    'status' => 'pending',
                    'notes' => $request->notes,
                ]);

                // Create order items (stock reduced only after payment confirmed)
                foreach ($cart->items as $item) {
                    $price = $item->product->getEffectivePrice();

                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $item->product->id,
                        'product_name' => $item->product->name,
                        'product_price' => $price,
                        'quantity' => $item->quantity,
                        'subtotal' => $price * $item->quantity,
                    ]);
                }

                // Clear cart
                $cart->items()->delete();

                return $order;
            });

            return redirect()->route('payment.show', $order)->with('success', 'Pesanan berhasil dibuat! Silakan selesaikan pembayaran.');
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan saat membuat pesanan. Silakan coba lagi.');
        }
    }
}
