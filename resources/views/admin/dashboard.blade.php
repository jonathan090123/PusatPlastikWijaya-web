@extends('layouts.admin')

@section('title', 'Dashboard - Admin')

@section('content')
<div class="page-header">
    <h1><i class="fas fa-tachometer-alt"></i> Dashboard</h1>
    <a href="{{ route('admin.orders.index') }}" class="btn btn-warning btn-sm" id="newOrderBtn" style="display:none;">
        <i class="fas fa-bell"></i> <span id="newOrderCount"></span> Pesanan Baru
    </a>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon blue"><i class="fas fa-shopping-bag"></i></div>
        <div class="stat-info">
            <h3>{{ number_format($totalOrders) }}</h3>
            <p>Total Pesanan</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green"><i class="fas fa-money-bill-wave"></i></div>
        <div class="stat-info">
            <h3>Rp {{ number_format($totalRevenue, 0, ',', '.') }}</h3>
            <p>Pendapatan Hari Ini</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon yellow"><i class="fas fa-box"></i></div>
        <div class="stat-info">
            <h3>{{ number_format($totalProducts) }}</h3>
            <p>Total Produk</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon red"><i class="fas fa-users"></i></div>
        <div class="stat-info">
            <h3>{{ number_format($totalCustomers) }}</h3>
            <p>Total Pelanggan</p>
        </div>
    </div>
</div>

<div style="display:grid; grid-template-columns:1fr 1fr; gap:1.5rem;">
    {{-- Recent Orders --}}
    <div class="card">
        <div class="card-header">
            <span><i class="fas fa-clock"></i> Pesanan Terbaru</span>
            <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-primary btn-sm">Lihat Semua</a>
        </div>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Invoice</th>
                        <th>Pelanggan</th>
                        <th>Total</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentOrders as $order)
                        <tr class="dashboard-row" style="cursor:pointer;" onclick="window.location='{{ route('admin.orders.show', $order) }}'">
                            <td><strong>{{ $order->invoice_number }}</strong></td>
                            <td>{{ $order->user->name ?? '-' }}</td>
                            <td>Rp {{ number_format($order->total, 0, ',', '.') }}</td>
                            <td><span class="badge-status badge-{{ $order->status }}">{{ $order->status_label }}</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">
                                <div class="empty-state" style="padding:1.5rem;">
                                    <i class="fas fa-inbox"></i>
                                    <h3>Belum ada pesanan</h3>
                                    <p>Pesanan baru akan muncul di sini</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Low Stock Alert --}}
    <div class="card">
        <div class="card-header">
            <span><i class="fas fa-exclamation-triangle" style="color:var(--warning);"></i> Stok Menipis</span>
            <a href="{{ route('admin.products.index') }}" class="btn btn-outline-primary btn-sm">Lihat Semua</a>
        </div>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Produk</th>
                        <th>Kategori</th>
                        <th>Stok</th>
                        <th>Alert</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($lowStockProducts as $product)
                        <tr class="dashboard-row" style="cursor:pointer;" onclick="window.location='{{ route('admin.products.edit', $product) }}'">
                            <td><strong>{{ $product->name }}</strong></td>
                            <td>{{ $product->category->name ?? '-' }}</td>
                            <td>
                                <span class="badge-status badge-cancelled">
                                    <i class="fas fa-exclamation-triangle"></i> {{ $product->stock }}
                                </span>
                            </td>
                            <td>{{ $product->stock_alert }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">
                                <div class="empty-state" style="padding:1.5rem;">
                                    <i class="fas fa-check-circle" style="color:var(--success);"></i>
                                    <h3>Semua stok aman</h3>
                                    <p>Tidak ada produk dengan stok menipis</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.dashboard-row { transition: background 0.15s ease; }
.dashboard-row:hover { background: #eff6ff !important; }
@media (max-width: 768px) {
    .admin-content > div[style*="grid-template-columns"] {
        grid-template-columns: 1fr !important;
    }
}
</style>
@endpush

@push('scripts')
<script>
(function () {
    const KEY      = 'admin_seen_orders';
    const seen     = new Set(JSON.parse(sessionStorage.getItem(KEY) || '[]'));
    const today    = @json($todayOrderIds);
    const newCount = {{ $newOrdersCount }};

    // Find today's order IDs that admin hasn't seen yet in this session
    const unseen = today.filter(function (id) { return !seen.has(String(id)); });

    if (unseen.length > 0 && newCount > 0) {
        const btn   = document.getElementById('newOrderBtn');
        const label = document.getElementById('newOrderCount');
        label.textContent = newCount;
        btn.style.display = 'inline-flex';
    }

    // Mark all today's orders as seen after this render
    today.forEach(function (id) { seen.add(String(id)); });
    sessionStorage.setItem(KEY, JSON.stringify([...seen]));
}());
</script>
@endpush
