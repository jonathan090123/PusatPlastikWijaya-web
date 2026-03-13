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
                <select name="status">
                    <option value="">Semua Status</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Menunggu</option>
                    <option value="waiting_payment" {{ request('status') === 'waiting_payment' ? 'selected' : '' }}>Menunggu Pembayaran</option>
                    <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Sudah Dibayar</option>
                    <option value="processing" {{ request('status') === 'processing' ? 'selected' : '' }}>Diproses</option>
                    <option value="shipped" {{ request('status') === 'shipped' ? 'selected' : '' }}>Dikirim</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Selesai</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i> Filter</button>
            @if(request()->hasAny(['search', 'status']))
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
                        $isDone = in_array($order->status, ['completed', 'cancelled']);
                        $badgeClass = match($order->status) {
                            'pending'           => 'badge-pending',
                            'waiting_payment'   => 'badge-pending',
                            'paid'              => 'badge-paid',
                            'processing'        => 'badge-processing',
                            'ready_for_pickup'  => 'badge-ready-pickup',
                            'shipped'           => 'badge-shipped',
                            'completed'         => 'badge-completed',
                            'cancelled'         => 'badge-cancelled',
                            default             => '',
                        };
                    @endphp
                    <tr class="order-row {{ $isDone ? 'order-row--done' : '' }}" data-order-id="{{ $order->id }}" onclick="window.location='{{ route('admin.orders.show', $order) }}'">
                        <td>{{ $orders->firstItem() + $index }}</td>
                        <td>
                            <strong>{{ $order->invoice_number }}</strong>
                            <span class="order-new-badge" style="display:none;">BARU</span>
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
</style>
@endpush

@push('scripts')
<script>
(function () {
    const KEY = 'admin_seen_orders';
    const seen = new Set(JSON.parse(sessionStorage.getItem(KEY) || '[]'));
    const currentIds = [];

    document.querySelectorAll('tr[data-order-id]').forEach(function (row) {
        const id = row.dataset.orderId;
        currentIds.push(id);
        if (!seen.has(id)) {
            const badge = row.querySelector('.order-new-badge');
            if (badge) badge.style.display = 'inline-block';
        }
    });

    // Merge current page IDs into seen set and persist
    currentIds.forEach(function (id) { seen.add(id); });
    sessionStorage.setItem(KEY, JSON.stringify([...seen]));
}());
</script>
@endpush
