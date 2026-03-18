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
        {{-- Left: Payment Method Selector --}}
        <div>
            <div class="card" style="margin-bottom:1.5rem;">
                <div class="card-body" style="padding:1.75rem;">
                    <h2 style="font-size:1.1rem; font-weight:700; color:var(--gray-800); margin-bottom:0.25rem;">
                        <i class="fas fa-wallet" style="color:var(--primary);"></i> Pilih Metode Pembayaran
                    </h2>
                    <p style="color:var(--gray-500); font-size:0.85rem; margin-bottom:1.5rem; margin-top:0.25rem;">
                        Pilih salah satu metode di bawah, lalu klik <strong>Lanjutkan Pembayaran</strong>.
                    </p>

                    <div class="payment-methods" style="display:flex; flex-direction:column; gap:0.75rem; margin-bottom:1.5rem;">

                        {{-- BCA Virtual Account --}}
                        <label class="method-card" data-method="bca_va">
                            <input type="radio" name="payment_method" value="bca_va" style="display:none;">
                            <div class="method-icon" style="background:#005faf; color:#fff;">
                                <i class="fas fa-university"></i>
                            </div>
                            <div class="method-info">
                                <div class="method-name">BCA Virtual Account</div>
                                <div class="method-desc">Transfer via ATM, mBCA, atau internet banking BCA</div>
                            </div>
                            <div class="method-check"><i class="fas fa-check-circle"></i></div>
                        </label>

                        {{-- GoPay QRIS --}}
                        <label class="method-card" data-method="gopay">
                            <input type="radio" name="payment_method" value="gopay" style="display:none;">
                            <div class="method-icon" style="background:#00aed6; color:#fff;">
                                <i class="fas fa-mobile-alt"></i>
                            </div>
                            <div class="method-info">
                                <div class="method-name">GoPay QRIS</div>
                                <div class="method-desc">Bayar langsung dari aplikasi Gojek / GoPay</div>
                            </div>
                            <div class="method-check"><i class="fas fa-check-circle"></i></div>
                        </label>

                    </div>

                    {{-- Total + Pay Button --}}
                    <div style="background:var(--gray-50); border-radius:var(--radius-md); padding:1.1rem 1.25rem; margin-bottom:1.25rem; display:flex; align-items:center; justify-content:space-between;">
                        <div>
                            <div style="font-size:0.78rem; color:var(--gray-500);">Total Pembayaran</div>
                            <div style="font-size:1.4rem; font-weight:800; color:var(--gray-900);">
                                Rp {{ number_format($order->total, 0, ',', '.') }}
                            </div>
                        </div>
                        <div style="font-size:0.75rem; color:var(--gray-400); text-align:right;">
                            {{ $order->invoice_number }}
                        </div>
                    </div>

                    <button id="pay-button" class="btn btn-primary" disabled
                        style="width:100%; padding:0.85rem 1.5rem; font-size:1rem; font-weight:700; opacity:0.55; cursor:not-allowed; transition:opacity .2s;">
                        <i class="fas fa-lock"></i> Lanjutkan Pembayaran
                    </button>

                    <p style="font-size:0.75rem; color:var(--gray-400); margin-top:0.85rem; text-align:center;">
                        <i class="fas fa-shield-alt"></i> Pembayaran diproses secara aman oleh Midtrans
                        &nbsp;·&nbsp;
                        <a href="https://simulator.sandbox.midtrans.com" target="_blank" rel="noopener noreferrer"
                           style="color:var(--primary); text-decoration:underline;">Payment Simulator</a>
                    </p>
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
/* ── Method Card ── */
.method-card {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem 1.1rem;
    border: 2px solid var(--gray-200);
    border-radius: var(--radius-md);
    cursor: pointer;
    transition: border-color .2s, background .2s, box-shadow .15s;
    background: #fff;
    user-select: none;
}
.method-card:hover {
    border-color: var(--primary);
    background: var(--primary-light, #dbeafe);
}
.method-card.selected {
    border-color: var(--primary);
    background: var(--primary-light, #dbeafe);
    box-shadow: 0 0 0 3px rgba(59,130,246,.15);
}
.method-icon {
    width: 46px;
    height: 46px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.3rem;
    flex-shrink: 0;
}
.method-info { flex: 1; }
.method-name { font-weight: 700; font-size: 0.95rem; color: var(--gray-800); }
.method-desc { font-size: 0.78rem; color: var(--gray-500); margin-top: 0.15rem; }
.method-check {
    font-size: 1.25rem;
    color: var(--gray-300);
    transition: color .2s;
}
.method-card.selected .method-check { color: var(--primary); }

#pay-button:not([disabled]) {
    opacity: 1 !important;
    cursor: pointer !important;
}

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
(function () {
    const cards    = document.querySelectorAll('.method-card');
    const payBtn   = document.getElementById('pay-button');
    const tokenUrl = '{{ route("payment.token", $order) }}';
    const finishUrl= '{{ route("payment.finish", $order) }}';
    const csrfToken= '{{ csrf_token() }}';

    let selectedMethod = null;

    // Select card on click
    cards.forEach(function (card) {
        card.addEventListener('click', function () {
            cards.forEach(function (c) { c.classList.remove('selected'); });
            card.classList.add('selected');
            card.querySelector('input[type=radio]').checked = true;
            selectedMethod = card.dataset.method;
            payBtn.disabled = false;
        });
    });

    // Pay button
    payBtn.addEventListener('click', function () {
        if (!selectedMethod) return;

        payBtn.disabled = true;
        payBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';

        fetch(tokenUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ method: selectedMethod })
        })
        .then(function (res) { return res.json(); })
        .then(function (data) {
            if (data.error) {
                alert(data.error);
                resetButton();
                return;
            }
            window.snap.pay(data.token, {
                onSuccess: function () { window.location.href = finishUrl; },
                onPending: function () { window.location.href = finishUrl; },
                onError:   function () {
                    alert('Pembayaran gagal. Silakan coba lagi.');
                    resetButton();
                },
                onClose:   function () { resetButton(); }
            });
        })
        .catch(function () {
            alert('Terjadi kesalahan. Silakan coba lagi.');
            resetButton();
        });
    });

    function resetButton() {
        payBtn.disabled = false;
        payBtn.innerHTML = '<i class="fas fa-lock"></i> Lanjutkan Pembayaran';
    }
})();


</script>
@endpush

