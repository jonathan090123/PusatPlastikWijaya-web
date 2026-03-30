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
                <div class="order-card-link" data-order-id="{{ $order->id }}"
                     data-href="{{ route('orders.show', $order) }}"
                     onclick="if(!event.target.closest('.reorder-btn-wrap')){window.location.href=this.dataset.href}"
                     style="cursor:pointer; text-decoration:none; display:block;">
                    <div class="card order-card {{ isset($unreadOrderIds[$order->id]) ? 'order-card--unread' : '' }}">
                        <div class="card-body" style="padding:1.25rem;">
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.75rem; flex-wrap:wrap; gap:0.5rem;">
                                <div style="display:flex; align-items:center; gap:0.5rem; flex-wrap:wrap;">
                                    <strong style="font-size:1rem; color:var(--gray-800);">{{ $order->invoice_number }}</strong>
                                    <span style="font-size:0.8rem; color:var(--gray-500); font-weight:600;">{{ $order->created_at->format('d M Y, H:i') }}</span>
                                    @if(isset($unreadOrderIds[$order->id]))
                                        <span class="order-unread-label">
                                            <span class="order-unread-dot" style="width:6px;height:6px;"></span>
                                            Ada pembaruan
                                        </span>
                                    @endif
                                </div>
                                <div style="display:flex; align-items:center; gap:0.5rem;">
                                    <span style="font-size:0.8rem; color:var(--gray-700); font-weight:700;">Status:</span>
                                    <span class="badge-status {{ $badgeClass }}">{{ $order->status_label }}</span>
                                </div>
                            </div>

                            {{-- Item produk --}}
                            @php $firstItems = $order->items->take(3); $remaining = $order->items->count() - 3; @endphp
                            <div style="display:flex; align-items:center; gap:0.6rem; margin-bottom:0.75rem; flex-wrap:wrap;">
                                @foreach($firstItems as $item)
                                    <div style="display:flex; align-items:center; gap:0.4rem; background:var(--gray-50); border:1px solid var(--gray-200); border-radius:8px; padding:0.3rem 0.6rem;">
                                        @if($item->product && $item->product->image)
                                            <img src="{{ asset('storage/' . $item->product->image) }}" alt="{{ $item->product->name }}"
                                                style="width:32px; height:32px; object-fit:cover; border-radius:4px; flex-shrink:0;">
                                        @else
                                            <div style="width:32px; height:32px; background:var(--gray-200); border-radius:4px; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                                                <i class="fas fa-box" style="font-size:0.7rem; color:var(--gray-400);"></i>
                                            </div>
                                        @endif
                                        <div style="max-width:110px;">
                                            <div style="font-size:0.78rem; color:var(--gray-700); font-weight:600; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $item->product->name ?? 'Produk dihapus' }}</div>
                                            <div style="font-size:0.72rem; color:var(--gray-400);">x{{ $item->quantity }}</div>
                                        </div>
                                    </div>
                                @endforeach
                                @if($remaining > 0)
                                    <span style="font-size:0.78rem; color:var(--gray-500); font-style:italic;">+{{ $remaining }} produk lainnya</span>
                                @endif
                            </div>

                            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:0.75rem;">
                                <div style="font-size:0.9rem; color:var(--gray-600);">
                                    <i class="fas fa-truck" style="color:var(--gray-400);"></i> {{ $order->shipping_name }}
                                    &middot;
                                    <i class="fas fa-user" style="color:var(--gray-400);"></i> {{ $order->recipient_name }}
                                </div>
                                <div style="display:flex; align-items:center; gap:0.6rem; flex-wrap:wrap;">
                                    <span style="font-weight:700; font-size:1.05rem; color:var(--gray-900);">
                                        Rp {{ number_format($order->total, 0, ',', '.') }}
                                    </span>
                                    {{-- Points earned badge --}}
                                    @if($order->status === 'completed')
                                        @php $earnedPts = $order->pointHistories->firstWhere('type', 'earned'); @endphp
                                        @if($earnedPts)
                                            <span style="background:#fef9c3; color:#854d0e; border:1px solid #fde047; font-size:0.72rem; font-weight:700; padding:0.15rem 0.5rem; border-radius:999px; white-space:nowrap;">
                                                <i class="fas fa-star" style="color:#ca8a04; font-size:0.65rem;"></i>
                                                +{{ number_format($earnedPts->amount, 0, ',', '.') }} poin
                                            </span>
                                        @endif
                                    @endif
                                    {{-- Beli Lagi --}}
                                    <div class="reorder-btn-wrap">
                                        <button type="button" class="btn-reorder btn-reorder-ajax"
                                            data-action="{{ route('orders.reorder', $order) }}"
                                            data-csrf="{{ csrf_token() }}"
                                            title="Tambahkan produk pesanan ini ke keranjang">
                                            <i class="fas fa-redo-alt"></i> Beli Lagi
                                        </button>
                                    </div>
                                    <span class="order-card-arrow"><i class="fas fa-chevron-right"></i></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
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
.btn-reorder {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    padding: 0.3rem 0.75rem;
    font-size: 0.78rem;
    font-weight: 600;
    border-radius: var(--radius-sm);
    border: 1.5px solid var(--primary);
    background: var(--white);
    color: var(--primary);
    cursor: pointer;
    transition: background 0.15s, color 0.15s;
    white-space: nowrap;
}
.btn-reorder:hover {
    background: var(--primary);
    color: #fff;
}

@media (max-width: 768px) {
    /* Order item chips: limit max-width for small screens */
    .order-card .card-body div[style*="max-width:110px"] {
        max-width: 90px !important;
    }
    .btn-reorder { padding: 0.4rem 0.65rem; font-size: 0.75rem; }
}
#reorder-modal {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.45);
    z-index: 99999;
    align-items: center;
    justify-content: center;
    padding: 1rem;
}
#reorder-modal.show { display: flex; }
#reorder-modal-box {
    background: #fff;
    border-radius: 14px;
    padding: 1.75rem 1.5rem 1.5rem;
    max-width: 380px;
    width: 100%;
    box-shadow: 0 16px 40px rgba(0,0,0,0.15);
    animation: reorderPop 0.22s cubic-bezier(0.34,1.56,0.64,1);
}
@keyframes reorderPop { from{opacity:0;transform:scale(0.9)} to{opacity:1;transform:scale(1)} }
#reorder-modal-list {
    list-style: none;
    padding: 0;
    margin: 0.75rem 0 1.25rem;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}
#reorder-modal-list li {
    display: flex;
    align-items: flex-start;
    gap: 0.5rem;
    background: #fef2f2;
    border: 1px solid #fecaca;
    border-radius: 8px;
    padding: 0.5rem 0.75rem;
    font-size: 0.83rem;
    color: #7f1d1d;
    line-height: 1.5;
}
#reorder-modal-list li i { color: #ef4444; margin-top: 2px; flex-shrink: 0; }
@media (max-width: 480px) {
    /* Stack invoice + date vertically */
    .order-card .card-body div[style*="display:flex; justify-content:space-between"] {
        flex-direction: column !important;
        align-items: flex-start !important;
        gap: 0.35rem !important;
    }
    /* Smaller product chip images */
    .order-card img[style*="width:32px"] {
        width: 26px !important;
        height: 26px !important;
    }
    /* Tighten card padding */
    .order-card .card-body { padding: 0.9rem !important; }
    .order-card .card-body div[style*="font-size:1.05rem"] { font-size: 0.95rem !important; }
}
</style>
@endpush

@push('scripts')
{{-- Reorder Stock Modal --}}
<div id="reorder-modal">
    <div id="reorder-modal-box">
        <div style="text-align:center; margin-bottom:0.75rem;">
            <div style="width:48px;height:48px;border-radius:50%;background:#fef2f2;display:flex;align-items:center;justify-content:center;margin:0 auto 0.75rem;">
                <i class="fas fa-exclamation-triangle" style="color:#ef4444;font-size:1.15rem;"></i>
            </div>
            <h3 style="font-size:1rem;font-weight:800;color:#111827;margin:0 0 0.25rem;">Stok Tidak Mencukupi</h3>
            <p style="font-size:0.83rem;color:#6b7280;margin:0;">Produk berikut tidak dapat ditambahkan karena stok tidak mencukupi:</p>
        </div>
        <ul id="reorder-modal-list"></ul>
        <button id="reorder-modal-ok" style="width:100%;padding:0.65rem;border-radius:8px;border:none;background:var(--primary);color:#fff;font-weight:700;font-size:0.9rem;cursor:pointer;">
            Mengerti
        </button>
    </div>
</div>

<script>
(function () {
    const modal   = document.getElementById('reorder-modal');
    const list    = document.getElementById('reorder-modal-list');
    const okBtn   = document.getElementById('reorder-modal-ok');

    function closeModal() { modal.classList.remove('show'); }
    okBtn.addEventListener('click', closeModal);
    modal.addEventListener('click', function(e) { if (e.target === modal) closeModal(); });
    document.addEventListener('keydown', function(e) { if (e.key === 'Escape') closeModal(); });

    document.querySelectorAll('.btn-reorder-ajax').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const action = btn.dataset.action;
            const csrf   = btn.dataset.csrf;
            const orig   = btn.innerHTML;

            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

            fetch(action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
            })
            .then(res => res.json())
            .then(function(data) {
                btn.innerHTML = orig;
                btn.disabled  = false;

                if (!data.success && !data.insufficient?.length) {
                    alert(data.message || 'Gagal menambahkan produk ke keranjang.');
                    return;
                }

                if (data.insufficient && data.insufficient.length > 0) {
                    // Build list items
                    list.innerHTML = '';
                    data.insufficient.forEach(function(item) {
                        const li = document.createElement('li');
                        const avail = item.available > 0
                            ? `Tersisa <strong>${item.available} ${item.unit}</strong>`
                            : 'Stok habis';
                        li.innerHTML = `<i class="fas fa-box-open"></i><span><strong>${item.name}</strong> &mdash; ${avail}</span>`;
                        list.appendChild(li);
                    });
                    modal.classList.add('show');
                } else if (data.redirect) {
                    window.location.href = data.redirect;
                }
            })
            .catch(function() {
                btn.innerHTML = orig;
                btn.disabled  = false;
                alert('Terjadi kesalahan. Silakan coba lagi.');
            });
        });
    });
})();
</script>
@endpush
