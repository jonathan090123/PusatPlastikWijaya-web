@extends('layouts.customer')

@section('title', 'Pesanan Saya - Pusat Plastik Wijaya')

@section('content')
<div style="padding: 0.5rem;">
    <div class="page-header">
        <h1><i class="fas fa-clipboard-list"></i> Pesanan Saya</h1>
    </div>

    @if($orders->count() > 0)
        <div style="display:flex; flex-direction:column; gap:1rem;">
            @foreach($orders as $order)
                @php
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
                <a href="{{ route('orders.show', $order) }}" class="order-card-link" data-order-id="{{ $order->id }}">
                    <div class="card order-card">
                        <div class="card-body" style="padding:1.25rem;">
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.75rem; flex-wrap:wrap; gap:0.5rem;">
                                <div>
                                    <strong style="font-size:1rem; color:var(--gray-800);">{{ $order->invoice_number }}</strong>
                                    <span style="font-size:0.8rem; color:var(--gray-400); margin-left:0.5rem;">{{ $order->created_at->format('d M Y, H:i') }}</span>
                                </div>
                                <div style="display:flex; align-items:center; gap:0.5rem;">
                                    @if(is_null($order->status_read_at))
                                        <span class="order-unread-dot" title="Status pesanan diperbarui"></span>
                                    @endif
                                    <span style="font-size:0.8rem; color:var(--gray-700); font-weight:700;">Status:</span>
                                    <span class="badge-status {{ $badgeClass }}">{{ $order->status_label }}</span>
                                </div>
                            </div>

                            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:0.75rem;">
                                <div style="font-size:0.9rem; color:var(--gray-600);">
                                    <i class="fas fa-truck" style="color:var(--gray-400);"></i> {{ $order->shipping_name }}
                                    &middot;
                                    <i class="fas fa-user" style="color:var(--gray-400);"></i> {{ $order->recipient_name }}
                                </div>
                                <div style="display:flex; align-items:center; gap:0.75rem;">
                                    <span style="font-weight:700; font-size:1.05rem; color:var(--gray-900);">
                                        Rp {{ number_format($order->total, 0, ',', '.') }}
                                    </span>
                                    <span class="order-card-arrow"><i class="fas fa-chevron-right"></i></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>

        @if($orders->hasPages())
            <div style="margin-top:1.5rem; display:flex; justify-content:center;">
                {{ $orders->links() }}
            </div>
        @endif
    @else
        <div class="empty-state" style="padding:3rem;">
            <i class="fas fa-clipboard-list" style="font-size:3rem;"></i>
            <h3>Belum Ada Pesanan</h3>
            <p>Pesanan Anda akan muncul di sini setelah checkout</p>
            <a href="{{ route('products.index') }}" class="btn btn-primary">
                <i class="fas fa-shopping-bag"></i> Mulai Belanja
            </a>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
(function() {
    const storageKey = 'customer_seen_orders';
    const seen = new Set(JSON.parse(sessionStorage.getItem(storageKey) || '[]'));
    const newlySeen = [];

    document.querySelectorAll('.order-card-link[data-order-id]').forEach(card => {
        const id = String(card.dataset.orderId);
        const dot = card.querySelector('.order-unread-dot');
        if (!dot) return;
        if (seen.has(id)) {
            dot.remove();
        } else {
            newlySeen.push(id);
        }
    });

    // Mark newly-seen as seen so they hide on next refresh
    newlySeen.forEach(id => seen.add(id));
    sessionStorage.setItem(storageKey, JSON.stringify([...seen]));

    // Hide sidebar badge if no dots remain visible
    if (!document.querySelector('.order-unread-dot')) {
        const badge = document.getElementById('customer-orders-nav-badge');
        if (badge) badge.style.display = 'none';
    }
})();
</script>
@endpush

@push('styles')
<style>
.order-card-link {
    display: block;
    text-decoration: none;
    color: inherit;
}
.order-card {
    transition: var(--transition);
    cursor: pointer;
}
.order-card-link:hover .order-card {
    border-color: var(--primary);
    box-shadow: var(--shadow-lg);
    transform: translateY(-1px);
}
.order-card-arrow {
    color: var(--gray-300);
    font-size: 0.85rem;
    transition: var(--transition);
}
.order-card-link:hover .order-card-arrow {
    color: var(--primary);
    transform: translateX(3px);
}
</style>
@endpush
