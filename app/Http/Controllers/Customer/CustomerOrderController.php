<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerOrderController extends Controller
{
    public function index()
    {
        // Auto-expire any unpaid orders whose deadline has passed & restore their stock
        $expiring = Order::where('user_id', Auth::id())
            ->whereIn('status', ['pending', 'waiting_payment'])
            ->where(function ($q) {
                $q->where('payment_deadline', '<=', now())
                  ->orWhere(function ($q2) {
                      $q2->whereNull('payment_deadline')
                         ->where('created_at', '<=', now()->subHours(2));
                  });
            })
            ->get();

        foreach ($expiring as $expOrder) {
            $this->restoreOrderStock($expOrder);
            $expOrder->update(['status' => 'expired', 'status_read_at' => null]);
        }

        $orders = Order::where('user_id', Auth::id())
            ->with('items.product')
            ->latest()
            ->paginate(10);

        return view('customer.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }

        // Auto-expire if deadline already passed
        if (in_array($order->status, ['pending', 'waiting_payment'])) {
            $deadline = $order->payment_deadline ?? $order->created_at->addHours(2);
            if (now()->gte($deadline)) {
                $this->restoreOrderStock($order);
                $order->update(['status' => 'expired', 'status_read_at' => null]);
                $order->refresh();
            }
        }

        $order->load(['items.product', 'payment', 'shippingCost']);

        // Mark notification as read
        if (is_null($order->status_read_at)) {
            $order->update(['status_read_at' => now()]);
        }

        return view('customer.orders.show', compact('order'));
    }

    public function cancel(Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }

        if (!in_array($order->status, ['pending', 'waiting_payment'])) {
            return back()->with('error', 'Pesanan ini tidak dapat dibatalkan.');
        }

        $deadline = $order->payment_deadline ?? $order->created_at->addHours(2);
        if (now()->gt($deadline)) {
            return back()->with('error', 'Batas waktu pembatalan telah habis.');
        }

        $this->restoreOrderStock($order);
        $order->update([
            'status'         => 'cancelled',
            'status_read_at' => null,
        ]);

        return redirect()->route('orders.index')
            ->with('success', 'Pesanan ' . $order->invoice_number . ' telah dibatalkan.');
    }

    /**
     * AJAX: mark order as expired when client-side countdown reaches zero.
     */
    public function expire(Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }

        $deadline = $order->payment_deadline ?? $order->created_at->addHours(2);

        if (in_array($order->status, ['pending', 'waiting_payment']) && now()->gte($deadline)) {
            $this->restoreOrderStock($order);
            $order->update([
                'status'         => 'expired',
                'status_read_at' => null,
            ]);
        }

        return response()->json(['ok' => true]);
    }

    /**
     * Add all items from an order back into the cart (Beli Lagi).
     */
    public function reorder(Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }

        $order->load('items.product');
        $cart  = Cart::firstOrCreate(['user_id' => Auth::id()]);
        $added = 0;

        foreach ($order->items as $item) {
            $product = $item->product;
            if (!$product || !$product->is_active || $product->stock < 1) {
                continue;
            }

            $unit     = $item->unit ?: $product->unit;
            $existing = CartItem::where('cart_id', $cart->id)
                ->where('product_id', $product->id)
                ->where('unit', $unit)
                ->first();

            if ($existing) {
                $existing->increment('quantity', $item->quantity);
            } else {
                CartItem::create([
                    'cart_id'    => $cart->id,
                    'product_id' => $product->id,
                    'quantity'   => $item->quantity,
                    'unit'       => $unit,
                ]);
            }
            $added++;
        }

        if ($added === 0) {
            return back()->with('error', 'Produk dari pesanan ini sudah tidak tersedia.');
        }

        return redirect()->route('cart.index')
            ->with('success', $added . ' produk berhasil ditambahkan ke keranjang.');
    }

    private function restoreOrderStock(Order $order): void
    {
        $order->load('items.product.productUnits');
        foreach ($order->items as $item) {
            if (!$item->product) continue;
            $conv = 1;
            if ($item->unit && $item->unit !== $item->product->unit) {
                $pu = $item->product->productUnits->firstWhere('unit', $item->unit);
                if ($pu) $conv = (int) $pu->conversion_value;
            }
            $item->product->increment('stock', $item->quantity * $conv);
        }
    }
}

