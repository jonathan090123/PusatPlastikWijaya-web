<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Exports\XlsxWriter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminReportsController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->input('period', 'month');
        $startDate = $this->getStartDate($period, $request);
        $endDate   = $this->getEndDate($period, $request);

        // Hanya order completed
        $completedQuery = Order::where('status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate]);

        // Semua order non-cancelled
        $allOrdersQuery = Order::whereBetween('created_at', [$startDate, $endDate]);

        // Summary stats
        $totalRevenue      = (clone $completedQuery)->sum('total');
        $totalOrders       = (clone $allOrdersQuery)->count();
        $completedOrders   = (clone $completedQuery)->count();
        $cancelledOrders   = (clone $allOrdersQuery)->where('status', 'cancelled')->count();
        $totalItemsSold    = OrderItem::whereHas('order', function ($q) use ($startDate, $endDate) {
            $q->where('status', 'completed')->whereBetween('created_at', [$startDate, $endDate]);
        })->sum('quantity');
        $avgOrderValue     = $completedOrders > 0 ? $totalRevenue / $completedOrders : 0;

        // Revenue chart
        $revenueChart = Order::where('status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(total) as revenue'), DB::raw('COUNT(*) as orders'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Produk terlaris (top 10)
        $bestSellingProducts = OrderItem::whereHas('order', function ($q) use ($startDate, $endDate) {
                $q->where('status', 'completed')->whereBetween('created_at', [$startDate, $endDate]);
            })
            ->select('product_id', 'product_name', DB::raw('SUM(quantity) as total_qty'), DB::raw('SUM(subtotal) as total_revenue'))
            ->groupBy('product_id', 'product_name')
            ->orderByDesc('total_qty')
            ->limit(10)
            ->get();


        // Order selesai terbaru
        $recentCompleted = Order::with('user')
            ->where('status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->latest()
            ->take(10)
            ->get();

        // Top pelanggan
        $topCustomers = Order::where('status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select('user_id', DB::raw('COUNT(*) as total_orders'), DB::raw('SUM(total) as total_spent'))
            ->groupBy('user_id')
            ->orderByDesc('total_spent')
            ->limit(5)
            ->with('user')
            ->get();

        // Distribusi status order (tetap dikirim untuk kompatibilitas cache blade di hosting)
        $statusDistribution = Order::whereBetween('created_at', [$startDate, $endDate])
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status');

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

    public function exportExcel(Request $request)
    {
        $period    = $request->input('period', 'month');
        $startDate = $this->getStartDate($period, $request);
        $endDate   = $this->getEndDate($period, $request);

        // ── Sheet 1: Ringkasan ────────────────────────────────────────────
        $completedQuery = Order::where('status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate]);
        $allOrdersQuery = Order::whereBetween('created_at', [$startDate, $endDate]);

        $totalRevenue    = (clone $completedQuery)->sum('total');
        $totalOrders     = (clone $allOrdersQuery)->count();
        $completedOrders = (clone $completedQuery)->count();
        $cancelledOrders = (clone $allOrdersQuery)->where('status', 'cancelled')->count();
        $totalItemsSold  = OrderItem::whereHas('order', fn ($q) =>
            $q->where('status', 'completed')->whereBetween('created_at', [$startDate, $endDate])
        )->sum('quantity');
        $avgOrderValue   = $completedOrders > 0 ? $totalRevenue / $completedOrders : 0;

        $periodLabel = match ($period) {
            'today'  => 'Hari Ini',
            'week'   => 'Minggu Ini',
            'month'  => 'Bulan Ini',
            'year'   => 'Tahun Ini',
            'custom' => $startDate->format('d M Y') . ' — ' . $endDate->format('d M Y'),
            default  => 'Bulan Ini',
        };

        $summaryRows = [
            ['Metrik',                      'Nilai'],
            ['Periode',                     $periodLabel],
            ['Tanggal Mulai',               $startDate->format('d M Y')],
            ['Tanggal Selesai',             $endDate->format('d M Y')],
            ['', ''],
            ['Total Pendapatan (Rp)',        'Rp ' . number_format($totalRevenue, 0, ',', '.')],
            ['Total Pesanan',               $totalOrders],
            ['Pesanan Selesai',             $completedOrders],
            ['Pesanan Dibatalkan',          $cancelledOrders],
            ['Item Terjual',               $totalItemsSold],
            ['Rata-rata per Pesanan (Rp)', 'Rp ' . number_format($avgOrderValue, 0, ',', '.')],
        ];

        // ── Sheet 2: Produk Terlaris ──────────────────────────────────────
        $bestSelling = OrderItem::whereHas('order', fn ($q) =>
                $q->where('status', 'completed')->whereBetween('created_at', [$startDate, $endDate])
            )
            ->select('product_name',
                DB::raw('SUM(quantity) as total_qty'),
                DB::raw('SUM(subtotal) as total_revenue')
            )
            ->groupBy('product_name')
            ->orderByDesc('total_qty')
            ->get();

        $bestSellingRows = [['#', 'Nama Produk', 'Qty Terjual', 'Pendapatan (Rp)']];
        foreach ($bestSelling as $i => $item) {
            $bestSellingRows[] = [
                $i + 1,
                $item->product_name,
                (int) $item->total_qty,
                'Rp ' . number_format($item->total_revenue, 0, ',', '.'),
            ];
        }

        // ── Sheet 3: Pelanggan Teratas ────────────────────────────────────
        $topCustomers = Order::where('status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select('user_id',
                DB::raw('COUNT(*) as total_orders'),
                DB::raw('SUM(total) as total_spent')
            )
            ->groupBy('user_id')
            ->orderByDesc('total_spent')
            ->with('user')
            ->get();

        $customersRows = [['#', 'Nama Pelanggan', 'Email', 'Jumlah Pesanan', 'Total Belanja (Rp)']];
        foreach ($topCustomers as $i => $row) {
            $customersRows[] = [
                $i + 1,
                $row->user->name  ?? '-',
                $row->user->email ?? '-',
                $row->total_orders,
                'Rp ' . number_format($row->total_spent, 0, ',', '.'),
            ];
        }

        // ── Sheet 4: Daftar Pesanan ───────────────────────────────────────
        $orders = Order::with('user', 'payment')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderByDesc('created_at')
            ->get();

        $ordersRows = [['No. Invoice', 'Pelanggan', 'Email', 'Status', 'Total (Rp)', 'Tanggal', 'Metode Bayar']];
        foreach ($orders as $order) {
            $statusLabel = match ($order->status) {
                'pending'    => 'Menunggu Pembayaran',
                'processing' => 'Diproses',
                'shipped'    => 'Dikirim',
                'completed'  => 'Selesai',
                'cancelled'  => 'Dibatalkan',
                'expired'    => 'Kadaluarsa',
                default      => ucfirst($order->status),
            };
            $ordersRows[] = [
                $order->invoice_number,
                $order->user->name  ?? '-',
                $order->user->email ?? '-',
                $statusLabel,
                'Rp ' . number_format($order->total, 0, ',', '.'),
                \Carbon\Carbon::parse($order->created_at)->format('d M Y H:i'),
                $order->payment->payment_type ?? '-',
            ];
        }

        // ── Filename ──────────────────────────────────────────────────────
        $fileLabel = match ($period) {
            'today'  => 'Hari-Ini',
            'week'   => 'Minggu-Ini',
            'month'  => 'Bulan-Ini',
            'year'   => 'Tahun-Ini',
            'custom' => $startDate->format('d-M-Y') . '_sd_' . $endDate->format('d-M-Y'),
            default  => 'Bulan-Ini',
        };
        $filename = 'Laporan-Penjualan_' . $fileLabel . '_' . now()->format('YmdHis') . '.xlsx';

        return (new XlsxWriter())
            ->addSheet('Ringkasan',         $summaryRows,     [32, 30],             '1e40af')
            ->addSheet('Produk Terlaris',   $bestSellingRows, [6, 40, 16, 22],     '065f46')
            ->addSheet('Pelanggan Teratas', $customersRows,   [6, 28, 34, 18, 22], '7c3aed')
            ->addSheet('Daftar Pesanan',    $ordersRows,      [22, 26, 32, 22, 18, 20, 18], '1e3a8a')
            ->download($filename);
    }
}
