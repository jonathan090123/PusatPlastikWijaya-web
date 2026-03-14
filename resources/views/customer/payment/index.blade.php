@extends('layouts.customer')

@section('title', 'Pembayaran - ' . $order->invoice_number)

@section('content')
<div style="padding: 0.5rem;">
    <div class="page-header">
        <h1><i class="fas fa-credit-card"></i> Pembayaran</h1>
        <a href="{{ route('orders.show', $order) }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>

    <div style="display:grid; grid-template-columns:1fr 380px; gap:1.5rem; align-items:start;">
        {{-- Left: Payment Info --}}
        <div>
            <div class="card" style="margin-bottom:1.5rem;">
                <div class="card-body" style="text-align:center; padding:2rem;">
                    <div style="width:80px; height:80px; background:var(--primary-light, #dbeafe); border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 1.25rem;">
                        <i class="fas fa-shield-alt" style="font-size:2rem; color:var(--primary);"></i>
                    </div>
                    <h2 style="font-size:1.25rem; font-weight:700; color:var(--gray-800); margin-bottom:0.5rem;">Selesaikan Pembayaran</h2>
                    <p style="color:var(--gray-500); font-size:0.9rem; margin-bottom:1.5rem;">
                        Klik tombol di bawah untuk memilih metode pembayaran dan menyelesaikan transaksi.
                    </p>

                    <div style="background:var(--gray-50); border-radius:var(--radius-md); padding:1.25rem; margin-bottom:1.5rem;">
                        <div style="font-size:0.85rem; color:var(--gray-500); margin-bottom:0.25rem;">Total Pembayaran</div>
                        <div style="font-size:1.75rem; font-weight:800; color:var(--gray-900);">
                            Rp {{ number_format($order->total, 0, ',', '.') }}
                        </div>
                        <div style="font-size:0.8rem; color:var(--gray-400); margin-top:0.25rem;">
                            {{ $order->invoice_number }}
                        </div>
                    </div>

                    <button id="pay-button" class="btn btn-primary" style="width:100%; padding:0.85rem 1.5rem; font-size:1rem; font-weight:700;">
                        <i class="fas fa-lock"></i> Bayar Sekarang
                    </button>

                    <p style="font-size:0.75rem; color:var(--gray-400); margin-top:1rem;">
                        <i class="fas fa-shield-alt"></i> Pembayaran diproses secara aman oleh Midtrans
                    </p>
                </div>
            </div>

            {{-- Payment Methods Info --}}
            <div class="card">
                <div class="card-body">
                    <h3 style="font-size:0.95rem; font-weight:700; color:var(--gray-800); margin-bottom:0.75rem;">
                        <i class="fas fa-wallet" style="color:var(--primary);"></i> Metode Pembayaran Tersedia
                    </h3>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:0.5rem; font-size:0.82rem; color:var(--gray-600);">
                        <div style="padding:0.5rem; background:var(--gray-50); border-radius:var(--radius-sm);">
                            <i class="fas fa-mobile-alt" style="color:var(--primary);"></i> GoPay
                        </div>
                        <div style="padding:0.5rem; background:var(--gray-50); border-radius:var(--radius-sm);">
                            <i class="fas fa-qrcode" style="color:var(--primary);"></i> QRIS
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right: Order Summary --}}
        <div>
            <div class="card">
                <div class="card-body">
                    <h3 style="font-size:1rem; font-weight:700; color:var(--gray-800); margin-bottom:0.75rem;">
                        <i class="fas fa-receipt" style="color:var(--primary);"></i> Ringkasan Pesanan
                    </h3>

                    <div style="display:flex; flex-direction:column; gap:0.5rem; margin-bottom:1rem;">
                        @foreach($order->items as $item)
                            <div style="display:flex; align-items:center; gap:0.6rem; padding:0.5rem; background:var(--gray-50); border-radius:var(--radius-sm);">
                                <div style="width:40px; height:40px; border-radius:var(--radius-sm); overflow:hidden; flex-shrink:0; background:var(--white); border:1px solid var(--gray-200);">
                                    @if($item->product && $item->product->image)
                                        <img src="{{ asset('storage/' . $item->product->image) }}" alt="{{ $item->product_name }}" style="width:100%; height:100%; object-fit:contain; padding:2px;">
                                    @else
                                        <div style="width:100%; height:100%; display:flex; align-items:center; justify-content:center; color:var(--gray-400); font-size:0.7rem;"><i class="fas fa-image"></i></div>
                                    @endif
                                </div>
                                <div style="flex:1; min-width:0;">
                                    <div style="font-weight:600; font-size:0.82rem; color:var(--gray-800); white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $item->product_name }}</div>
                                    <div style="font-size:0.75rem; color:var(--gray-500);">{{ $item->quantity }} x Rp {{ number_format($item->product_price, 0, ',', '.') }}</div>
                                </div>
                                <div style="font-weight:700; font-size:0.82rem; color:var(--gray-800); white-space:nowrap;">
                                    Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div style="border-top:1px solid var(--gray-200); padding-top:0.75rem; font-size:0.85rem;">
                        <div style="display:flex; justify-content:space-between; padding:0.3rem 0; color:var(--gray-600);">
                            <span>Subtotal</span>
                            <span>Rp {{ number_format($order->subtotal, 0, ',', '.') }}</span>
                        </div>
                        @if($order->discount_amount > 0)
                            <div style="display:flex; justify-content:space-between; padding:0.3rem 0; color:var(--success);">
                                <span>Diskon</span>
                                <span>-Rp {{ number_format($order->discount_amount, 0, ',', '.') }}</span>
                            </div>
                        @endif
                        <div style="display:flex; justify-content:space-between; padding:0.3rem 0; color:var(--gray-600);">
                            <span>Ongkos Kirim</span>
                            <span>Rp {{ number_format($order->shipping_fee, 0, ',', '.') }}</span>
                        </div>
                        <div style="display:flex; justify-content:space-between; padding:0.5rem 0 0; margin-top:0.3rem; border-top:2px solid var(--gray-100); font-weight:700; font-size:1rem; color:var(--gray-900);">
                            <span>Total</span>
                            <span>Rp {{ number_format($order->total, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Shipping info --}}
            <div class="card" style="margin-top:1rem;">
                <div class="card-body">
                    <h3 style="font-size:0.9rem; font-weight:700; color:var(--gray-800); margin-bottom:0.5rem;">
                        <i class="fas fa-truck" style="color:var(--primary);"></i> Pengiriman
                    </h3>
                    <div style="font-size:0.82rem; color:var(--gray-600); line-height:1.6;">
                        <strong>{{ $order->recipient_name }}</strong><br>
                        {{ $order->recipient_phone }}<br>
                        {{ $order->shipping_address }}<br>
                        <span style="color:var(--primary); font-weight:600;">{{ $order->shipping_name }}</span>
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
    div[style*="grid-template-columns:1fr 380px"] {
        grid-template-columns: 1fr !important;
    }
}
</style>
@endpush

@push('scripts')
<script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('services.midtrans.client_key') }}"></script>
<script>
document.getElementById('pay-button').addEventListener('click', function () {
    this.disabled = true;
    this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';

    window.snap.pay('{{ $snapToken }}', {
        onSuccess: function(result) {
            window.location.href = '{{ route("payment.finish", $order) }}';
        },
        onPending: function(result) {
            window.location.href = '{{ route("payment.finish", $order) }}';
        },
        onError: function(result) {
            alert('Pembayaran gagal. Silakan coba lagi.');
            location.reload();
        },
        onClose: function() {
            document.getElementById('pay-button').disabled = false;
            document.getElementById('pay-button').innerHTML = '<i class="fas fa-lock"></i> Bayar Sekarang';
        }
    });
});
</script>
@endpush
