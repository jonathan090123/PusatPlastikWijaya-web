<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;

class CustomerOrderController extends Controller
{
    public function index()
    {
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

        $deadline = $order->created_at->addHours(2);
        if (now()->gt($deadline)) {
            return back()->with('error', 'Batas waktu pembatalan telah habis.');
        }

        $order->update([
            'status'         => 'cancelled',
            'status_read_at' => null,
        ]);

        return redirect()->route('orders.index')
            ->with('success', 'Pesanan ' . $order->invoice_number . ' telah dibatalkan.');
    }
}

