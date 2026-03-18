<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminReportsController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->input('period', 'month');
        $startDate = $this->getStartDate($period, $request);
        $endDate   = $this->getEndDate($period, $request);

        // Only completed orders count as revenue
        $completedQuery = Order::where('status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate]);

        // All non-cancelled orders for order stats
        $allOrdersQuery = Order::whereBetween('created_at', [$startDate, $endDate]);

        // ── Summary Stats ──
        $totalRevenue      = (clone $completedQuery)->sum('total');
        $totalOrders       = (clone $allOrdersQuery)->count();
        $completedOrders   = (clone $completedQuery)->count();
        $cancelledOrders   = (clone $allOrdersQuery)->where('status', 'cancelled')->count();
        $totalItemsSold    = OrderItem::whereHas('order', function ($q) use ($startDate, $endDate) {
            $q->where('status', 'completed')->whereBetween('created_at', [$startDate, $endDate]);
        })->sum('quantity');
        $avgOrderValue     = $completedOrders > 0 ? $totalRevenue / $completedOrders : 0;

        // ── Revenue Chart (daily for current period) ──
        $revenueChart = Order::where('status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(total) as revenue'), DB::raw('COUNT(*) as orders'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // ── Best Selling Products (top 10) ──
        $bestSellingProducts = OrderItem::whereHas('order', function ($q) use ($startDate, $endDate) {
                $q->where('status', 'completed')->whereBetween('created_at', [$startDate, $endDate]);
            })
            ->select('product_id', 'product_name', DB::raw('SUM(quantity) as total_qty'), DB::raw('SUM(subtotal) as total_revenue'))
            ->groupBy('product_id', 'product_name')
            ->orderByDesc('total_qty')
            ->limit(10)
            ->get();

        // ── Order Status Distribution ──
        $statusDistribution = Order::whereBetween('created_at', [$startDate, $endDate])
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status');

        // ── Recent Completed Orders ──
        $recentCompleted = Order::with('user')
            ->where('status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->latest()
            ->take(10)
            ->get();

        // ── Top Customers ──
        $topCustomers = Order::where('status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select('user_id', DB::raw('COUNT(*) as total_orders'), DB::raw('SUM(total) as total_spent'))
            ->groupBy('user_id')
            ->orderByDesc('total_spent')
            ->limit(5)
            ->with('user')
            ->get();

        return view('admin.reports.index', compact(
            'period', 'startDate', 'endDate',
            'totalRevenue', 'totalOrders', 'completedOrders', 'cancelledOrders',
            'totalItemsSold', 'avgOrderValue',
            'revenueChart', 'bestSellingProducts', 'statusDistribution',
            'recentCompleted', 'topCustomers'
        ));
    }

    private function getStartDate(string $period, Request $request)
    {
        return match ($period) {
            'today'  => now()->startOfDay(),
            'week'   => now()->startOfWeek(),
            'month'  => now()->startOfMonth(),
            'year'   => now()->startOfYear(),
            'custom' => $request->filled('start_date')
                ? \Carbon\Carbon::parse($request->input('start_date'))->startOfDay()
                : now()->startOfMonth(),
            default  => now()->startOfMonth(),
        };
    }

    private function getEndDate(string $period, Request $request)
    {
        return match ($period) {
            'today'  => now()->endOfDay(),
            'week'   => now()->endOfWeek(),
            'month'  => now()->endOfMonth(),
            'year'   => now()->endOfYear(),
            'custom' => $request->filled('end_date')
                ? \Carbon\Carbon::parse($request->input('end_date'))->endOfDay()
                : now()->endOfMonth(),
            default  => now()->endOfMonth(),
        };
    }
}
