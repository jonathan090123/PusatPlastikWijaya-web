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
            ->take(5)
            ->get();

        $lowStockProducts = Product::with('category')
            ->where('is_active', true)
            ->whereColumn('stock', '<=', 'stock_alert')
            ->orderBy('stock')
            ->take(5)
            ->get();

        $newOrdersCount = Order::whereIn('status', ['pending', 'waiting_payment'])->count();

        // IDs of today's newest orders — passed to JS for sessionStorage new-order notification
        $todayOrderIds  = Order::whereDate('created_at', today())
            ->orderByDesc('id')
            ->pluck('id');

        return view('admin.dashboard', compact(
            'totalOrders',
            'totalRevenue',
            'totalProducts',
            'totalCustomers',
            'recentOrders',
            'lowStockProducts',
            'newOrdersCount',
            'todayOrderIds'
        ));
    }
}
