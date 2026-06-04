@extends('layouts.admin')

@section('title', 'Laporan Penjualan - Admin')

@section('content')
<div class="page-header">
    <h1><i class="fas fa-chart-bar"></i> Laporan Penjualan</h1>
    <a href="{{ route('admin.reports.exportExcel') }}"
       id="btnExportExcel"
       style="display:inline-flex;align-items:center;gap:0.45rem;background:linear-gradient(135deg,#059669,#047857);color:#fff;border:none;padding:0.5rem 1.15rem;border-radius:8px;font-weight:600;font-size:0.88rem;text-decoration:none;transition:opacity .18s;box-shadow:0 2px 8px rgba(5,150,105,.28);cursor:pointer;"
       onmouseover="this.style.opacity='.82'" onmouseout="this.style.opacity='1'">
        <i class="fas fa-file-excel"></i> Export Excel
    </a>
</div>

{{-- ═══════════ Period Filter ═══════════ --}}
<div class="card" style="margin-bottom:1.5rem;">
    <div class="card-body" style="padding:1rem 1.25rem;">
        <form method="GET" action="{{ route('admin.reports.index') }}" id="reportFilterForm" style="display:flex; align-items:center; gap:0.75rem; flex-wrap:wrap;">
            <span style="font-weight:700; font-size:0.88rem; color:var(--gray-700);"><i class="fas fa-calendar-alt" style="color:var(--primary);"></i> Periode:</span>

            <div class="report-period-tabs">
                @foreach(['today' => 'Hari Ini', 'week' => 'Minggu Ini', 'month' => 'Bulan Ini', 'year' => 'Tahun Ini', 'custom' => 'Kustom'] as $key => $label)
                    <button type="button" class="period-tab {{ $period === $key ? 'active' : '' }}" data-period="{{ $key }}" onclick="selectPeriod('{{ $key }}')">{{ $label }}</button>
                @endforeach
            </div>

            <input type="hidden" name="period" id="periodInput" value="{{ $period }}">

            <div id="customDateRange" style="display:{{ $period === 'custom' ? 'flex' : 'none' }}; align-items:center; gap:0.5rem;">
                <input type="date" name="start_date" value="{{ request('start_date', $startDate->format('Y-m-d')) }}" class="report-date-input">
                <span style="color:var(--gray-400);">—</span>
                <input type="date" name="end_date" value="{{ request('end_date', $endDate->format('Y-m-d')) }}" class="report-date-input">
                <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i> Terapkan</button>
            </div>
        </form>
    </div>
</div>

{{-- ═══════════ Stat Cards ═══════════ --}}
<div class="stats-grid" style="grid-template-columns: repeat(3, 1fr); margin-bottom:1.5rem;">
    <div class="stat-card">
        <div class="stat-icon green"><i class="fas fa-money-bill-wave"></i></div>
        <div class="stat-info">
            <h3>Rp {{ number_format($totalRevenue, 0, ',', '.') }}</h3>
            <p>Total Pendapatan</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon blue"><i class="fas fa-file-invoice"></i></div>
        <div class="stat-info">
            <h3>{{ number_format($totalOrders) }}</h3>
            <p>Total Pesanan</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon yellow"><i class="fas fa-cubes"></i></div>
        <div class="stat-info">
            <h3>{{ number_format($totalItemsSold) }}</h3>
            <p>Item Terjual</p>
        </div>
    </div>
</div>

<div class="stats-grid" style="grid-template-columns: repeat(3, 1fr); margin-bottom:1.5rem;">
    <div class="stat-card">
        <div class="stat-icon" style="background:#d1fae5; color:#065f46;"><i class="fas fa-check-circle"></i></div>
        <div class="stat-info">
            <h3>{{ number_format($completedOrders) }}</h3>
            <p>Pesanan Selesai</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon red"><i class="fas fa-times-circle"></i></div>
        <div class="stat-info">
            <h3>{{ number_format($cancelledOrders) }}</h3>
            <p>Pesanan Dibatalkan</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#ede9fe; color:#6d28d9;"><i class="fas fa-receipt"></i></div>
        <div class="stat-info">
            <h3>Rp {{ number_format($avgOrderValue, 0, ',', '.') }}</h3>
            <p>Rata-rata per Pesanan</p>
        </div>
    </div>
</div>

{{-- ═══════════ Revenue Chart ═══════════ --}}
<div class="card" style="margin-bottom:1.5rem;">
    <div class="card-header">
        <span><i class="fas fa-chart-line" style="color:var(--primary);"></i> Grafik Pendapatan</span>
        <span style="font-size:0.78rem; color:var(--gray-400);">{{ $startDate->format('d M Y') }} — {{ $endDate->format('d M Y') }}</span>
    </div>
    <div class="card-body" style="padding:1.25rem;">
        @if($revenueChart->count() > 0)
            <div class="chart-container">
                <canvas id="revenueChart" height="280"></canvas>
            </div>
        @else
            <div class="empty-state" style="padding:2rem;">
                <i class="fas fa-chart-line" style="color:var(--gray-300);"></i>
                <h3>Belum ada data</h3>
                <p>Tidak ada transaksi selesai pada periode ini</p>
            </div>
        @endif
    </div>
</div>

{{-- ═══════════ Best Selling Products (Full Width) ═══════════ --}}
<div class="card" style="margin-bottom:1.5rem;">
    <div class="card-header">
        <span><i class="fas fa-trophy" style="color:var(--warning);"></i> Produk Terlaris</span>
    </div>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th style="width:40px;">#</th>
                    <th>Produk</th>
                    <th style="text-align:right;">Qty</th>
                    <th style="text-align:right;">Pendapatan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($bestSellingProducts as $i => $item)
                    <tr>
                        <td>
                            @if($i < 3)
                                <span class="rank-badge rank-{{ $i + 1 }}">{{ $i + 1 }}</span>
                            @else
                                <span style="color:var(--gray-400); font-weight:600;">{{ $i + 1 }}</span>
                            @endif
                        </td>
                        <td><strong style="color:var(--gray-800);">{{ $item->product_name }}</strong></td>
                        <td style="text-align:right; font-weight:600;">{{ number_format($item->total_qty) }}</td>
                        <td style="text-align:right; font-weight:600; color:var(--success);">Rp {{ number_format($item->total_revenue, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4">
                            <div class="empty-state" style="padding:1.5rem;">
                                <i class="fas fa-box-open"></i>
                                <h3>Belum ada data</h3>
                                <p>Tidak ada produk terjual di periode ini</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- ═══════════ Two Column: Top Customers + Recent Completed ═══════════ --}}
<div style="display:grid; grid-template-columns:1fr 1fr; gap:1.5rem; margin-bottom:1.5rem;">

    {{-- Top Customers --}}
    <div class="card">
        <div class="card-header">
            <span><i class="fas fa-crown" style="color:var(--warning);"></i> Pelanggan Teratas</span>
        </div>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th style="width:40px;">#</th>
                        <th>Pelanggan</th>
                        <th style="text-align:right;">Pesanan</th>
                        <th style="text-align:right;">Total Belanja</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($topCustomers as $i => $row)
                        <tr>
                            <td>
                                @if($i < 3)
                                    <span class="rank-badge rank-{{ $i + 1 }}">{{ $i + 1 }}</span>
                                @else
                                    <span style="color:var(--gray-400); font-weight:600;">{{ $i + 1 }}</span>
                                @endif
                            </td>
                            <td><strong style="color:var(--gray-800);">{{ $row->user->name ?? '-' }}</strong></td>
                            <td style="text-align:right; font-weight:600;">{{ $row->total_orders }}</td>
                            <td style="text-align:right; font-weight:600; color:var(--success);">Rp {{ number_format($row->total_spent, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">
                                <div class="empty-state" style="padding:1.5rem;">
                                    <i class="fas fa-users"></i>
                                    <h3>Belum ada data</h3>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Recent Completed Orders --}}
    <div class="card">
        <div class="card-header">
            <span><i class="fas fa-check-double" style="color:var(--success);"></i> Pesanan Selesai Terbaru</span>
            <a href="{{ route('admin.orders.index', ['status' => 'completed']) }}" class="btn btn-outline-primary btn-sm">Lihat Semua</a>
        </div>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Invoice</th>
                        <th>Pelanggan</th>
                        <th style="text-align:right;">Total</th>
                        <th>Tanggal</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentCompleted as $order)
                        <tr class="dashboard-row" style="cursor:pointer;" onclick="window.location='{{ route('admin.orders.show', $order) }}'">
                            <td><strong>{{ $order->invoice_number }}</strong></td>
                            <td>{{ $order->user->name ?? '-' }}</td>
                            <td style="text-align:right; font-weight:600;">Rp {{ number_format($order->total, 0, ',', '.') }}</td>
                            <td style="font-size:0.82rem; color:var(--gray-500);">{{ $order->created_at->format('d M Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">
                                <div class="empty-state" style="padding:1.5rem;">
                                    <i class="fas fa-inbox"></i>
                                    <h3>Belum ada pesanan selesai</h3>
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
/* Period tabs */
.report-period-tabs {
    display: flex;
    gap: 0.25rem;
    background: var(--gray-100);
    border-radius: var(--radius);
    padding: 0.2rem;
}
.period-tab {
    padding: 0.4rem 0.85rem;
    border: none;
    background: transparent;
    border-radius: var(--radius-sm);
    font-size: 0.82rem;
    font-weight: 600;
    color: var(--gray-500);
    cursor: pointer;
    transition: var(--transition);
}
.period-tab:hover { color: var(--gray-700); }
.period-tab.active {
    background: var(--white);
    color: var(--primary);
    box-shadow: var(--shadow-sm);
}
.report-date-input {
    padding: 0.35rem 0.6rem;
    border: 1px solid var(--gray-200);
    border-radius: var(--radius-sm);
    font-size: 0.82rem;
    color: var(--gray-700);
}

/* Rank badges */
.rank-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 24px; height: 24px;
    border-radius: 50%;
    font-size: 0.72rem;
    font-weight: 800;
}
.rank-1 { background: #fef3c7; color: #92400e; }
.rank-2 { background: #e2e8f0; color: #475569; }
.rank-3 { background: #fed7aa; color: #9a3412; }


/* Chart container */
.chart-container {
    position: relative;
    width: 100%;
}

/* Responsive */
@media (max-width: 900px) {
    .stats-grid { grid-template-columns: 1fr 1fr !important; }
    div[style*="grid-template-columns:1fr 1fr"] { grid-template-columns: 1fr !important; }
}
@media (max-width: 576px) {
    .stats-grid { grid-template-columns: 1fr !important; }
    .report-period-tabs { flex-wrap: wrap; }
    #customDateRange { flex-wrap: wrap; }
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
function buildExportUrl() {
    var baseUrl = "{{ route('admin.reports.exportExcel') }}";
    var period  = document.getElementById('periodInput').value;
    var params  = new URLSearchParams({ period: period });
    if (period === 'custom') {
        var s = document.querySelector('[name="start_date"]');
        var e = document.querySelector('[name="end_date"]');
        if (s && s.value) params.append('start_date', s.value);
        if (e && e.value) params.append('end_date',   e.value);
    }
    document.getElementById('btnExportExcel').href = baseUrl + '?' + params.toString();
}

function selectPeriod(period) {
    document.querySelectorAll('.period-tab').forEach(t => t.classList.remove('active'));
    document.querySelector('[data-period="' + period + '"]').classList.add('active');
    document.getElementById('periodInput').value = period;

    var custom = document.getElementById('customDateRange');
    if (period === 'custom') {
        custom.style.display = 'flex';
        buildExportUrl();
    } else {
        custom.style.display = 'none';
        buildExportUrl();
        document.getElementById('reportFilterForm').submit();
    }
}

document.addEventListener('DOMContentLoaded', function () {
    buildExportUrl();
    var sd = document.querySelector('[name="start_date"]');
    var ed = document.querySelector('[name="end_date"]');
    if (sd) sd.addEventListener('change', buildExportUrl);
    if (ed) ed.addEventListener('change', buildExportUrl);
});

// Revenue Chart
@if($revenueChart->count() > 0)
(function() {
    var ctx = document.getElementById('revenueChart').getContext('2d');

    var labels = @json($revenueChart->pluck('date')->map(fn($d) => \Carbon\Carbon::parse($d)->format('d M')));
    var revenueData = @json($revenueChart->pluck('revenue'));
    var ordersData  = @json($revenueChart->pluck('orders'));

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Pendapatan (Rp)',
                    data: revenueData,
                    backgroundColor: 'rgba(37, 99, 235, 0.15)',
                    borderColor: '#2563eb',
                    borderWidth: 2,
                    borderRadius: 6,
                    yAxisID: 'y',
                    order: 2
                },
                {
                    label: 'Jumlah Pesanan',
                    data: ordersData,
                    type: 'line',
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    borderWidth: 2,
                    pointRadius: 4,
                    pointBackgroundColor: '#10b981',
                    tension: 0.3,
                    fill: true,
                    yAxisID: 'y1',
                    order: 1
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: {
                    position: 'top',
                    labels: { usePointStyle: true, padding: 16, font: { size: 12 } }
                },
                tooltip: {
                    callbacks: {
                        label: function(ctx) {
                            if (ctx.dataset.yAxisID === 'y') {
                                return 'Pendapatan: Rp ' + Number(ctx.raw).toLocaleString('id-ID');
                            }
                            return 'Pesanan: ' + ctx.raw;
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { font: { size: 11 } }
                },
                y: {
                    position: 'left',
                    beginAtZero: true,
                    grid: { color: 'rgba(0,0,0,0.04)' },
                    ticks: {
                        font: { size: 11 },
                        callback: function(v) {
                            if (v >= 1000000) return 'Rp ' + (v / 1000000).toFixed(1) + 'jt';
                            if (v >= 1000) return 'Rp ' + (v / 1000).toFixed(0) + 'rb';
                            return 'Rp ' + v;
                        }
                    }
                },
                y1: {
                    position: 'right',
                    beginAtZero: true,
                    grid: { drawOnChartArea: false },
                    ticks: {
                        font: { size: 11 },
                        stepSize: 1
                    }
                }
            }
        }
    });
})();
@endif
</script>
@endpush
