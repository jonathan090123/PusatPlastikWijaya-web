@extends('layouts.admin')

@section('title', 'Manajemen Pesanan - Admin')

@section('content')
<div class="page-header">
    <h1><i class="fas fa-shopping-bag"></i> Manajemen Pesanan</h1>
</div>

{{-- Filters --}}
<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-body" style="padding: 0.75rem 1.25rem;">
        <form action="{{ route('admin.orders.index') }}" method="GET" style="display:flex; gap:0.75rem; align-items:center; flex-wrap:wrap;">
            <div class="form-group" style="flex:1; min-width:200px; margin:0;">
                <input type="text" name="search" placeholder="Cari invoice, nama..." value="{{ request('search') }}">
            </div>
            <div class="form-group" style="min-width:180px; margin:0;">
                <select name="status" onchange="this.form.submit()">
                    <option value="">Semua Status</option>
                    <option value="waiting_payment" {{ request('status') === 'waiting_payment' ? 'selected' : '' }}>Menunggu Pembayaran</option>
                    <option value="processing" {{ request('status') === 'processing' ? 'selected' : '' }}>Diproses</option>
                    <option value="ready_for_pickup" {{ request('status') === 'ready_for_pickup' ? 'selected' : '' }}>Siap Diambil</option>
                    <option value="shipped" {{ request('status') === 'shipped' ? 'selected' : '' }}>Dikirim</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Selesai</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                    <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Kadaluarsa</option>
                </select>
            </div>
            <div class="form-group" style="margin:0; display:flex; align-items:center; gap:0.4rem;">
                <input type="date" name="date_from" value="{{ request('date_from') }}" title="Dari tanggal" style="min-width:140px;">
                <span style="color:#94a3b8; font-size:0.85rem;">—</span>
                <input type="date" name="date_to" value="{{ request('date_to') }}" title="Sampai tanggal" style="min-width:140px;">
            </div>
            <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i> Cari</button>
            @if(request()->hasAny(['search', 'status', 'date_from', 'date_to']))
                <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary btn-sm"><i class="fas fa-times"></i> Reset</a>
            @endif
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Invoice</th>
                    <th>Pelanggan</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Tanggal</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $index => $order)
                    @php
                        $isDone = in_array($order->status, ['completed', 'cancelled', 'expired']);
                        $badgeClass = match($order->status) {
                            'pending'           => 'badge-pending',
                            'waiting_payment'   => 'badge-waiting_payment',
                            'paid'              => 'badge-paid',
                            'processing'        => 'badge-processing',
                            'ready_for_pickup'  => 'badge-ready-pickup',
                            'shipped'           => 'badge-shipped',
                            'completed'         => 'badge-completed',
                            'cancelled'         => 'badge-cancelled',
                            'expired'           => 'badge-expired',
                            default             => '',
                        };
                    @endphp
                    @php $isNew = isset($newOrderIds[$order->id]); @endphp
                    @php $isOngoing = $order->status === 'processing'; @endphp
                    <tr class="order-row {{ $isDone ? 'order-row--done' : '' }} {{ $isNew ? 'order-row--new' : '' }}" data-order-id="{{ $order->id }}"
                        onclick="window.location='{{ route('admin.orders.show', $order) }}'">
                        <td>{{ $orders->firstItem() + $index }}</td>
                        <td>
                            <strong>{{ $order->invoice_number }}</strong>
                            @if($isNew)
                                <span class="order-new-badge">BARU</span>
                            @elseif($isOngoing)
                                <i class="fas fa-circle-exclamation order-ongoing-icon" title="Pesanan sedang berlangsung"></i>
                            @endif
                        </td>
                        <td>{{ $order->user->name ?? '-' }}</td>
                        <td>Rp {{ number_format($order->total, 0, ',', '.') }}</td>
                        <td><span class="badge-status {{ $badgeClass }}">{{ $order->status_label }}</span></td>
                        <td>{{ $order->created_at->format('d M Y H:i') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">
                            <div class="empty-state">
                                <i class="fas fa-shopping-bag"></i>
                                <h3>Belum ada pesanan</h3>
                                <p>Pesanan dari pelanggan akan muncul di sini</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($orders->hasPages())
        <div class="card-footer">
            {{ $orders->links() }}
        </div>
    @endif
</div>
@endsection

@push('styles')
<style>
.order-row {
    cursor: pointer;
    transition: background 0.15s ease, opacity 0.15s ease;
}
.order-row:hover {
    background: #eff6ff !important;
}
.order-row--done {
    opacity: 0.55;
}
.order-row--done:hover {
    opacity: 0.8;
    background: var(--gray-50) !important;
}
.order-row--new {
    background: #fff5f5 !important;
    border-left: 3px solid #ef4444;
}
.order-row--new:hover {
    background: #fee2e2 !important;
}
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
    50%       { opacity: 0.6; }
}
.order-ongoing-icon {
    color: #ef4444;
    font-size: 1.1rem;
    margin-left: 0.4rem;
    vertical-align: middle;
}
</style>
@endpush


