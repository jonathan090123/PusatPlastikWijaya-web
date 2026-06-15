<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PointHistory;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminOrderController extends Controller
{
    public function index(Request $request)
    {
        $doneStatuses = ['completed', 'refunded', 'cancelled', 'expired'];

        $query = Order::with('user')
            ->orderByRaw("CASE WHEN status IN ('" . implode("','", $doneStatuses) . "') THEN 1 ELSE 0 END ASC")
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhere('recipient_name', 'like', "%{$search}%")
                  ->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $orders = $query->paginate(15)->withQueryString();

        // Ambil ID unread sebelum ditandai dibaca
        $newOrderIds = Order::whereNull('admin_read_at')
            ->whereIn('status', ['pending', 'waiting_payment'])
            ->pluck('id')
            ->flip()
            ->toArray();

        Order::whereNull('admin_read_at')
             ->whereIn('status', ['pending', 'waiting_payment'])
             ->update(['admin_read_at' => now()]);

        return view('admin.orders.index', compact('orders', 'newOrderIds'));
    }

    public function show(Order $order)
    {
        $order->load(['user', 'items.product', 'payment', 'shippingCost']);

        // Tandai order sudah dibaca
        if (is_null($order->admin_read_at)) {
            $order->update(['admin_read_at' => now()]);
        }

        return view('admin.orders.show', compact('order'));
    }



    public function invoice(Order $order)
    {
        $order->load(['user', 'items.product', 'payment', 'shippingCost']);
        return view('admin.orders.invoice', compact('order'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:pending,waiting_payment,paid,processing,ready_for_pickup,shipped,completed,refunded,cancelled',
        ]);

        $newStatus = $request->status;

        // Hanya boleh ke 'refunded' dari 'completed'
        if ($newStatus === 'refunded' && $order->status !== 'completed') {
            return back()->with('error', 'Refund hanya bisa dilakukan untuk pesanan yang sudah selesai.');
        }

        DB::transaction(function () use ($order, $newStatus) {
            // Lock baris order
            $locked = Order::lockForUpdate()->find($order->id);

            $previousStatus = $locked->status;

            $locked->update([
                'status'         => $newStatus,
                'status_read_at' => null, // mark as unread for customer
            ]);

            // (pt) Cairkan poin ke customer & kurangi stok saat status jadi "completed"
            if ($newStatus === 'completed' && $previousStatus !== 'completed') {
                $this->awardPoints($locked->fresh());
                $this->deductStock($locked->fresh());
            }

            // (pt) Tarik kembali poin yang sudah dicairkan jika order di-refund
            if ($newStatus === 'refunded' && $previousStatus === 'completed') {
                $this->deductEarnedPointsForRefund($locked->fresh());
            }
        });

        return redirect()->route('admin.orders.index')->with('success', 'Status pesanan berhasil diperbarui menjadi "' . $order->fresh()->status_label . '".');
    }

    public function updateTracking(Request $request, Order $order)
    {
        $request->validate([
            'tracking_number' => 'nullable|string|max:100',
        ]);

        $order->update([
            'tracking_number' => $request->tracking_number ?: null,
            'status_read_at'  => null, // notify customer of update
        ]);

        return back()->with('success', $request->tracking_number
            ? 'Nomor resi berhasil disimpan.'
            : 'Nomor resi berhasil dihapus.');
    }

    public function markItemOutOfStock(Request $request, Order $order, OrderItem $item)
    {
        abort_if($item->order_id !== $order->id, 403);

        DB::transaction(function () use ($order, $item) {
            // Lock baris item
            $lockedItem = OrderItem::lockForUpdate()->find($item->id);

            // Re-check dalam lock
            abort_if($lockedItem->is_out_of_stock, 422, 'Item sudah ditandai stok kosong.');

            $lockedItem->update([
                'is_out_of_stock' => true,
                'out_of_stock_at' => now(),
            ]);

            // Set stok produk ke 0
            if ($lockedItem->product_id) {
                Product::lockForUpdate()->where('id', $lockedItem->product_id)->update(['stock' => 0]);
            }

        // Kurangi poin customer jika item out of stock & order sudah completed/refunded
            $lockedOrder = Order::with('user')->lockForUpdate()->find($order->id);
            if (in_array($lockedOrder->status, ['completed', 'refunded'])) {
                $deduct = (int) floor((float) $lockedItem->subtotal / 200);
                if ($deduct > 0) {
                    $currentPoints = $lockedOrder->user->points ?? 0;
                    $deduct = min($deduct, $currentPoints); // jangan sampai minus
                    if ($deduct > 0) {
                        $lockedOrder->user->decrement('points', $deduct);
                        PointHistory::create([
                            'user_id'     => $lockedOrder->user_id,
                            'order_id'    => $lockedOrder->id,
                            'type'        => 'deducted',
                            'amount'      => $deduct,
                            'description' => 'Koreksi poin: ' . $lockedItem->product_name . ' (stok kosong) - ' . $lockedOrder->invoice_number,
                        ]);
                    }
                }
            }
        });

        return back()->with('success', 'Item "' . $item->product_name . '" ditandai stok kosong. Stok produk utama telah diubah menjadi 0.');
    }

    // (pt) Tarik kembali poin yang sudah dicairkan saat order di-refund oleh admin
    private function deductEarnedPointsForRefund(Order $order): void
    {
        $earnedHistory = PointHistory::lockForUpdate()
            ->where('order_id', $order->id)
            ->where('type', 'earned')
            ->first();

        if (!$earnedHistory) {
            return; // tidak ada poin earned, skip
        }

        // Guard: jangan double-deduct refund
        $alreadyDeducted = PointHistory::where('order_id', $order->id)
            ->where('type', 'deducted')
            ->where('description', 'like', '%refund%')
            ->exists();

        if ($alreadyDeducted) {
            return;
        }

        $order->loadMissing('user');
        $deduct       = $earnedHistory->amount;
        $currentPoints = $order->user->points ?? 0;
        $actualDeduct  = min($deduct, $currentPoints); // jangan sampai minus

        if ($actualDeduct > 0) {
            $order->user->decrement('points', $actualDeduct);
        }

        PointHistory::create([
            'user_id'     => $order->user_id,
            'order_id'    => $order->id,
            'type'        => 'deducted',
            'amount'      => $actualDeduct,
            'description' => 'Penarikan poin refund pesanan ' . $order->invoice_number,
        ]);
    }

    // (pt) function cairakan point kalo selesai
    private function awardPoints(Order $order): void
    {
        // Cegah poin dicairkan 2x untuk order yang sama
        $alreadyAwarded = PointHistory::lockForUpdate()
            ->where('order_id', $order->id)
            ->where('type', 'earned')
            ->exists();

        if ($alreadyAwarded) {
            return;
        }

        $order->loadMissing('items');
        $activeSubtotal = $order->items->where('is_out_of_stock', false)->sum('subtotal');
        $totalDiscount = $order->discount_amount + $order->points_discount;
        $belanja = max(0, $activeSubtotal - $totalDiscount);

        // (pt) Konversi: 1 poin = Rp 200 belanja bersih
        $points = (int) floor($belanja / 200);
        if ($points <= 0) {
            return;
        }

        $order->user->increment('points', $points);

        PointHistory::create([
            'user_id'     => $order->user_id,
            'order_id'    => $order->id,
            'type'        => 'earned',
            'amount'      => $points,
            'description' => 'Poin dari pesanan ' . $order->invoice_number,
        ]);
    }

    /** Kurangi stok produk saat order pertama kali selesai. */
    private function deductStock(Order $order): void
    {
        $order->loadMissing('items');

        foreach ($order->items->where('is_out_of_stock', false) as $item) {
            if (!$item->product_id) {
                continue;
            }

            // Lock baris produk
            $product = Product::lockForUpdate()->find($item->product_id);
            if (!$product) {
                continue;
            }

            // Hindari stok minus
            $deduct = min((int) $item->quantity, max(0, (int) $product->stock));
            if ($deduct > 0) {
                $product->decrement('stock', $deduct);
            }
        }
    }
}
