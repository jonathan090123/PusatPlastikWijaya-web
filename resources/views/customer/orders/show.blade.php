@extends('layouts.customer')

@section('title', 'Detail Pesanan #' . $order->invoice_number . ' - Pusat Plastik Wijaya')

@section('content')
<div style="padding: 0.5rem;">
    <div class="page-header">
        <h1><i class="fas fa-file-invoice"></i> Detail Pesanan</h1>
        <a href="{{ route('orders.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>

    <div style="display:grid; grid-template-columns:1fr 350px; gap:1.5rem; align-items:start;">
        {{-- Left --}}
        <div>
            {{-- Order Status --}}
            <div class="card" style="margin-bottom:1.5rem;">
                <div class="card-body">
                    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:0.5rem;">
                        <div>
                            <h3 style="font-size:1.1rem; font-weight:700; color:var(--gray-800); margin:0;">{{ $order->invoice_number }}</h3>
                            <span style="font-size:0.85rem; color:var(--gray-400);">{{ $order->created_at->format('d M Y, H:i') }}</span>
                        </div>
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
                        <div style="display:flex; align-items:center; gap:0.5rem;">
                            <span style="font-size:0.8rem; color:var(--gray-700); font-weight:700;">Status:</span>
                            <span class="badge-status {{ $badgeClass }}" style="font-size:0.85rem; padding:0.4rem 0.8rem;">{{ $order->status_label }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Shipping Info --}}
            <div class="card" style="margin-bottom:1.5rem;">
                <div class="card-body">
                    <h3 style="font-size:1rem; font-weight:700; color:var(--gray-800); margin-bottom:0.75rem;">
                        <i class="fas fa-truck" style="color:var(--primary);"></i> Informasi Pengiriman
                    </h3>
                    <div style="font-size:0.9rem; color:var(--gray-600); line-height:1.6;">
                        <strong>{{ $order->recipient_name }}</strong><br>
                        {{ $order->recipient_phone }}<br>
                        {{ $order->shipping_address }}<br>
                        <span style="color:var(--primary); font-weight:600;">{{ $order->shipping_name }}</span>
                        @if($order->shippingCost && $order->shippingCost->estimation)
                            <span style="color:var(--gray-400);">({{ $order->shippingCost->estimation }})</span>
                        @endif
                    </div>
                    @if($order->notes)
                        <div style="margin-top:0.75rem; padding:0.75rem; background:var(--gray-50); border-radius:var(--radius-sm); font-size:0.85rem;">
                            <strong>Catatan:</strong> {{ $order->notes }}
                        </div>
                    @endif
                </div>
            </div>

        </div>

        {{-- Right: Ringkasan Pesanan (items + price breakdown) --}}
        <div>
            <div class="card">
                <div class="card-body">
                    <h3 style="font-size:1rem; font-weight:700; color:var(--gray-800); margin-bottom:0.75rem;">
                        <i class="fas fa-receipt" style="color:var(--primary);"></i> Ringkasan Pesanan
                    </h3>

                    {{-- Items --}}
                    <div style="display:flex; flex-direction:column; gap:0.6rem; margin-bottom:1rem;">
                        @foreach($order->items as $item)
                            <div style="display:flex; align-items:center; gap:0.75rem; padding:0.6rem; background:var(--gray-50); border-radius:var(--radius-sm);">
                                <div style="width:44px; height:44px; border-radius:var(--radius-sm); overflow:hidden; flex-shrink:0; background:var(--white); border:1px solid var(--gray-200);">
                                    @if($item->product && $item->product->image)
                                        <img src="{{ asset('storage/' . $item->product->image) }}" alt="{{ $item->product_name }}" style="width:100%; height:100%; object-fit:contain; padding:2px;">
                                    @else
                                        <div style="width:100%; height:100%; display:flex; align-items:center; justify-content:center; color:var(--gray-400); font-size:0.8rem;"><i class="fas fa-image"></i></div>
                                    @endif
                                </div>
                                <div style="flex:1; min-width:0;">
                                    <div style="font-weight:600; font-size:0.85rem; color:var(--gray-800); white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $item->product_name }}</div>
                                    <div style="font-size:0.78rem; color:var(--gray-500);">{{ $item->quantity }} x Rp {{ number_format($item->product_price, 0, ',', '.') }}</div>
                                </div>
                                <div style="font-weight:700; font-size:0.85rem; color:var(--gray-800); white-space:nowrap;">
                                    Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Price Breakdown --}}
                    <div style="border-top:1px solid var(--gray-200); padding-top:0.75rem; font-size:0.9rem;">
                        <div style="display:flex; justify-content:space-between; padding:0.35rem 0; color:var(--gray-600);">
                            <span>Subtotal</span>
                            <span>Rp {{ number_format($order->subtotal, 0, ',', '.') }}</span>
                        </div>
                        @if($order->discount_amount > 0)
                            <div style="display:flex; justify-content:space-between; padding:0.35rem 0; color:var(--success);">
                                <span>Diskon Voucher</span>
                                <span>-Rp {{ number_format($order->discount_amount, 0, ',', '.') }}</span>
                            </div>
                        @endif
                        @if($order->points_discount > 0)
                            <div style="display:flex; justify-content:space-between; padding:0.35rem 0; color:var(--success);">
                                <span>Diskon Poin ({{ $order->points_used }} poin)</span>
                                <span>-Rp {{ number_format($order->points_discount, 0, ',', '.') }}</span>
                            </div>
                        @endif
                        <div style="display:flex; justify-content:space-between; padding:0.35rem 0; color:var(--gray-600);">
                            <span>Ongkos Kirim</span>
                            <span>Rp {{ number_format($order->shipping_fee, 0, ',', '.') }}</span>
                        </div>
                        <div style="display:flex; justify-content:space-between; padding:0.6rem 0 0; margin-top:0.4rem; border-top:2px solid var(--gray-100); font-weight:700; font-size:1.05rem; color:var(--gray-900);">
                            <span>Total</span>
                            <span>Rp {{ number_format($order->total, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
@media (max-width: 768px) {
    .admin-content > div > div[style*="grid-template-columns"] {
        grid-template-columns: 1fr !important;
    }
}
</style>
@endpush
