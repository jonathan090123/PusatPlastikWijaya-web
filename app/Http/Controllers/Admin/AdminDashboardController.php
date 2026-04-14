<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    private array $recentStatuses = ['processing', 'shipped', 'ready_for_pickup', 'pending'];

    public function index()
    {
        $totalOrders   = Order::count();
        $totalRevenue  = Order::where('status', 'completed')->sum('total');
        $totalProducts  = Product::count();
        $totalCustomers = User::where('role', 'customer')->count();

        $recentPaginator = Order::with('user')
            ->whereIn('status', $this->recentStatuses)
            ->latest()
            ->paginate(9);

        $recentOrders          = $recentPaginator->items();
        $recentOrdersHasMore   = $recentPaginator->hasMorePages();

        $lowStockProducts = Product::with('category')
            ->where('is_active', true)
            ->whereColumn('stock', '<=', 'stock_alert')
            ->orderBy('stock')
            ->take(10)
            ->get();

        // Capture unread IDs BEFORE marking as read — badge shows only on first load
        $newOrderIds = Order::whereNull('admin_read_at')
            ->whereNotIn('status', ['cancelled', 'completed', 'expired'])
            ->pluck('id')
            ->flip()
            ->toArray();

        Order::whereNull('admin_read_at')
            ->whereNotIn('status', ['cancelled', 'completed', 'expired'])
            ->update(['admin_read_at' => now()]);

        $newOrdersCount = count($newOrderIds);

        return view('admin.dashboard', compact(
            'totalOrders',
            'totalRevenue',
            'totalProducts',
            'totalCustomers',
            'recentOrders',
            'recentOrdersHasMore',
            'lowStockProducts',
            'newOrdersCount',
            'newOrderIds'
        ));
    }

    public function recentOrdersAjax(Request $request)
    {
        $page = max(1, (int) $request->query('page', 1));

        $paginator = Order::with('user')
            ->whereIn('status', $this->recentStatuses)
            ->latest()
            ->paginate(9, ['*'], 'page', $page);

        $orders = collect($paginator->items())->map(fn($order) => [
            'id'             => $order->id,
            'invoice_number' => $order->invoice_number,
            'user_name'      => $order->user->name ?? '-',
            'total'          => number_format($order->total, 0, ',', '.'),
            'status'         => $order->status,
            'status_label'   => $order->status_label,
            'url'            => route('admin.orders.show', $order),
        ]);

        return response()->json([
            'orders'    => $orders,
            'has_more'  => $paginator->hasMorePages(),
            'next_page' => $page + 1,
        ]);
    }
}
