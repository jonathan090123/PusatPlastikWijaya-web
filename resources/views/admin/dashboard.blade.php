@extends('layouts.admin')

@section('title', 'Dashboard - Admin')

@section('content')
<div id="dashboard-page">
<div class="page-header">
    <h1><i class="fas fa-tachometer-alt"></i> Dashboard</h1>
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
            <p>Total Pendapatan</p>
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

<div class="dashboard-tables">
    {{-- Recent Orders --}}
    <div class="card">
        <div class="card-header">
            <span><i class="fas fa-clock"></i> Pesanan Terbaru</span>
            <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-primary btn-sm">Lihat Semua</a>
        </div>
        <div class="table-responsive" id="recent-orders-scroll">
            <table>
                <thead>
                    <tr>
                        <th>Invoice</th>
                        <th>Pelanggan</th>
                        <th>Total</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody id="recent-orders-tbody">
                    @forelse($recentOrders as $order)
                        <tr class="dashboard-row" style="cursor:pointer;"
                            onclick="window.location='{{ route('admin.orders.show', $order) }}'">
                            <td>
                                <strong>{{ $order->invoice_number }}</strong>
                                @if(isset($newOrderIds[$order->id]))
                                    <span class="order-new-badge">BARU</span>
                                @elseif($order->status === 'processing')
                                    <i class="fas fa-circle-exclamation order-ongoing-icon" title="Pesanan sedang berlangsung"></i>
                                @endif
                            </td>
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
            <div id="recent-orders-sentinel" style="height:1px;"></div>
            <div id="recent-orders-loader" style="display:none; text-align:center; padding:0.6rem 0; color:#94a3b8; font-size:0.82rem;">
                <i class="fas fa-spinner fa-spin" style="margin-right:0.3rem;"></i> Memuat data...
            </div>
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
</div>
@endsection

@push('styles')
<style>
/* Dashboard: fit entire page in viewport — tables scroll internally */
#dashboard-page {
    display: flex;
    flex-direction: column;
    height: calc(100vh - 60px - 3rem); /* 60px topbar, 3rem = 1.5rem padding top + bottom */
    overflow: hidden;
}
#dashboard-page > .page-header {
    flex-shrink: 0;
    margin-bottom: 0.75rem;
}
#dashboard-page > .stats-grid {
    flex-shrink: 0;
    margin-bottom: 0.75rem;
    gap: 0.6rem;
}
#dashboard-page > .stats-grid .stat-card {
    padding: 0.75rem 1rem;
}
#dashboard-page > .stats-grid .stat-icon {
    width: 38px;
    height: 38px;
    font-size: 0.95rem;
    flex-shrink: 0;
}
.dashboard-tables {
    flex: 1;
    min-height: 0;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.25rem;
    overflow: hidden;
}
.dashboard-tables .card {
    display: flex;
    flex-direction: column;
    min-height: 0;
    overflow: hidden;
}
.dashboard-tables .card .table-responsive {
    flex: 1;
    overflow-y: auto;
    min-height: 0;
}
.dashboard-row { transition: background 0.15s ease; }
.dashboard-row:hover { background: #eff6ff !important; }
.order-new-badge {
    display: inline-block;
    font-size: 0.65rem;
    font-weight: 800;
    letter-spacing: 0.05em;
    background: #ef4444;
    color: #fff;
    border-radius: 4px;
    padding: 0.1rem 0.4rem;
    margin-left: 0.4rem;
    vertical-align: middle;
    animation: newPulse 1.5s ease infinite;
}
@keyframes newPulse {
    0%, 100% { opacity: 1; }
    50%       { opacity: 0.55; }
}
.order-ongoing-icon {
    color: #ef4444;
    font-size: 1.1rem;
    margin-left: 0.4rem;
    vertical-align: middle;
}
@media (max-width: 768px) {
    #dashboard-page { height: auto; overflow: visible; }
    .dashboard-tables { grid-template-columns: 1fr !important; }
}
</style>
@endpush

@push('scripts')
<script>
(function () {
    const tbody     = document.getElementById('recent-orders-tbody');
    const sentinel  = document.getElementById('recent-orders-sentinel');
    const loader    = document.getElementById('recent-orders-loader');
    const scrollEl  = document.getElementById('recent-orders-scroll');

    let page    = 2;
    let loading = false;
    let hasMore = {{ $recentOrdersHasMore ? 'true' : 'false' }};

    function renderRow(order) {
        const tr = document.createElement('tr');
        tr.className = 'dashboard-row';
        tr.style.cursor = 'pointer';
        tr.addEventListener('click', () => { window.location = order.url; });

        let badge = '';
        if (order.status === 'processing') {
            badge = '<i class="fas fa-circle-exclamation order-ongoing-icon" title="Pesanan sedang berlangsung"></i>';
        }

        tr.innerHTML =
            '<td><strong>' + order.invoice_number + '</strong>' + badge + '</td>' +
            '<td>' + order.user_name + '</td>' +
            '<td>Rp ' + order.total + '</td>' +
            '<td><span class="badge-status badge-' + order.status + '">' + order.status_label + '</span></td>';

        return tr;
    }

    async function loadMore() {
        if (loading || !hasMore) return;
        loading = true;
        loader.style.display = 'block';

        try {
            const res = await fetch('{{ route('admin.dashboard.recent-orders') }}?page=' + page, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            });
            if (!res.ok) throw new Error('HTTP ' + res.status);
            const data = await res.json();

            data.orders.forEach(order => tbody.appendChild(renderRow(order)));
            hasMore = data.has_more;
            page    = data.next_page;
        } catch (e) {
            hasMore = false;
        } finally {
            loading = false;
            loader.style.display = 'none';
            if (!hasMore) observer.disconnect();
        }
    }

    const observer = new IntersectionObserver(
        (entries) => { if (entries[0].isIntersecting) loadMore(); },
        { root: scrollEl, threshold: 0 }
    );

    if (hasMore) observer.observe(sentinel);
})();
</script>
@endpush

