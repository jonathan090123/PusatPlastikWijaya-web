<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\PointHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerOrderController extends Controller
{
    public function index()
    {
        // Auto-expire order yang melewati deadline
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
            $this->refundPointsIfNeeded($expOrder);
            $expOrder->update(['status' => 'expired', 'status_read_at' => null]);
        }

        $orders = Order::where('user_id', Auth::id())
            ->with('items.product', 'pointHistories')
            ->latest()
            ->paginate(10);

        // Ambil ID unread sebelum ditandai dibaca
        $unreadOrderIds = Order::where('user_id', Auth::id())
            ->whereNull('status_read_at')
            ->pluck('id')
            ->flip()
            ->toArray();

        Order::where('user_id', Auth::id())
            ->whereNull('status_read_at')
            ->update(['status_read_at' => now()]);

        $confirmableCount = Order::where('user_id', Auth::id())
            ->whereIn('status', ['shipped', 'ready_for_pickup'])
            ->count();

        return view('customer.orders.index', compact('orders', 'unreadOrderIds', 'confirmableCount'));
    }

    public function show(Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }

        // Auto-expire if deadline already passed
        if (in_array($order->status, ['pending', 'waiting_payment'])) {
            $deadline = $order->payment_deadline ?? $order->created_at->addHours(12);
            if (now()->gte($deadline)) {
                $this->restoreOrderStock($order);
                $this->refundPointsIfNeeded($order);
                $order->update(['status' => 'expired', 'status_read_at' => null]);
                $order->refresh();
            }
        }

        $order->load(['items.product', 'payment', 'shippingCost', 'pointHistories']);

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

        $deadline = $order->payment_deadline ?? $order->created_at->addHours(12);
        if (now()->gt($deadline)) {
            return back()->with('error', 'Batas waktu pembatalan telah habis.');
        }

        $this->restoreOrderStock($order);
        $this->refundPointsIfNeeded($order);
        $order->update([
            'status' => 'cancelled',
            'status_read_at' => null,
        ]);

        return redirect()->route('orders.index')
            ->with('success', 'Pesanan ' . $order->invoice_number . ' telah dibatalkan.');
    }

    /** AJAX: tandai order expired saat countdown habis. */
    public function expire(Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }

        $deadline = $order->payment_deadline ?? $order->created_at->addHours(12);

        if (in_array($order->status, ['pending', 'waiting_payment']) && now()->gte($deadline)) {
            $this->restoreOrderStock($order);
            $this->refundPointsIfNeeded($order);
            $order->update([
                'status' => 'expired',
                'status_read_at' => null,
            ]);
        }

        return response()->json(['ok' => true]);
    }

    /** Tambah semua item order ke keranjang (Beli Lagi). */
    public function reorder(Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }

        $order->load('items.product.productUnits');
        $cart = Cart::firstOrCreate(['user_id' => Auth::id()]);
        $added = 0;
        $insufficient = [];

        foreach ($order->items as $item) {
            $product = $item->product;
            if (!$product || !$product->is_active) {
                continue;
            }

            $unit = $item->unit ?: $product->unit;

            $conversionValue = 1;
            if ($unit !== $product->unit) {
                $pu = $product->productUnits->firstWhere('unit', $unit);
                if ($pu)
                    $conversionValue = $pu->conversion_value;
            }

            $requiredStock = $item->quantity * $conversionValue;

            if ($product->stock < $requiredStock) {
                $available = $conversionValue > 0 ? (int) floor($product->stock / $conversionValue) : 0;
                $insufficient[] = [
                    'name' => $product->name,
                    'requested' => $item->quantity,
                    'available' => $available,
                    'unit' => $unit,
                ];
                continue;
            }

            $existing = CartItem::where('cart_id', $cart->id)
                ->where('product_id', $product->id)
                ->where('unit', $unit)
                ->first();

            if ($existing) {
                $existing->increment('quantity', $item->quantity);
            } else {
                CartItem::create([
                    'cart_id' => $cart->id,
                    'product_id' => $product->id,
                    'quantity' => $item->quantity,
                    'unit' => $unit,
                ]);
            }
            $added++;
        }

        if ($added === 0 && empty($insufficient)) {
            return response()->json([
                'success' => false,
                'message' => 'Produk dari pesanan ini sudah tidak tersedia.',
                'insufficient' => [],
                'redirect' => null,
            ]);
        }

        return response()->json([
            'success' => true,
            'added' => $added,
            'insufficient' => $insufficient,
            'redirect' => empty($insufficient) ? route('cart.index') : null,
        ]);
    }

    // (pt) Customer klik "Pesanan Diterima" 
    public function complete(Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }

        if (!in_array($order->status, ['shipped', 'ready_for_pickup'])) {
            return back()->with('error', 'Pesanan ini tidak dapat diselesaikan.');
        }

        $order->update([
            'status' => 'completed',
            'status_read_at' => null,
        ]);

        // (pt) Cairkan poin ke customer setelah klik "Pesanan Diterima"
        $this->awardPoints($order->fresh());

        return back()->with('success', 'Pesanan ' . $order->invoice_number . ' telah diselesaikan. Terima kasih!');
    }

    // (pt) Hitung & cairkan poin ke customer: 1 poin per Rp 200 belanja
    private function awardPoints(Order $order): void
    {
        // Cegah poin dicairkan 2x untuk order yang sama
        if (PointHistory::where('order_id', $order->id)->where('type', 'earned')->exists()) {
            return;
        }

        // (pt) Rumus: 1 poin per Rp 200 belanja 
        $belanja = $order->subtotal - $order->discount_amount - $order->points_discount;
        $points = (int) floor($belanja / 200);
        if ($points <= 0) {
            return;
        }

        $order->user->increment('points', $points);

        PointHistory::create([
            'user_id' => $order->user_id,
            'order_id' => $order->id,
            'type' => 'earned',
            'amount' => $points,
            'description' => 'Poin dari pesanan ' . $order->invoice_number,
        ]);
    }

    // (pt) Kembalikan poin ke customer jika order dibatalkan/refund
    private function refundPointsIfNeeded(Order $order): void
    {
        if (($order->points_used ?? 0) <= 0)
            return;

        // Cegah pengembalian poin 2x
        $alreadyRefunded = PointHistory::where('order_id', $order->id)
            ->where('type', 'refunded')
            ->exists();
        if ($alreadyRefunded)
            return;

        $order->loadMissing('user');
        $order->user->increment('points', $order->points_used);

        PointHistory::create([
            'user_id' => $order->user_id,
            'order_id' => $order->id,
            'type' => 'refunded',
            'amount' => $order->points_used,
            'description' => 'Poin dikembalikan (pesanan ' . $order->invoice_number . ' dibatalkan/kadaluarsa)',
        ]);
    }

    // restore / kembalikan stok produk jika order dibatalkan
    private function restoreOrderStock(Order $order): void
    {
        $order->load('items.product.productUnits');
        foreach ($order->items as $item) {
            if (!$item->product)
                continue;
            $conv = 1;
            if ($item->unit && $item->unit !== $item->product->unit) {
                $pu = $item->product->productUnits->firstWhere('unit', $item->unit);
                if ($pu)
                    $conv = (int) $pu->conversion_value;
            }
            $item->product->increment('stock', $item->quantity * $conv);
        }
    }
}

