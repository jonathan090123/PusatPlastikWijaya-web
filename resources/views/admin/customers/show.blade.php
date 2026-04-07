@extends('layouts.admin')

@section('title', 'Detail Pelanggan - Admin')

@section('content')
<div class="page-header">
    <h1><i class="fas fa-user"></i> Detail Pelanggan</h1>
    <div style="display:flex; gap:.5rem; align-items:center;">
        <form method="POST" action="{{ route('admin.customers.toggleActive', $customer) }}">
            @csrf
            @method('PATCH')
            <button type="submit"
                class="btn btn-sm {{ $customer->is_active ? 'btn-outline-danger' : 'btn-success' }}"
                onclick="return confirm('{{ $customer->is_active ? 'Nonaktifkan' : 'Aktifkan' }} akun {{ addslashes($customer->name) }}?')">
                <i class="fas {{ $customer->is_active ? 'fa-ban' : 'fa-circle-check' }}"></i>
                {{ $customer->is_active ? 'Nonaktifkan Akun' : 'Aktifkan Akun' }}
            </button>
        </form>
        <a href="{{ route('admin.customers.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>
</div>

{{-- Customer Info --}}
<div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
    <div class="stat-card">
        <div class="stat-icon blue"><i class="fas fa-user"></i></div>
        <div class="stat-info">
            <h3 style="font-size:1rem;">{{ $customer->name }}</h3>
            <p>{{ $customer->email }}</p>
        </div> 
    </div>
    <div class="stat-card">
        <div class="stat-icon green"><i class="fas fa-phone"></i></div>
        <div class="stat-info">
            <h3 style="font-size:1rem;">{{ $customer->phone ?? '-' }}</h3>
            <p>No. Handphone</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon yellow"><i class="fas fa-star"></i></div>
        <div class="stat-info">
            <h3>{{ number_format($customer->points) }}</h3>
            <p>Poin Tersedia</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon red"><i class="fas fa-money-bill-wave"></i></div>
        <div class="stat-info">
            <h3 style="font-size:1rem;">Rp {{ number_format($totalSpent, 0, ',', '.') }}</h3>
            <p>Total Belanja</p>
        </div>
    </div>
</div>

@if($customer->address)
<div class="card" style="margin-bottom:1.5rem;">
    <div class="card-header"><span><i class="fas fa-map-marker-alt"></i> Alamat</span></div>
    <div class="card-body">
        <p>{{ $customer->address }}</p>
    </div>
</div>
@endif

@if($customer->customer_type === 'business')
<div class="card" style="margin-bottom:1.5rem;">
    <div class="card-header"><span><i class="fas fa-building"></i> Info Usaha / Perusahaan</span></div>
    <div class="card-body">
        <p style="margin:0; color:var(--gray-700);">
            {{ $customer->business_name ?? '<em style="color:var(--gray-400);">Nama usaha tidak diisi</em>' }}
        </p>
    </div>
</div>
@endif

{{-- Recent Orders --}}
<div class="card" style="margin-bottom:1.5rem;">
    <div class="card-header"><span><i class="fas fa-shopping-bag"></i> Pesanan Terakhir</span></div>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Invoice</th>
                    <th>Tanggal</th>
                    <th>Item</th>
                    <th>Total</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($customer->orders as $order)
                    <tr>
                        <td>
                            <a href="{{ route('admin.orders.show', $order) }}" style="font-weight:600; color:var(--primary); text-decoration:none;"
                               title="Lihat detail pesanan">
                                {{ $order->invoice_number }}
                            </a>
                        </td>
                        <td>{{ $order->created_at->format('d M Y H:i') }}</td>
                        <td>{{ $order->items->count() }} item</td>
                        <td>Rp {{ number_format($order->total, 0, ',', '.') }}</td>
                        <td><span class="badge-status badge-{{ $order->status }}">{{ $order->status_label }}</span></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">
                            <div class="empty-state" style="padding:1.5rem;">
                                <p>Belum ada pesanan</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Point History --}}
<div class="card">
    <div class="card-header"><span><i class="fas fa-star"></i> Riwayat Poin</span></div>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Tipe</th>
                    <th>Jumlah</th>
                    <th>Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($customer->pointHistories as $point)
                    <tr>
                        <td>{{ $point->created_at->format('d M Y H:i') }}</td>
                        <td>
                            @if($point->type === 'earn')
                                <span class="badge-status badge-paid"><i class="fas fa-plus"></i> Dapat</span>
                            @else
                                <span class="badge-status badge-cancelled"><i class="fas fa-minus"></i> Pakai</span>
                            @endif
                        </td>
                        <td>
                            <strong style="color: {{ $point->type === 'earn' ? 'var(--success)' : 'var(--danger)' }}">
                                {{ $point->type === 'earn' ? '+' : '-' }}{{ number_format($point->amount) }}
                            </strong>
                        </td>
                        <td>{{ $point->description }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4">
                            <div class="empty-state" style="padding:1.5rem;">
                                <p>Belum ada riwayat poin</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
