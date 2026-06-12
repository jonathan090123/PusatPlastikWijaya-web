<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PointHistory;
use App\Models\ShippingCost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Services\RajaOngkirService;

class CheckoutController extends Controller
{
    public function index()
    {
        $cart = $this->getCart();
        if (!$cart) {
            return redirect()->route('cart.index')->with('error', 'Keranjang belanja kosong.');
        }

        $cart->load('items.product.category', 'items.product.productUnits');
        $shippingMethods = ShippingCost::whereIn('type', ['pickup', 'local', 'outside'])
            ->get()
            ->keyBy('type');
        $user = Auth::user();

        $rajaOngkirAvailable = app(RajaOngkirService::class)->isConfigured();
        $totalWeight = $this->calculateCartWeight($cart);

        return view('customer.checkout.index', compact('cart', 'shippingMethods', 'user', 'rajaOngkirAvailable', 'totalWeight'));
    }

    public function store(Request $request)
    {
        $this->validateRequest($request);

        if ($request->shipping_city_type === 'outside' && $request->shipping_type === 'local') {
            return back()->with('error', 'Kurir Toko hanya tersedia untuk area Kota Blitar.');
        }

        $cart = $this->getCart();
        if (!$cart) {
            return redirect()->route('cart.index')->with('error', 'Keranjang belanja kosong.');
        }

        $cart->load('items.product.productUnits');

        if ($response = $this->validateCartStock($cart)) {
            return $response;
        }

        [$shippingFee, $shippingName, $shippingCostId, $shippingError] = $this->resolveShipping($request, $cart);
        if ($shippingError) {
            return back()->with('error', $shippingError);
        }

        $user = Auth::user();
        [$pointsToUse, $pointsDiscount, $pointsUsed] = $this->calculatePoints($request, $cart);

        try {
            $order = DB::transaction(function () use ($cart, $shippingFee, $shippingName, $shippingCostId, $request, $pointsDiscount, $pointsUsed, $user) {
                $subtotal = $this->calculateCartSubtotal($cart);
                $total = $subtotal - $pointsDiscount + $shippingFee;

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

                $this->createOrderItemsAndDeductStock($order, $cart);
                $this->createPointHistory($user, $order, $pointsUsed);
                $cart->items()->delete();

                return $order;
            });

            return redirect()->route('payment.show', $order)->with('success', 'Pesanan berhasil dibuat! Silakan selesaikan pembayaran.');
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan saat membuat pesanan. Silakan coba lagi.');
        }
    }

    private function validateRequest(Request $request): void
    {
        $request->validate([
            'recipient_name' => 'required|string|max:255',
            'recipient_phone' => 'required|string|max:20',
            'shipping_city_type' => 'required|in:blitar,outside',
            'shipping_address' => 'required|string|max:1000',
            'shipping_type' => 'required|in:pickup,local,outside',
            'notes' => 'nullable|string|max:500',
            'use_points' => 'nullable|integer|min:0',
            'ongkir_cost' => 'required_if:shipping_type,outside|nullable|numeric|min:0',
            'ongkir_courier' => 'required_if:shipping_type,outside|nullable|string|max:100',
            'ongkir_service' => 'required_if:shipping_type,outside|nullable|string|max:100',
            'ongkir_destination_id' => 'required_if:shipping_type,outside|nullable|integer|min:1',
            'ongkir_destination' => 'nullable|string|max:200',
            'ongkir_etd' => 'nullable|string|max:50',
        ]);
    }

    private function getCart(): ?Cart
    {
        return Cart::where('user_id', Auth::id())->first();
    }

    private function calculateCartWeight(Cart $cart): int
    {
        $weight = 0;
        foreach ($cart->items as $item) {
            $conv = $this->getConversionValue($item);
            $productWeight = $item->product->weight > 0 ? $item->product->weight : 500;
            $weight += $productWeight * $item->quantity * $conv;
        }

        return $weight;
    }

    private function calculateCartSubtotal(Cart $cart): int
    {
        $subtotal = 0;
        foreach ($cart->items as $item) {
            $subtotal += $item->product->getPriceForUnit($item->unit) * $item->quantity;
        }

        return $subtotal;
    }

    private function validateCartStock(Cart $cart)
    {
        foreach ($cart->items as $item) {
            if (!$item->product->is_active) {
                return back()->with('error', "Produk \"{$item->product->name}\" sudah tidak tersedia.");
            }

            $required = $item->quantity * $this->getConversionValue($item);
            if ($item->product->stock < $required) {
                $avail = $this->getAvailableStockText($item);
                return back()->with('error', "Stok \"{$item->product->name}\" tidak mencukupi. Tersisa {$avail}.");
            }
        }

        return null;
    }

    private function resolveShipping(Request $request, Cart $cart): array
    {
        if ($request->shipping_type === 'outside') {
            $method = ShippingCost::where('type', 'outside')->first();
            if (!$method || !$method->is_active) {
                return [0, '', 0, 'Pengiriman luar kota belum tersedia.'];
            }

            $shippingFee = (int) $request->ongkir_cost;
            $shippingName = 'Ekspedisi ' . $request->ongkir_courier;
            if ($request->ongkir_etd) {
                $shippingName .= ' (Est. ' . $request->ongkir_etd . ' hari)';
            }

            if ($request->ongkir_destination) {
                $totalWeight = $this->calculateCartWeight($cart);
                $options = app(RajaOngkirService::class)->getShippingOptions($request->ongkir_destination_id, $totalWeight);
                $verified = false;
                foreach ($options as $opt) {
                    $optCourier = strtoupper($opt['code']) . ' ' . $opt['service'];
                    if ($optCourier === $request->ongkir_service && (int) $opt['cost'] === $shippingFee) {
                        $verified = true;
                        break;
                    }
                }
                if (!$verified) {
                    return [0, '', 0, 'Ongkir tidak valid. Silakan cek ulang ongkos kirim.'];
                }
            }

            return [$shippingFee, $shippingName, $method->id, ''];
        }

        $method = ShippingCost::where('type', $request->shipping_type)->first();
        if (!$method || !$method->is_active) {
            return [0, '', 0, 'Metode pengiriman yang dipilih tidak tersedia.'];
        }

        return [$method->cost, $method->name, $method->id, ''];
    }

    private function calculatePoints(Request $request, Cart $cart): array
    {
        $requestedPoints = (int) ($request->use_points ?? 0);
        $pointsToUse = min($requestedPoints, Auth::user()->points);
        $pointsDiscount = min($pointsToUse, $this->calculateCartSubtotal($cart));
        $pointsUsed = (int) $pointsDiscount;

        return [$pointsToUse, $pointsDiscount, $pointsUsed];
    }

    private function createOrderItemsAndDeductStock(Order $order, Cart $cart): void
    {
        foreach ($cart->items as $item) {
            $price = $item->product->getPriceForUnit($item->unit);

            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item->product->id,
                'product_name' => $item->product->name,
                'product_price' => $price,
                'unit' => $item->unit,
                'quantity' => $item->quantity,
                'subtotal' => $price * $item->quantity,
            ]);

            $item->product->decrement('stock', $item->quantity * $this->getConversionValue($item));
        }
    }

    private function createPointHistory($user, Order $order, int $pointsUsed): void
    {
        if ($pointsUsed <= 0) {
            return;
        }

        $user->decrement('points', $pointsUsed);
        PointHistory::create([
            'user_id' => $user->id,
            'order_id' => $order->id,
            'type' => 'used',
            'amount' => $pointsUsed,
            'description' => 'Penukaran poin untuk pesanan ' . $order->invoice_number,
        ]);
    }

    private function getConversionValue(CartItem $item): int
    {
        if (!$item->unit || $item->unit === $item->product->unit) {
            return 1;
        }

        $pu = $item->product->productUnits->firstWhere('unit', $item->unit);
        return $pu ? (int) $pu->conversion_value : 1;
    }

    private function getAvailableStockText($item): string
    {
        $conv = $this->getConversionValue($item);
        $avail = $conv > 1 ? (int) floor($item->product->stock / $conv) : $item->product->stock;
        $unitLabel = $item->unit ? strtoupper($item->unit) : strtoupper($item->product->unit);

        return $avail . ' ' . $unitLabel;
    }
}
