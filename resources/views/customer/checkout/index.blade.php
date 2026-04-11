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

                {{-- Lokasi Pengiriman --}}
                <div class="card checkout-section" id="locationCard">
                    <div class="card-body">
                        <h3 class="section-title">
                            <i class="fas fa-map-marker-alt" style="color:var(--primary);"></i> Lokasi Pengiriman
                        </h3>
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
                </div>

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
                                    <p class="shipping-desc">Ambil langsung di toko, <b>Ruko Niaga Jl. Sedap Malam Kav 8-10 Blitar</b></p>
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

                            @if($rajaOngkirAvailable)
                            <div class="shipping-option" id="outsideOptionDiv" data-cost="0" data-type="outside" data-allow="outside" style="cursor:pointer;">
                                <input type="radio" id="outsideRadioInput" style="width:17px;height:17px;flex-shrink:0;cursor:pointer;" tabindex="-1">
                                <div class="shipping-info">
                                    <div class="shipping-name">
                                        <i class="fas fa-shipping-fast" style="color:var(--primary);"></i>
                                        <strong>Pengiriman Luar Kota</strong>
                                    </div>
                                    <p class="shipping-desc">Via ekspedisi (JNE, TIKI, POS) — pilih kurir & layanan di bawah</p>
                                </div>
                                <span class="shipping-price" style="color:var(--gray-400);" id="outsideShippingPrice">-</span>
                            </div>
                            @else
                            <div class="shipping-option disabled" data-allow="outside">
                                <input type="radio" disabled>
                                <div class="shipping-info">
                                    <div class="shipping-name">
                                        <i class="fas fa-shipping-fast" style="color:var(--gray-400);"></i>
                                        <strong>Pengiriman Luar Kota</strong>
                                        <span class="badge-coming">Segera Hadir</span>
                                    </div>
                                    <p class="shipping-desc">Via ekspedisi (JNE, TIKI, POS) — pilih kurir & layanan di bawah</p>
                                </div>
                                <span class="shipping-price" style="color:var(--gray-400);">-</span>
                            </div>
                            @endif

                            @if($rajaOngkirAvailable)
                            {{-- RajaOngkir Ekspedisi Section --}}
                            <div id="rajaongkirSection" style="display:none; margin-top:0.5rem;">
                                <div style="padding:1rem; background:var(--gray-50); border-radius:var(--radius); border:1.5px solid var(--gray-200);">

                                    <h4 style="font-size:0.88rem; font-weight:700; color:var(--gray-700); margin:0 0 0.35rem; display:flex; align-items:center; gap:0.4rem;">
                                        <i class="fas fa-map-marker-alt" style="color:var(--primary);"></i> Kode Pos Tujuan
                                    </h4>
                                    <p style="font-size:0.78rem; color:var(--gray-500); margin:0 0 0.75rem;">
                                        Masukkan kode pos (5 digit) atau nama kecamatan/kelurahan tujuan Anda.
                                    </p>

                                    {{-- Input + tombol cari --}}
                                    <div style="display:flex; gap:0.5rem; margin-bottom:0.5rem;">
                                        <div style="position:relative; flex:1;">
                                            <input type="text" id="ongkirSearch"
                                                placeholder="Contoh: 60243 (atau nama kecamatan)"
                                                maxlength="60"
                                                autocomplete="off"
                                                inputmode="text"
                                                style="width:100%; padding:0.5rem 0.75rem; border:1.5px solid var(--gray-200); border-radius:var(--radius-sm); font-size:0.9rem; box-sizing:border-box; font-weight:500; letter-spacing:0.03em;">
                                            <div id="ongkirSearchResults" style="display:none; position:absolute; top:100%; left:0; right:0; background:#fff; border:1.5px solid var(--gray-300); border-top:none; border-radius:0 0 var(--radius-sm) var(--radius-sm); max-height:220px; overflow-y:auto; z-index:999; box-shadow:0 4px 16px rgba(0,0,0,0.12);"></div>
                                        </div>
                                    </div>                

                                    {{-- Selected destination label --}}
                                    <div id="ongkirSelectedLabel" style="display:none; font-size:0.82rem; color:#065f46; margin-bottom:0.75rem; padding:0.5rem 0.75rem; background:#d1fae5; border-radius:var(--radius-sm); align-items:center; gap:0.5rem;">
                                        <i class="fas fa-check-circle"></i>
                                        <span id="ongkirSelectedText" style="font-weight:600;"></span>
                                    </div>

                                    <button type="button" id="btnCekOngkir" disabled class="btn btn-primary btn-sm" style="width:100%; margin-bottom:0.75rem;">
                                        <i class="fas fa-search"></i> Cek Ongkos Kirim
                                    </button>

                                    {{-- Loading --}}
                                    <div id="ongkirLoading" style="display:none; text-align:center; padding:1rem; color:var(--gray-400);">
                                        <i class="fas fa-spinner fa-spin"></i> Mengambil data ongkir...
                                    </div>

                                    {{-- Error --}}
                                    <div id="ongkirError" style="display:none; padding:0.75rem; background:#fee2e2; border:1px solid #fca5a5; border-radius:var(--radius-sm); color:#991b1b; font-size:0.82rem;">
                                    </div>

                                    {{-- Results --}}
                                    <div id="ongkirResults" style="display:none;">
                                        <p style="font-size:0.8rem; font-weight:600; color:var(--gray-600); margin:0 0 0.5rem;">
                                            <i class="fas fa-box"></i> Berat: <span id="ongkirWeight">0</span> gram &middot; Pilih layanan:
                                        </p>
                                        <div id="ongkirList" class="shipping-list" style="max-height:240px; overflow-y:auto;"></div>
                                    </div>

                                    {{-- Hidden fields --}}
                                    <input type="hidden" name="ongkir_service" id="ongkirServiceHidden" value="">
                                    <input type="hidden" name="ongkir_cost" id="ongkirCostHidden" value="0">
                                    <input type="hidden" name="ongkir_destination_id" id="ongkirDestinationIdHidden" value="">
                                    <input type="hidden" name="ongkir_destination" id="ongkirDestinationHidden" value="">
                                    <input type="hidden" name="ongkir_courier" id="ongkirCourierHidden" value="">
                                    <input type="hidden" name="ongkir_etd" id="ongkirEtdHidden" value="">
                                </div>
                            </div>
                            @endif

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
                    <div class="summary-items" id="summaryItemsList">
                        @foreach($cart->items as $item)
                        @php
                            $itemUnitPrice  = $item->product->getPriceForUnit($item->unit);
                            // Max qty in selected unit: for non-base units divide stock by conversion
                            $itemConversion = 1;
                            if ($item->unit && $item->unit !== $item->product->unit) {
                                $pu = $item->product->productUnits->firstWhere('unit', $item->unit);
                                if ($pu) $itemConversion = (int) $pu->conversion_value;
                            }
                            $itemMaxQty = $itemConversion > 1 ? (int) floor($item->product->stock / $itemConversion) : $item->product->stock;
                        @endphp
                        <div class="summary-item" id="summary-item-{{ $item->id }}"
                             data-item-id="{{ $item->id }}"
                             data-price="{{ $itemUnitPrice }}"
                             data-stock="{{ $item->product->stock }}"
                             data-max="{{ $itemMaxQty }}"
                             data-unit="{{ $item->unit }}">
                            <div class="summary-item-img">
                                @if($item->product->image)
                                    <img src="{{ asset('storage/' . $item->product->image) }}"
                                         alt="{{ $item->product->name }}">
                                @else
                                    <div class="summary-item-img-placeholder"><i class="fas fa-image"></i></div>
                                @endif
                            </div>
                            <div class="summary-item-detail">
                                <a href="{{ route('products.show', $item->product->slug) }}"
                                   target="_blank" rel="noopener"
                                   class="summary-item-name">{{ $item->product->name }}
                                    @if($item->unit && $item->unit !== $item->product->unit)
                                        <span style="font-size:0.7rem;font-weight:600;color:var(--primary);background:var(--primary-light);padding:0.1rem 0.35rem;border-radius:4px;margin-left:0.25rem;">{{ strtoupper($item->unit) }}</span>
                                    @endif
                                </a>
                                <div class="summary-item-controls">
                                    <button type="button" class="qty-btn qty-minus" data-id="{{ $item->id }}"
                                        {{ $item->quantity <= 1 ? 'disabled' : '' }}>−</button>
                                    <input type="number" class="qty-val" id="qty-{{ $item->id }}"
                                        value="{{ $item->quantity }}" min="1" max="{{ $itemMaxQty }}">
                                    <button type="button" class="qty-btn qty-plus" data-id="{{ $item->id }}" data-max="{{ $itemMaxQty }}"
                                        {{ $item->quantity >= $itemMaxQty ? 'disabled' : '' }}>+</button>
                                </div>
                            </div>
                            <div style="display:flex;flex-direction:column;align-items:flex-end;gap:0.3rem;">
                                <div class="summary-item-subtotal" id="sub-{{ $item->id }}">
                                    Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                                </div>
                                <button type="button" class="item-delete-btn" data-id="{{ $item->id }}" title="Hapus">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    {{-- Divider --}}
                    <div class="summary-divider"></div>

                    {{-- Empty state --}}
                    <div id="cartEmptyMsg" style="display:none;padding:1.25rem;text-align:center;color:var(--gray-400);font-size:0.85rem;">
                        <i class="fas fa-shopping-cart" style="font-size:1.5rem;display:block;margin-bottom:0.4rem;"></i>
                        Keranjang kosong
                    </div>

                    {{-- Totals --}}
                    <div class="summary-row">
                        <span>Subtotal</span>
                        <span id="checkout-subtotal">Rp {{ number_format($cart->total, 0, ',', '.') }}</span>
                    </div>
                    <div class="summary-row">
                        <span>Ongkos Kirim</span>
                        <span id="checkout-shipping" class="shipping-cost-display">Pilih metode dulu</span>
                    </div>

                    {{-- Points discount row (hidden until toggled) --}}
                    <div class="summary-row" id="points-discount-row" style="display:none; color:var(--success);">
                        <span><i class="fas fa-star" style="font-size:0.75rem;"></i> Diskon Poin (<span id="points-used-label">0</span> poin)</span>
                        <span id="points-discount-display" style="color:var(--success);">-Rp 0</span>
                    </div>

                    <div class="summary-row summary-total">
                        <span>Total</span>
                        <span id="checkout-total">Rp {{ number_format($cart->total, 0, ',', '.') }}</span>
                    </div>

                    {{-- Earned points preview --}}
                    <div id="earned-points-row" style="display:flex; align-items:center; justify-content:space-between; padding:0.55rem 1.25rem; background:linear-gradient(135deg,#f0fdf4,#dcfce7); border-top:1px dashed #86efac; font-size:0.8rem;">
                        <span style="color:#15803d; font-weight:600;">
                            <i class="fas fa-star" style="color:#16a34a; font-size:0.7rem;"></i>
                            Poin yang akan didapat
                        </span>
                        <span id="earned-points-value" style="color:#15803d; font-weight:700;">+{{ floor($cart->total / 100) }} poin</span>
                    </div>

                    {{-- Use Points section (always visible) --}}
                    <div style="margin:0 1.25rem 0.5rem; padding:0.85rem 1rem; border-radius:var(--radius); font-size:0.85rem;
                        {{ $user->points > 0 ? 'background:linear-gradient(135deg, #fef9c3 0%, #fefce8 100%); border:1.5px solid #fde047;' : 'background:#f9fafb; border:1.5px solid #e5e7eb;' }}">
                        @if($user->points > 0)
                            {{-- Has points — show toggle --}}
                            <div style="display:flex; align-items:center; justify-content:space-between; gap:0.75rem; flex-wrap:wrap;">
                                <div>
                                    <div style="font-weight:700; color:#854d0e; margin-bottom:0.15rem;">
                                        <i class="fas fa-star" style="color:#ca8a04;"></i> Gunakan Poin
                                    </div>
                                    <div style="font-size:0.78rem; color:#a16207;">
                                        Tersedia: <strong>{{ number_format($user->points, 0, ',', '.') }} poin</strong>
                                        (senilai Rp {{ number_format($user->points, 0, ',', '.') }})
                                    </div>
                                </div>
                                <label style="display:flex; align-items:center; gap:0.4rem; cursor:pointer; user-select:none;">
                                    <div class="points-toggle-wrap">
                                        <input type="checkbox" id="usePointsToggle" style="display:none;">
                                        <div class="points-toggle" id="pointsToggleVisual">
                                            <div class="points-toggle-knob"></div>
                                        </div>
                                    </div>
                                    <span id="pointsToggleLabel" style="font-size:0.78rem; color:#92400e; font-weight:600;">Tidak</span>
                                </label>
                            </div>
                            <div id="pointsInputGroup" style="display:none; margin-top:0.75rem; padding-top:0.65rem; border-top:1px dashed #fde047;">
                                <label style="font-size:0.78rem; font-weight:600; color:#854d0e; margin-bottom:0.3rem; display:block;">
                                    Jumlah poin yang digunakan:
                                </label>
                                <div style="display:flex; align-items:center; gap:0.5rem;">
                                    <input type="number" id="pointsAmountInput" min="1" max="{{ $user->points }}"
                                        value="{{ $user->points }}"
                                        style="width:110px; padding:0.35rem 0.5rem; border:1.5px solid #fde047; border-radius:var(--radius-sm); font-size:0.85rem; font-weight:600; text-align:center; background:#fffbeb; color:#78350f;">
                                    <button type="button" id="pointsMaxBtn"
                                        style="padding:0.3rem 0.65rem; background:#ca8a04; color:#fff; border:none; border-radius:var(--radius-sm); font-size:0.75rem; font-weight:700; cursor:pointer;">
                                        Semua
                                    </button>
                                    <span style="font-size:0.78rem; color:#92400e; font-weight:600;">
                                        = Rp <span id="pointsRpPreview">{{ number_format($user->points, 0, ',', '.') }}</span>
                                    </span>
                                </div>
                            </div>
                            <input type="hidden" name="use_points" id="usePointsHidden" value="0">
                        @else
                            {{-- No points yet — show info --}}
                            <div style="display:flex; align-items:center; gap:0.65rem;">
                                <i class="fas fa-star" style="color:#d1d5db; font-size:1.1rem; flex-shrink:0;"></i>
                                <div>
                                    <div style="font-weight:700; color:#6b7280; margin-bottom:0.1rem;">Gunakan Poin</div>
                                    <div style="font-size:0.78rem; color:#9ca3af;">
                                        Kamu belum punya poin.
                                        Selesaikan pesanan untuk mendapat poin belanja.
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" name="use_points" value="0">
                        @endif
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
    text-decoration: none;
    display: block;
}
a.summary-item-name:hover {
    color: var(--primary);
    text-decoration: underline;
}
.summary-item-qty { font-size: 0.75rem; color: var(--gray-500); margin-top: 0.1rem; }
.summary-item-subtotal {
    font-size: 0.82rem;
    font-weight: 700;
    color: var(--gray-700);
    white-space: nowrap;
}
.summary-item-controls {
    display: flex;
    align-items: center;
    gap: 0.3rem;
    margin-top: 0.25rem;
}
.qty-btn {
    width: 22px; height: 22px;
    border-radius: 4px;
    border: 1px solid var(--gray-300);
    background: var(--white);
    color: var(--gray-700);
    font-size: 0.9rem;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer;
    line-height: 1;
    padding: 0;
    transition: background 0.12s, border-color 0.12s;
}
.qty-btn:hover { background: var(--primary-light); border-color: var(--primary); color: var(--primary); }
.qty-btn:disabled { opacity: 0.35; cursor: not-allowed; }
.qty-val {
    width: 42px;
    min-width: 42px;
    text-align: center;
    font-size: 0.82rem;
    font-weight: 700;
    color: var(--gray-800);
    border: 1px solid var(--gray-200);
    border-radius: 4px;
    padding: 0.1rem 0.2rem;
    background: var(--white);
    -moz-appearance: textfield;
}
.qty-val::-webkit-inner-spin-button,
.qty-val::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }
.qty-val:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 2px rgba(37,99,235,0.15); }
.item-delete-btn {
    background: none;
    border: none;
    color: #f87171;
    font-size: 0.82rem;
    cursor: pointer;
    padding: 0.1rem 0.25rem;
    transition: color 0.12s;
}
.item-delete-btn:hover { color: var(--danger); }

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

/* Points toggle switch */
.points-toggle-wrap { display: flex; align-items: center; }
.points-toggle {
    width: 40px; height: 22px;
    background: #d1d5db;
    border-radius: 999px;
    position: relative;
    transition: background 0.2s;
    cursor: pointer;
}
.points-toggle.active { background: #ca8a04; }
.points-toggle-knob {
    position: absolute;
    top: 3px; left: 3px;
    width: 16px; height: 16px;
    background: #fff;
    border-radius: 50%;
    transition: transform 0.2s;
    box-shadow: 0 1px 3px rgba(0,0,0,0.2);
}
.points-toggle.active .points-toggle-knob { transform: translateX(18px); }

@media (max-width: 900px) {
    .checkout-grid { grid-template-columns: 1fr; }
    .checkout-right { position: static; }
    .summary-items { max-height: none; }
}
@media (max-width: 768px) {
    .form-grid-2 { grid-template-columns: 1fr; }
    .shipping-option { flex-wrap: wrap; gap: 0.5rem; }
    .qty-btn { width: 32px; height: 32px; }
}
@media (max-width: 576px) {
    .form-grid-2 { grid-template-columns: 1fr; }
    .summary-items { max-height: none; }
    .summary-item { gap: 0.5rem; }
    .summary-item-img { width: 36px; height: 36px; }
    .city-option { font-size: 0.82rem; padding: 0.55rem 0.75rem; }
}
</style>
@endpush

@push('scripts')
<script>
var subtotal           = {{ $cart->total }};
const customerAddress  = @json($user->address ?? '');
const csrfToken        = document.querySelector('meta[name="csrf-token"]').content;
const userPoints       = {{ $user->points }};  // available balance

function formatRupiah(num) {
    return 'Rp ' + Math.round(num).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
}

function getPointsDiscount() {
    var hidden = document.getElementById('usePointsHidden');
    if (!hidden) return 0;
    return parseInt(hidden.value, 10) || 0;
}

function recalcTotal() {
    var shippingSelected = document.querySelector('input[name="shipping_type"]:checked');
    var shippingCost = shippingSelected ? parseFloat(shippingSelected.closest('.shipping-option').dataset.cost) : 0;
    var pointsDiscount = getPointsDiscount();
    var total = subtotal + shippingCost - pointsDiscount;
    if (total < 0) total = 0;
    document.getElementById('checkout-total').textContent = formatRupiah(total);
    // Update earned points preview (floor(total / 100))
    var earned = Math.floor(total / 100);
    var earnedRow = document.getElementById('earned-points-row');
    var earnedVal = document.getElementById('earned-points-value');
    if (earned > 0) {
        earnedVal.textContent = '+' + earned.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.') + ' poin';
        earnedRow.style.display = 'flex';
    } else {
        earnedRow.style.display = 'none';
    }
}

function recalcSubtotal() {
    let total = 0;
    document.querySelectorAll('#summaryItemsList .summary-item').forEach(function(row) {
        const price = parseFloat(row.dataset.price);
        const qty   = parseInt(document.getElementById('qty-' + row.dataset.itemId).value, 10);
        total += price * qty;
    });
    subtotal = total;
    document.getElementById('checkout-subtotal').textContent = formatRupiah(total);
    // Recalculate using shared helper
    recalcTotal();
    // Also cap points in case subtotal dropped
    syncPointsInput();
    // Total items badge
    var count = 0;
    document.querySelectorAll('#summaryItemsList .summary-item').forEach(function(row) {
        count += parseInt(document.getElementById('qty-' + row.dataset.itemId).value, 10);
    });
    var badge = document.querySelector('.summary-item-count');
    if (badge) badge.textContent = count + ' item';
}

function updateCartItem(itemId, newQty) {
    fetch('/cart/' + itemId, {
        method: 'PATCH',
        headers: {'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/json', 'Accept': 'application/json'},
        body: JSON.stringify({quantity: newQty})
    }).then(function(r) { return r.json(); }).then(function(data) {
        if (data.success === false) return;
        var row   = document.getElementById('summary-item-' + itemId);
        var price = parseFloat(row.dataset.price);
        document.getElementById('sub-' + itemId).textContent = formatRupiah(price * newQty);
        recalcSubtotal();
    });
}

function deleteCartItem(itemId) {
    fetch('/cart/' + itemId, {
        method: 'DELETE',
        headers: {'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json'}
    }).then(function(r) { return r.json(); }).then(function() {
        var row = document.getElementById('summary-item-' + itemId);
        if (row) row.remove();
        recalcSubtotal();
        var remaining = document.querySelectorAll('#summaryItemsList .summary-item').length;
        if (remaining === 0) {
            document.getElementById('cartEmptyMsg').style.display = 'block';
            document.getElementById('btnCheckout').disabled = true;
        }
    });
}

// Qty minus
document.addEventListener('click', function(e) {
    var btn = e.target.closest('.qty-minus');
    if (!btn || btn.disabled) return;
    var id  = btn.dataset.id;
    var el  = document.getElementById('qty-' + id);
    var qty = parseInt(el.value, 10);
    if (qty <= 1) return; // min = 1
    el.value = qty - 1;
    // toggle buttons
    btn.disabled = (qty - 1) <= 1;
    var plusBtn = document.querySelector('.qty-plus[data-id="' + id + '"]');
    if (plusBtn) plusBtn.disabled = false;
    updateCartItem(id, qty - 1);
});

// Qty plus
document.addEventListener('click', function(e) {
    var btn = e.target.closest('.qty-plus');
    if (!btn || btn.disabled) return;
    var id   = btn.dataset.id;
    var row  = document.getElementById('summary-item-' + id);
    var max  = parseInt(row.dataset.max, 10);
    var el   = document.getElementById('qty-' + id);
    var qty  = parseInt(el.value, 10);
    if (qty >= max) return;
    el.value = qty + 1;
    // toggle buttons
    btn.disabled = (qty + 1) >= max;
    var minusBtn = document.querySelector('.qty-minus[data-id="' + id + '"]');
    if (minusBtn) minusBtn.disabled = false;
    updateCartItem(id, qty + 1);
});

// Delete
document.addEventListener('click', function(e) {
    var btn = e.target.closest('.item-delete-btn');
    if (!btn) return;
    deleteCartItem(btn.dataset.id);
});

// Direct qty input
document.addEventListener('change', function(e) {
    if (!e.target.classList.contains('qty-val')) return;
    var el  = e.target;
    var id  = el.id.replace('qty-', '');
    var row = document.getElementById('summary-item-' + id);
    var max = parseInt(row.dataset.max, 10);
    var qty = parseInt(el.value, 10);
    if (isNaN(qty) || qty < 1) qty = 1;
    if (qty > max) qty = max;
    el.value = qty;
    var minusBtn = document.querySelector('.qty-minus[data-id="' + id + '"]');
    var plusBtn  = document.querySelector('.qty-plus[data-id="' + id + '"]');
    if (minusBtn) minusBtn.disabled = qty <= 1;
    if (plusBtn)  plusBtn.disabled  = qty >= max;
    var price = parseFloat(row.dataset.price);
    document.getElementById('sub-' + id).textContent = formatRupiah(price * qty);
    recalcSubtotal();
    updateCartItem(id, qty);
});

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

    // If switching to outside, auto-activate ekspedisi option as default
    if (cityType === 'outside') {
        document.querySelectorAll('input[name="shipping_type"]').forEach(function(r) { r.checked = false; });
        document.querySelectorAll('.shipping-option').forEach(function(o) { o.classList.remove('selected'); });
        if (typeof window.activateOutsideOption === 'function') window.activateOutsideOption();
    }

    // Toggle address requirement based on shipping type
    updateAddressField();
}

function resetShippingDisplay() {
    document.querySelectorAll('.shipping-option').forEach(function(opt) { opt.classList.remove('selected'); });
    var shippingEl = document.getElementById('checkout-shipping');
    shippingEl.className = 'shipping-cost-display';
    shippingEl.textContent = 'Pilih metode dulu';
    recalcTotal();
}

// ── Shipping type change ──
function updateShipping() {
    const selected = document.querySelector('input[name="shipping_type"]:checked');
    if (!selected) return;

    const option = selected.closest('.shipping-option');
    const cost   = parseFloat(option.dataset.cost);

    const shippingEl = document.getElementById('checkout-shipping');
    shippingEl.className = 'shipping-price' + (cost === 0 ? ' free' : '');
    shippingEl.textContent = cost > 0 ? formatRupiah(cost) : 'Gratis';
    recalcTotal();

    document.querySelectorAll('.shipping-option').forEach(function(opt) { opt.classList.remove('selected'); });
    option.classList.add('selected');

    // If pickup chosen while city_type=outside, collapse the ongkir section
    if (selected.value === 'pickup' && typeof window.deactivateOutsideOption === 'function') {
        var ct = document.querySelector('input[name="shipping_city_type"]:checked');
        if (ct && ct.value === 'outside') window.deactivateOutsideOption();
    }

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
        addressInput.setAttribute('readonly', 'readonly');
        addressInput.style.cursor     = 'not-allowed';
        addressInput.style.background = 'var(--gray-100)';
        addressInput.value            = customerAddress;
    } else {
        addressInput.setAttribute('required', 'required');
        addressRequired.style.display = 'inline';
        addressGroup.style.opacity    = '1';
        addressInput.removeAttribute('readonly');
        addressInput.style.cursor     = '';
        addressInput.style.background = '';
    }
}

// ── Event listeners ──
document.querySelectorAll('input[name="shipping_city_type"]').forEach(function(radio) {
    radio.addEventListener('change', updateCityType);
});
document.querySelectorAll('input[name="shipping_type"]').forEach(function(radio) {
    radio.addEventListener('change', updateShipping);
});

// Init: sync UI state with current form values
function initCheckoutForm() {
    updateCityType();
    updateShipping();
}

// Run at script time (DOM already available since script is at </body>)
initCheckoutForm();

// Re-run after full page load — covers browser form-state restoration
// which can happen AFTER DOMContentLoaded but BEFORE window.load
window.addEventListener('load', initCheckoutForm);

// Re-run when page is restored from bfcache (browser Back button)
window.addEventListener('pageshow', function(event) {
    if (event.persisted) {
        initCheckoutForm();
    }
});

// Prevent double submit
document.getElementById('checkoutForm').addEventListener('submit', function() {
    const btn = document.getElementById('btnCheckout');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';
});

// ── Points Toggle ──
function syncPointsInput() {
    var toggle = document.getElementById('usePointsToggle');
    if (!toggle || !toggle.checked) return;
    // Re-cap the input value based on current subtotal + shipping
    var input      = document.getElementById('pointsAmountInput');
    var shippingEl = document.querySelector('input[name="shipping_type"]:checked');
    var shippingCost = shippingEl ? parseFloat(shippingEl.closest('.shipping-option').dataset.cost) : 0;
    var maxAllowed = Math.min(userPoints, Math.floor(subtotal + shippingCost));
    if (parseInt(input.value, 10) > maxAllowed) input.value = maxAllowed;
    applyPoints();
}

function applyPoints() {
    var toggle      = document.getElementById('usePointsToggle');
    var hidden      = document.getElementById('usePointsHidden');
    var discRow     = document.getElementById('points-discount-row');
    var usedLabel   = document.getElementById('points-used-label');
    var discDisplay = document.getElementById('points-discount-display');

    if (!toggle || !hidden) return;

    if (!toggle.checked) {
        hidden.value = 0;
        if (discRow) discRow.style.display = 'none';
        recalcTotal();
        return;
    }

    var input    = document.getElementById('pointsAmountInput');
    var shippingEl = document.querySelector('input[name="shipping_type"]:checked');
    var shippingCost = shippingEl ? parseFloat(shippingEl.closest('.shipping-option').dataset.cost) : 0;
    var maxAllowed = Math.min(userPoints, Math.floor(subtotal + shippingCost));
    var pts      = Math.min(parseInt(input.value, 10) || 0, maxAllowed);
    if (pts < 0) pts = 0;

    hidden.value = pts;
    if (usedLabel)   usedLabel.textContent   = pts.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    if (discDisplay) discDisplay.textContent  = '-Rp ' + pts.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    if (discRow)     discRow.style.display    = pts > 0 ? '' : 'none';

    var rpPreview = document.getElementById('pointsRpPreview');
    if (rpPreview) rpPreview.textContent = pts.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');

    recalcTotal();
}

(function initPointsToggle() {
    var toggle      = document.getElementById('usePointsToggle');
    var visual      = document.getElementById('pointsToggleVisual');
    var lbl         = document.getElementById('pointsToggleLabel');
    var inputGroup  = document.getElementById('pointsInputGroup');
    var input       = document.getElementById('pointsAmountInput');
    var maxBtn      = document.getElementById('pointsMaxBtn');

    if (!toggle) return;

    function getMaxPoints() {
        var shippingEl = document.querySelector('input[name="shipping_type"]:checked');
        var shippingCost = shippingEl ? parseFloat(shippingEl.closest('.shipping-option').dataset.cost) : 0;
        return Math.min(userPoints, Math.floor(subtotal + shippingCost));
    }

    toggle.addEventListener('change', function() {
        var on = toggle.checked;
        visual.classList.toggle('active', on);
        lbl.textContent = on ? 'Ya' : 'Tidak';
        inputGroup.style.display = on ? 'block' : 'none';
        if (on) {
            var maxPts = getMaxPoints();
            input.max = maxPts;
            input.value = maxPts;
        }
        applyPoints();
    });

    if (input) {
        input.addEventListener('input', function() {
            var maxPts = getMaxPoints();
            var v = parseInt(this.value, 10) || 0;
            if (v > maxPts) { this.value = maxPts; v = maxPts; }
            if (v < 0) { this.value = 0; v = 0; }
            applyPoints();
        });
    }

    if (maxBtn) {
        maxBtn.addEventListener('click', function() {
            var maxPts = getMaxPoints();
            if (input) input.value = maxPts;
            applyPoints();
        });
    }
})();

// ── RajaOngkir Integration (V2 — Komerce API) ──
@if($rajaOngkirAvailable ?? false)
(function initRajaOngkir() {
    var totalWeight     = {{ $totalWeight ?? 500 }};
    var searchInput     = document.getElementById('ongkirSearch');
    var searchResults   = document.getElementById('ongkirSearchResults');
    var selectedLabel   = document.getElementById('ongkirSelectedLabel');
    var selectedText    = document.getElementById('ongkirSelectedText');
    var btnCek          = document.getElementById('btnCekOngkir');
    var loadingEl       = document.getElementById('ongkirLoading');
    var errorEl         = document.getElementById('ongkirError');
    var resultsEl       = document.getElementById('ongkirResults');
    var listEl          = document.getElementById('ongkirList');
    var weightEl        = document.getElementById('ongkirWeight');
    var sectionEl       = document.getElementById('rajaongkirSection');

    if (!searchInput || !sectionEl) return;

    var selectedDestId   = null;
    var selectedDestName = '';
    var searchTimer      = null;

    if (weightEl) weightEl.textContent = totalWeight.toLocaleString('id-ID');

    var outsideDiv  = document.getElementById('outsideOptionDiv');
    var radioVisual = document.getElementById('outsideRadioInput');

    window.activateOutsideOption = function() {
        document.querySelectorAll('.shipping-option:not(.ongkir-option)').forEach(function(o) { o.classList.remove('selected'); });
        if (outsideDiv) outsideDiv.classList.add('selected');
        if (radioVisual) radioVisual.checked = true;
        if (sectionEl) sectionEl.style.display = 'block';
        var shippingEl = document.getElementById('checkout-shipping');
        if (shippingEl) { shippingEl.className = 'shipping-cost-display'; shippingEl.textContent = 'Pilih kurir dulu'; }
    };

    window.deactivateOutsideOption = function() {
        if (outsideDiv) outsideDiv.classList.remove('selected');
        if (radioVisual) radioVisual.checked = false;
        if (sectionEl) sectionEl.style.display = 'none';
        clearOngkirSelection();
        clearOngkirResults();
    };

    // Show/hide section based on city type
    function toggleSection() {
        var ct = document.querySelector('input[name="shipping_city_type"]:checked');
        if (ct && ct.value === 'outside') {
            var pickupChecked = document.querySelector('input[name="shipping_type"][value="pickup"]:checked');
            if (!pickupChecked) window.activateOutsideOption();
        } else {
            window.deactivateOutsideOption();
        }
    }

    document.querySelectorAll('input[name="shipping_city_type"]').forEach(function(r) {
        r.addEventListener('change', toggleSection);
    });
    toggleSection();

    // Click on outsideOptionDiv to activate ongkir section
    if (outsideDiv) {
        outsideDiv.addEventListener('click', function() {
            document.querySelectorAll('.shipping-option:not(#outsideOptionDiv) input[type="radio"]').forEach(function(r) {
                r.checked = false;
                r.closest('.shipping-option').classList.remove('selected');
            });
            window.activateOutsideOption();
            recalcTotal();
            updateAddressField();
        });
    }

    // Debounced search — auto-trigger on 5 digits (kode pos)
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimer);
        var q = this.value.trim();

        // Auto-search instantly when exactly 5 digits (kode pos)
        var isZip = /^\d{5}$/.test(q);
        if (isZip) {
            doSearch(q);
            return;
        }

        // Name search: min 3 chars, debounced 400ms
        if (q.length < 3) {
            searchResults.style.display = 'none';
            return;
        }
        searchTimer = setTimeout(function() { doSearch(q); }, 400);
    });

    searchInput.addEventListener('blur', function() {
        setTimeout(function() { searchResults.style.display = 'none'; }, 200);
    });

    function doSearch(q) {
        searchResults.innerHTML = '<div style="padding:0.6rem 0.75rem; color:var(--gray-400); font-size:0.82rem;"><i class="fas fa-spinner fa-spin"></i> Mencari...</div>';
        searchResults.style.display = 'block';

        fetch('/api/shipping/search-destinations?query=' + encodeURIComponent(q), {
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            searchResults.innerHTML = '';
            if (!data.success || !data.data || !data.data.length) {
                var d = document.createElement('div');
                d.style.cssText = 'padding:0.6rem 0.75rem; color:var(--gray-400); font-size:0.82rem;';
                d.textContent = 'Tidak ditemukan. Coba kode pos lain.';
                searchResults.appendChild(d);
            } else {
                data.data.forEach(function(item) {
                    var d = document.createElement('div');
                    d.style.cssText = 'padding:0.6rem 0.75rem; cursor:pointer; font-size:0.83rem; border-bottom:1px solid var(--gray-100); line-height:1.4;';
                    // Format: Kel. WONOKROMO — Kec. WONOKROMO, Kota SURABAYA (60243)
                    var parts = item.label.split(', ');
                    var sub  = parts[0] || '';
                    var dist = parts[1] || '';
                    var city = parts[2] || '';
                    var prov = parts[3] || '';
                    var zip  = parts[4] || '';
                    d.innerHTML =
                        '<span style="font-weight:700; color:var(--gray-800);">' + toTitle(sub) + '</span>' +
                        ' <span style="color:var(--gray-400); font-size:0.78rem;">Kec. ' + toTitle(dist) + '</span>' +
                        '<br><span style="color:var(--gray-600); font-size:0.78rem;">' +
                            toTitle(city) + ', ' + toTitle(prov) +
                            (zip ? ' <span style="background:#e0e7ff; color:#3730a3; padding:1px 5px; border-radius:3px; font-weight:600;">' + zip.trim() + '</span>' : '') +
                        '</span>';
                    d.addEventListener('mousedown', function() { onDestSelect(item); });
                    searchResults.appendChild(d);
                });
            }
            searchResults.style.display = 'block';
        })
        .catch(function() { searchResults.style.display = 'none'; });
    }

    function toTitle(str) {
        if (!str) return '';
        return str.trim().replace(/\w\S*/g, function(t) {
            return t.charAt(0).toUpperCase() + t.substr(1).toLowerCase();
        });
    }

    function onDestSelect(item) {
        selectedDestId   = item.id;
        selectedDestName = item.label;

        // Show friendly name: "Wonokromo, Surabaya, Jawa Timur (60243)"
        var parts = item.label.split(', ');
        var sub  = toTitle(parts[0] || '');
        var city = toTitle(parts[2] || '');
        var prov = toTitle(parts[3] || '');
        var zip  = (parts[4] || '').trim();
        var friendlyName = sub + ', ' + city + ', ' + prov + (zip ? ' (' + zip + ')' : '');

        searchInput.value = friendlyName;
        searchResults.style.display = 'none';
        selectedText.textContent = friendlyName;
        selectedLabel.style.display = 'flex';
        btnCek.disabled = false;
        clearOngkirResults();
    }

    // Cek Ongkir button
    btnCek.addEventListener('click', function() {
        if (!selectedDestId) return;

        loadingEl.style.display = 'block';
        errorEl.style.display = 'none';
        resultsEl.style.display = 'none';
        btnCek.disabled = true;
        btnCek.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mengecek...';

        fetch('/api/shipping/cost', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ destination_id: selectedDestId, weight: totalWeight })
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            loadingEl.style.display = 'none';
            btnCek.disabled = false;
            btnCek.innerHTML = '<i class="fas fa-search"></i> Cek Ongkos Kirim';
            if (!data.success || !data.data || !data.data.length) {
                errorEl.textContent = 'Tidak ada layanan pengiriman yang tersedia untuk tujuan ini.';
                errorEl.style.display = 'block';
                return;
            }
            renderOngkirOptions(data.data);
        })
        .catch(function() {
            loadingEl.style.display = 'none';
            errorEl.textContent = 'Gagal mengambil data ongkir. Silakan coba lagi.';
            errorEl.style.display = 'block';
            btnCek.disabled = false;
            btnCek.innerHTML = '<i class="fas fa-search"></i> Cek Ongkos Kirim';
        });
    });

    // Layanan yang di-skip: trucking, kargo, motor, barang bahaya/berharga, dan layanan khusus kurang relevan
    var SKIP_SERVICES = ['JTR', 'JTR<130', 'JTR>130', 'JTR>200', 'TRC', 'T15', 'T25', 'T60', 'SRP', 'TRX', 'PDG', 'PVG', 'PJB'];
    var SKIP_KEYWORDS = ['trucking', 'kargo', 'motor', 'dangerous', 'valuable', 'sirip', 'tirex'];

    function isSkipped(opt) {
        var svc  = (opt.service || '').toUpperCase().trim();
        var desc = (opt.description || '').toLowerCase();
        if (SKIP_SERVICES.indexOf(svc) !== -1) return true;
        for (var i = 0; i < SKIP_KEYWORDS.length; i++) {
            if (desc.indexOf(SKIP_KEYWORDS[i]) !== -1) return true;
        }
        return false;
    }

    function renderOngkirOptions(options) {
        listEl.innerHTML = '';
        resultsEl.style.display = 'block';

        var filtered = options.filter(function(opt) { return !isSkipped(opt); });

        if (!filtered.length) {
            listEl.innerHTML = '<p style="font-size:0.82rem;color:var(--gray-400);padding:0.5rem 0;">Tidak ada layanan reguler tersedia untuk rute ini.</p>';
            return;
        }

        filtered.forEach(function(opt, idx) {
            var courierKey = opt.code.toUpperCase() + ' ' + opt.service;
            var etdText    = opt.etd ? opt.etd.replace(/day/i, '').trim() : '-';
            var label      = document.createElement('label');
            label.className = 'shipping-option ongkir-option';
            label.dataset.cost = opt.cost;
            label.dataset.type = 'outside';
            label.innerHTML =
                '<input type="radio" name="shipping_type" value="outside"' +
                ' data-courier="' + courierKey + '"' +
                ' data-cost="' + opt.cost + '"' +
                ' data-etd="' + etdText + '">' +
                '<div class="shipping-info">' +
                    '<div class="shipping-name">' +
                        '<i class="fas fa-shipping-fast" style="color:var(--info);"></i> ' +
                        '<strong>' + opt.code.toUpperCase() + ' — ' + opt.service + '</strong>' +
                    '</div>' +
                    '<p class="shipping-desc">' + (opt.description || '') +
                        ' &middot; Est. ' + etdText + ' hari</p>' +
                '</div>' +
                '<span class="shipping-price">' + formatRupiah(opt.cost) + '</span>';
            listEl.appendChild(label);
        });

        listEl.querySelectorAll('input[name="shipping_type"]').forEach(function(radio) {
            radio.addEventListener('change', function() { onOngkirSelect(this); });
        });
    }

    function onOngkirSelect(radio) {
        var cost    = parseInt(radio.dataset.cost, 10);
        var courier = radio.dataset.courier;
        var etd     = radio.dataset.etd;

        document.getElementById('ongkirServiceHidden').value        = courier;
        document.getElementById('ongkirCostHidden').value           = cost;
        document.getElementById('ongkirDestinationIdHidden').value  = selectedDestId;
        document.getElementById('ongkirDestinationHidden').value    = selectedDestName;
        document.getElementById('ongkirCourierHidden').value        = courier;
        document.getElementById('ongkirEtdHidden').value            = etd;

        document.querySelectorAll('.ongkir-option').forEach(function(o) { o.classList.remove('selected'); });
        radio.closest('.ongkir-option').classList.add('selected');

        // Uncheck pickup/local (outsideOptionDiv has no radio, skip it)
        document.querySelectorAll('.shipping-option:not(.ongkir-option):not(#outsideOptionDiv) input[name="shipping_type"]').forEach(function(r) {
            r.checked = false;
            r.closest('.shipping-option').classList.remove('selected');
        });
        // Keep outsideOptionDiv visually selected as the active category
        if (outsideDiv) outsideDiv.classList.add('selected');
        if (radioVisual) radioVisual.checked = true;

        recalcTotal();
    }

    function clearOngkirSelection() {
        document.getElementById('ongkirServiceHidden').value       = '';
        document.getElementById('ongkirCostHidden').value          = '0';
        document.getElementById('ongkirDestinationIdHidden').value = '';
        document.getElementById('ongkirDestinationHidden').value   = '';
        document.getElementById('ongkirCourierHidden').value       = '';
        document.getElementById('ongkirEtdHidden').value           = '';
    }

    function clearOngkirResults() {
        if (resultsEl) resultsEl.style.display = 'none';
        if (errorEl)   errorEl.style.display   = 'none';
        if (listEl)    listEl.innerHTML         = '';
        clearOngkirSelection();
        // Reset shipping cost display if outside option is still the active category
        var ct = document.querySelector('input[name="shipping_city_type"]:checked');
        if (ct && ct.value === 'outside' && outsideDiv && outsideDiv.classList.contains('selected')) {
            var shippingEl = document.getElementById('checkout-shipping');
            if (shippingEl) { shippingEl.className = 'shipping-cost-display'; shippingEl.textContent = 'Pilih kurir dulu'; }
        }
    }

    // Override recalcTotal to support ongkir cost
    var _origRecalcTotal = window.recalcTotal;
    window.recalcTotal = function() {
        var ongkirRadio = document.querySelector('.ongkir-option input[name="shipping_type"]:checked');
        if (ongkirRadio) {
            var shippingCost   = parseInt(ongkirRadio.dataset.cost, 10) || 0;
            var pointsDiscount = getPointsDiscount();
            var total          = Math.max(0, subtotal + shippingCost - pointsDiscount);

            // Update ongkos kirim di ringkasan
            var shippingDisplay = document.getElementById('checkout-shipping');
            shippingDisplay.className = 'shipping-price';
            shippingDisplay.textContent = formatRupiah(shippingCost);

            document.getElementById('checkout-total').textContent = formatRupiah(total);
            var earned    = Math.floor(total / 100);
            var earnedRow = document.getElementById('earned-points-row');
            var earnedVal = document.getElementById('earned-points-value');
            if (earned > 0) {
                earnedVal.textContent = '+' + earned.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.') + ' poin';
                earnedRow.style.display = 'flex';
            } else {
                earnedRow.style.display = 'none';
            }
            return;
        }
        _origRecalcTotal();
    };

    // When pickup/local selected, clear ongkir selection
    document.querySelectorAll('.shipping-option:not(.ongkir-option) input[name="shipping_type"]').forEach(function(radio) {
        radio.addEventListener('change', function() {
            document.querySelectorAll('.ongkir-option').forEach(function(o) {
                o.classList.remove('selected');
                o.querySelector('input').checked = false;
            });
            clearOngkirSelection();
        });
    });
})();
@endif
</script>
@endpush
