<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PointHistory;
use App\Models\Product;
use Illuminate\Http\Request;

class AdminOrderController extends Controller
{
    public function index(Request $request)
    {
        $doneStatuses = ['completed', 'cancelled', 'expired'];

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

        // Capture unread IDs BEFORE marking as read — badge shows only on first visit
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

        // Mark this order as read by admin when detail page is opened
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
            'status' => 'required|in:pending,waiting_payment,paid,processing,ready_for_pickup,shipped,completed,cancelled',
        ]);

        $previousStatus = $order->status;

        $order->update([
            'status'         => $request->status,
            'status_read_at' => null,  // mark as unread for customer
        ]);

        // Award loyalty points & deduct stock when order is marked completed for the first time
        if ($request->status === 'completed' && $previousStatus !== 'completed') {
            $this->awardPoints($order->fresh());
            $this->deductStock($order->fresh());
        }

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
        abort_if($item->is_out_of_stock, 422, 'Item sudah ditandai stok kosong.');

        $item->update([
            'is_out_of_stock' => true,
            'out_of_stock_at' => now(),
        ]);

        // ── Karena produk ditandai stok kosong, ubah stoknya menjadi 0 agar tidak bisa dibeli lagi ───────────
        if ($item->product_id) {
            Product::where('id', $item->product_id)->update(['stock' => 0]);
        }

        // ── Jika poin sudah diberikan (order completed), kurangi poin proporsional
        if ($order->status === 'completed') {
            $deduct = (int) floor((float) $item->subtotal / 200);
            if ($deduct > 0) {
                $currentPoints = $order->user->points ?? 0;
                $deduct = min($deduct, $currentPoints); // jangan sampai minus
                if ($deduct > 0) {
                    $order->user->decrement('points', $deduct);
                    PointHistory::create([
                        'user_id'     => $order->user_id,
                        'order_id'    => $order->id,
                        'type'        => 'deducted',
                        'amount'      => $deduct,
                        'description' => 'Koreksi poin: ' . $item->product_name . ' (stok kosong) - ' . $order->invoice_number,
                    ]);
                }
            }

            // Kembalikan stok juga jika order sudah selesai (stok sudah dikurangi sebelumnya)
            // Ini sudah ditangani oleh increment di atas.
        }

        return back()->with('success', 'Item "' . $item->product_name . '" ditandai stok kosong. Stok produk utama telah diubah menjadi 0.');
    }

    private function awardPoints(Order $order): void
    {
        // Guard: only award once per order
        if (PointHistory::where('order_id', $order->id)->where('type', 'earned')->exists()) {
            return;
        }

        // Hitung subtotal hanya dari item yang TIDAK stok kosong
        $order->loadMissing('items');
        $activeSubtotal = $order->items->where('is_out_of_stock', false)->sum('subtotal');

        // Proporsi diskon terhadap total subtotal order
        $totalDiscount = $order->discount_amount + $order->points_discount;
        $belanja = max(0, $activeSubtotal - $totalDiscount);

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

    /**
     * Kurangi stok produk untuk semua item yang terpenuhi (bukan stok kosong).
     * Hanya dijalankan sekali saat order pertama kali selesai.
     * Guard: dicek via kolom status (hanya dipanggil dari updateStatus saat previousStatus !== 'completed').
     */
    private function deductStock(Order $order): void
    {
        $order->loadMissing('items');

        foreach ($order->items->where('is_out_of_stock', false) as $item) {
            if (!$item->product_id) {
                continue;
            }

            $product = Product::find($item->product_id);
            if (!$product) {
                continue;
            }

            // Jangan biarkan stok minus
            $deduct = min((int) $item->quantity, max(0, (int) $product->stock));
            if ($deduct > 0) {
                $product->decrement('stock', $deduct);
            }
        }
    }
}
