@extends('layouts.customer')

@section('title', 'Checkout - Pusat Plastik Wijaya')

@section('content')
<div style="padding: 0.5rem;">
    <div class="page-header">
        <h1><i class="fas fa-credit-card"></i> Checkout</h1>
        <a href="{{ route('cart.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Kembali ke Keranjang
        </a>
    </div>

    <form action="{{ route('checkout.store') }}" method="POST" id="checkoutForm">
        @csrf
        <div class="checkout-grid">

            {{-- ======================== LEFT COLUMN ======================== --}}
            <div class="checkout-left">

                {{-- Metode Pengiriman --}}
                <div class="card checkout-section" id="shippingMethodCard">
                    <div class="card-body">
                        <h3 class="section-title">
                            <i class="fas fa-truck" style="color:var(--primary);"></i> Metode Pengiriman
                        </h3>

                        <div class="shipping-list">
                            @php $pickup = $shippingMethods->get('pickup'); @endphp
                            @if($pickup && $pickup->is_active)
                            <label class="shipping-option {{ old('shipping_type') === 'pickup' ? 'selected' : '' }}" data-cost="0" data-type="pickup" data-allow="blitar,outside">
                                <input type="radio" name="shipping_type" value="pickup" {{ old('shipping_type') === 'pickup' ? 'checked' : '' }} required>
                                <div class="shipping-info">
                                    <div class="shipping-name">
                                        <i class="fas fa-store" style="color:var(--success);"></i>
                                        <strong>Pickup (Ambil di Toko)</strong>
                                    </div>
                                    <p class="shipping-desc">Ambil langsung di toko Pusat Plastik Wijaya, Blitar</p>
                                </div>
                                <span class="shipping-price free">Gratis</span>
                            </label>
                            @endif

                            @php $local = $shippingMethods->get('local'); @endphp
                            @if($local && $local->is_active)
                            <label class="shipping-option {{ old('shipping_type') === 'local' ? 'selected' : '' }}" data-cost="{{ $local->cost }}" data-type="local" data-allow="blitar">
                                <input type="radio" name="shipping_type" value="local" {{ old('shipping_type') === 'local' ? 'checked' : '' }}>
                                <div class="shipping-info">
                                    <div class="shipping-name">
                                        <i class="fas fa-motorcycle" style="color:var(--primary);"></i>
                                        <strong>Kurir Toko</strong>
                                    </div>
                                    <p class="shipping-desc">
                                        {{ $local->description ?? 'Pengiriman dalam kota Blitar' }}
                                        @if($local->estimation) &middot; Est. {{ $local->estimation }} @endif
                                    </p>
                                </div>
                                <span class="shipping-price">Rp {{ number_format($local->cost, 0, ',', '.') }}</span>
                            </label>
                            @endif

                            <div class="shipping-option disabled" data-allow="outside">
                                <input type="radio" disabled>
                                <div class="shipping-info">
                                    <div class="shipping-name">
                                        <i class="fas fa-shipping-fast" style="color:var(--gray-400);"></i>
                                        <strong>Pengiriman Luar Kota</strong>
                                        <span class="badge-coming">Segera Hadir</span>
                                    </div>
                                    <p class="shipping-desc">Via ekspedisi (JNE, J&T, dll) — segera tersedia</p>
                                </div>
                                <span class="shipping-price" style="color:var(--gray-400);">-</span>
                            </div>

                            @if((!$pickup || !$pickup->is_active) && (!$local || !$local->is_active))
                            <div class="no-shipping-warning">
                                <i class="fas fa-exclamation-triangle"></i>
                                Saat ini tidak ada metode pengiriman yang tersedia. Silakan hubungi toko.
                            </div>
                            @endif
                        </div>

                        @error('shipping_type')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                {{-- Informasi Penerima --}}
                <div class="card checkout-section">
                    <div class="card-body">
                        <h3 class="section-title">
                            <i class="fas fa-user" style="color:var(--primary);"></i> Informasi Penerima
                        </h3>

                        <div class="form-grid-2">
                            <div class="form-group">
                                <label>Nama Penerima <span class="required-star">*</span></label>
                                <input type="text" name="recipient_name"
                                    value="{{ old('recipient_name', $user->name) }}"
                                    class="{{ $errors->has('recipient_name') ? 'is-invalid' : '' }}"
                                    placeholder="Nama lengkap penerima" required>
                                @error('recipient_name')<span class="error-message">{{ $message }}</span>@enderror
                            </div>
                            <div class="form-group">
                                <label>No. Telepon <span class="required-star">*</span></label>
                                <input type="text" name="recipient_phone"
                                    value="{{ old('recipient_phone', $user->phone) }}"
                                    class="{{ $errors->has('recipient_phone') ? 'is-invalid' : '' }}"
                                    placeholder="08xxxxxxxxxx" required>
                                @error('recipient_phone')<span class="error-message">{{ $message }}</span>@enderror
                            </div>
                        </div>

                        {{-- City type selector --}}
                        <div class="form-group">
                            <label>Lokasi Pengiriman <span class="required-star">*</span></label>
                            <div style="display:flex; gap:0.75rem; margin-top:0.25rem;">
                                <label class="city-option {{ old('shipping_city_type', $user->city_type) === 'blitar' ? 'selected' : '' }}">
                                    <input type="radio" name="shipping_city_type" value="blitar"
                                        {{ old('shipping_city_type', $user->city_type) === 'blitar' ? 'checked' : '' }} required>
                                    <i class="fas fa-city"></i>
                                    <span>Kota Blitar</span>
                                </label>
                                <label class="city-option {{ old('shipping_city_type', $user->city_type) === 'outside' ? 'selected' : '' }}">
                                    <input type="radio" name="shipping_city_type" value="outside"
                                        {{ old('shipping_city_type', $user->city_type) === 'outside' ? 'checked' : '' }}>
                                    <i class="fas fa-globe-asia"></i>
                                    <span>Luar Kota Blitar</span>
                                </label>
                            </div>
                            @error('shipping_city_type')<span class="error-message">{{ $message }}</span>@enderror
                        </div>

                        <div class="form-group" id="addressGroup">
                            <label>Alamat Lengkap <span class="required-star" id="addressRequired">*</span></label>
                            <textarea name="shipping_address" rows="3" id="shippingAddress"
                                class="{{ $errors->has('shipping_address') ? 'is-invalid' : '' }}"
                                placeholder="Contoh: Jl. Bali No. 20, RT 03/RW 05, Kel. Sananwetan" required>{{ old('shipping_address', $user->address) }}</textarea>
                            @error('shipping_address')<span class="error-message">{{ $message }}</span>@enderror
                        </div>

                        <div class="form-group">
                            <label>Catatan (opsional)</label>
                            <textarea name="notes" rows="2" placeholder="Catatan tambahan untuk pesanan...">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </div>

            </div>{{-- end left --}}

            {{-- ======================== RIGHT COLUMN ======================== --}}
            <div class="checkout-right">
                <div class="summary-card">

                    {{-- Header --}}
                    <div class="summary-header">
                        <h3><i class="fas fa-receipt" style="color:var(--primary);"></i> Ringkasan Pesanan</h3>
                        <span class="summary-item-count">{{ $cart->total_items }} item</span>
                    </div>

                    {{-- Product list --}}
                    <div class="summary-items">
                        @foreach($cart->items as $item)
                        <div class="summary-item">
                            <div class="summary-item-img">
                                @if($item->product->image)
                                    <img src="{{ asset('storage/' . $item->product->image) }}"
                                         alt="{{ $item->product->name }}">
                                @else
                                    <div class="summary-item-img-placeholder"><i class="fas fa-image"></i></div>
                                @endif
                            </div>
                            <div class="summary-item-detail">
                                <div class="summary-item-name">{{ $item->product->name }}</div>
                                <div class="summary-item-qty">{{ $item->quantity }} × Rp {{ number_format($item->product->getEffectivePrice(), 0, ',', '.') }}</div>
                            </div>
                            <div class="summary-item-subtotal">
                                Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                            </div>
                        </div>
                        @endforeach
                    </div>

                    {{-- Divider --}}
                    <div class="summary-divider"></div>

                    {{-- Totals --}}
                    <div class="summary-row">
                        <span>Subtotal</span>
                        <span id="checkout-subtotal">Rp {{ number_format($cart->total, 0, ',', '.') }}</span>
                    </div>
                    <div class="summary-row">
                        <span>Ongkos Kirim</span>
                        <span id="checkout-shipping" class="shipping-cost-display">Pilih metode dulu</span>
                    </div>
                    <div class="summary-row summary-total">
                        <span>Total</span>
                        <span id="checkout-total">Rp {{ number_format($cart->total, 0, ',', '.') }}</span>
                    </div>

                    {{-- Submit --}}
                    <button type="submit" class="btn btn-primary btn-lg" style="width:100%; margin-top:1.25rem;" id="btnCheckout">
                        <i class="fas fa-check-circle"></i> Buat Pesanan
                    </button>
                    <p class="summary-note">Dengan menekan tombol di atas, Anda menyetujui pesanan ini</p>

                </div>
            </div>

        </div>{{-- end checkout-grid --}}
    </form>
</div>
@endsection

@push('styles')
<style>
/* Layout */
.checkout-grid {
    display: grid;
    grid-template-columns: 1fr 400px;
    gap: 2rem;
    align-items: start;
}
.checkout-left { display: flex; flex-direction: column; gap: 1.25rem; }
.checkout-right { position: sticky; top: 5rem; }
.checkout-section { margin-bottom: 0 !important; }

/* Section titles */
.section-title {
    font-size: 1rem;
    font-weight: 700;
    color: var(--gray-800);
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

/* Shipping options */
.shipping-list { display: flex; flex-direction: column; gap: 0.6rem; }
.shipping-option {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.85rem 1rem;
    border: 2px solid var(--gray-200);
    border-radius: var(--radius);
    cursor: pointer;
    transition: var(--transition);
}
.shipping-option:not(.disabled):hover {
    border-color: var(--primary-light);
    background: rgba(59,130,246,0.03);
}
.shipping-option.selected,
.shipping-option:has(input:checked) {
    border-color: var(--primary);
    background: rgba(59,130,246,0.05);
}
.shipping-option.disabled {
    opacity: 0.45;
    cursor: not-allowed;
    border-style: dashed;
}
.shipping-option input[type="radio"] {
    width: 17px; height: 17px;
    flex-shrink: 0;
    cursor: pointer;
}
.shipping-option.disabled input { cursor: not-allowed; }
.shipping-info { flex: 1; min-width: 0; }
.shipping-name { display: flex; align-items: center; gap: 0.4rem; font-size: 0.9rem; }
.shipping-desc { margin: 0.15rem 0 0; font-size: 0.78rem; color: var(--gray-500); }
.shipping-price { font-weight: 700; font-size: 0.9rem; color: var(--primary); white-space: nowrap; }
.shipping-price.free { color: var(--success); }
.badge-coming {
    background: #fef3c7; color: #92400e;
    font-size: 0.68rem; font-weight: 600;
    padding: 0.1rem 0.4rem;
    border-radius: 999px;
}
.no-shipping-warning {
    padding: 0.85rem 1rem;
    background: #fef3c7;
    border: 1px solid #fde68a;
    border-radius: var(--radius);
    color: #92400e;
    font-size: 0.85rem;
}

/* Form helpers */
.form-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
.required-star { color: var(--danger); }

/* City type selector */
.city-option {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex: 1;
    padding: 0.7rem 1rem;
    border: 2px solid var(--gray-200);
    border-radius: var(--radius);
    cursor: pointer;
    transition: var(--transition);
    font-weight: 600;
    font-size: 0.88rem;
    color: var(--gray-600);
}
.city-option:hover { border-color: var(--primary-light); }
.city-option.selected,
.city-option:has(input:checked) {
    border-color: var(--primary);
    background: rgba(59,130,246,0.05);
    color: var(--primary);
}
.city-option input[type="radio"] { display: none; }

/* Summary card */
.summary-card {
    background: var(--white);
    border-radius: var(--radius-md);
    border: 1px solid var(--gray-100);
    box-shadow: var(--shadow);
    overflow: hidden;
}
.summary-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1.1rem 1.25rem 0.9rem;
    border-bottom: 2px solid var(--gray-100);
}
.summary-header h3 {
    font-size: 1rem;
    font-weight: 700;
    color: var(--gray-800);
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.4rem;
}
.summary-item-count {
    font-size: 0.78rem;
    background: var(--gray-100);
    color: var(--gray-600);
    padding: 0.2rem 0.6rem;
    border-radius: 999px;
    font-weight: 600;
}

/* Product list inside summary */
.summary-items {
    padding: 0.75rem 1.25rem;
    display: flex;
    flex-direction: column;
    gap: 0.6rem;
    max-height: 280px;
    overflow-y: auto;
}
.summary-items::-webkit-scrollbar { width: 4px; }
.summary-items::-webkit-scrollbar-track { background: transparent; }
.summary-items::-webkit-scrollbar-thumb { background: var(--gray-200); border-radius: 99px; }
.summary-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}
.summary-item-img {
    width: 42px; height: 42px;
    border-radius: var(--radius-sm);
    overflow: hidden;
    flex-shrink: 0;
    background: var(--gray-50);
    border: 1px solid var(--gray-200);
}
.summary-item-img img { width: 100%; height: 100%; object-fit: contain; padding: 2px; }
.summary-item-img-placeholder {
    width: 100%; height: 100%;
    display: flex; align-items: center; justify-content: center;
    color: var(--gray-300); font-size: 0.9rem;
}
.summary-item-detail { flex: 1; min-width: 0; }
.summary-item-name {
    font-size: 0.82rem;
    font-weight: 600;
    color: var(--gray-800);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.summary-item-qty { font-size: 0.75rem; color: var(--gray-500); margin-top: 0.1rem; }
.summary-item-subtotal {
    font-size: 0.82rem;
    font-weight: 700;
    color: var(--gray-700);
    white-space: nowrap;
}

/* Totals section */
.summary-divider { height: 2px; background: var(--gray-100); margin: 0; }
.summary-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.45rem 1.25rem;
    font-size: 0.88rem;
    color: var(--gray-600);
}
.summary-total {
    border-top: 2px solid var(--gray-100);
    padding: 0.65rem 1.25rem;
    font-size: 1.05rem;
    font-weight: 700;
    color: var(--gray-900);
}
.shipping-cost-display { font-size: 0.8rem; color: var(--gray-400); font-style: italic; }
.summary-note {
    font-size: 0.72rem;
    color: var(--gray-400);
    text-align: center;
    margin: 0.5rem 0 0;
    padding: 0 1.25rem 1rem;
}
.summary-card .btn {
    margin-left: 1.25rem;
    margin-right: 1.25rem;
    width: calc(100% - 2.5rem) !important;
}

@media (max-width: 900px) {
    .checkout-grid { grid-template-columns: 1fr; }
    .checkout-right { position: static; }
    .summary-items { max-height: none; }
}
@media (max-width: 576px) {
    .form-grid-2 { grid-template-columns: 1fr; }
}
</style>
@endpush

@push('scripts')
<script>
const subtotal = {{ $cart->total }};

function formatRupiah(num) {
    return 'Rp ' + Math.round(num).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
}

// ── City type change: filter shipping options ──
function updateCityType() {
    const selected = document.querySelector('input[name="shipping_city_type"]:checked');
    if (!selected) return;

    const cityType = selected.value;

    // Update city selector visual
    document.querySelectorAll('.city-option').forEach(function(opt) { opt.classList.remove('selected'); });
    selected.closest('.city-option').classList.add('selected');

    // Show/hide shipping options based on data-allow
    document.querySelectorAll('.shipping-option[data-allow]').forEach(function(opt) {
        const allowed = opt.dataset.allow.split(',');
        if (allowed.includes(cityType)) {
            opt.style.display = '';
            opt.classList.remove('city-hidden');
        } else {
            opt.style.display = 'none';
            opt.classList.add('city-hidden');
            // Uncheck if hidden
            const radio = opt.querySelector('input[type="radio"]');
            if (radio && radio.checked) {
                radio.checked = false;
                resetShippingDisplay();
            }
        }
    });

    // Toggle address requirement based on shipping type
    updateAddressField();
}

function resetShippingDisplay() {
    document.querySelectorAll('.shipping-option').forEach(function(opt) { opt.classList.remove('selected'); });
    var shippingEl = document.getElementById('checkout-shipping');
    shippingEl.className = 'shipping-cost-display';
    shippingEl.textContent = 'Pilih metode dulu';
    document.getElementById('checkout-total').textContent = formatRupiah(subtotal);
}

// ── Shipping type change ──
function updateShipping() {
    const selected = document.querySelector('input[name="shipping_type"]:checked');
    if (!selected) return;

    const option = selected.closest('.shipping-option');
    const cost   = parseFloat(option.dataset.cost);
    const type   = option.dataset.type;

    const shippingEl = document.getElementById('checkout-shipping');
    shippingEl.className = 'shipping-price' + (cost === 0 ? ' free' : '');
    shippingEl.textContent = cost > 0 ? formatRupiah(cost) : 'Gratis';
    document.getElementById('checkout-total').textContent = formatRupiah(subtotal + cost);

    document.querySelectorAll('.shipping-option').forEach(function(opt) { opt.classList.remove('selected'); });
    option.classList.add('selected');

    updateAddressField();
}

function updateAddressField() {
    const selected = document.querySelector('input[name="shipping_type"]:checked');
    const addressGroup    = document.getElementById('addressGroup');
    const addressInput    = document.getElementById('shippingAddress');
    const addressRequired = document.getElementById('addressRequired');

    if (selected && selected.value === 'pickup') {
        addressInput.removeAttribute('required');
        addressRequired.style.display = 'none';
        addressGroup.style.opacity    = '0.5';
    } else {
        addressInput.setAttribute('required', 'required');
        addressRequired.style.display = 'inline';
        addressGroup.style.opacity    = '1';
    }
}

// ── Event listeners ──
document.querySelectorAll('input[name="shipping_city_type"]').forEach(function(radio) {
    radio.addEventListener('change', updateCityType);
});
document.querySelectorAll('input[name="shipping_type"]').forEach(function(radio) {
    radio.addEventListener('change', updateShipping);
});

// Init on page load
updateCityType();
updateShipping();

// Prevent double submit
document.getElementById('checkoutForm').addEventListener('submit', function() {
    const btn = document.getElementById('btnCheckout');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';
});
</script>
@endpush
