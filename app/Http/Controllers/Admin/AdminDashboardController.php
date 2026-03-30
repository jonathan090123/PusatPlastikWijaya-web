<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $totalOrders   = Order::count();
        $totalRevenue  = Order::where('status', 'completed')->sum('total');
        $totalProducts  = Product::count();
        $totalCustomers = User::where('role', 'customer')->count();

        $recentOrders = Order::with('user')
            ->latest()
            ->take(9)
            ->get();

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
            'lowStockProducts',
            'newOrdersCount',
            'newOrderIds'
        ));
    }
}
