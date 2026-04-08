@extends('layouts.customer')

@section('title', $product->name . ' - Pusat Plastik Wijaya')

@section('content')
<div class="container" style="padding: 2rem 1rem;">
    {{-- Breadcrumb --}}
    <nav class="breadcrumb">
        <a href="{{ route('home') }}">Beranda</a>
        <i class="fas fa-chevron-right"></i>
        <a href="{{ route('products.index') }}">Produk</a>
        <i class="fas fa-chevron-right"></i>
        <a href="{{ route('products.index', ['category' => $product->category->slug]) }}">{{ $product->category->name }}</a>
        <i class="fas fa-chevron-right"></i>
        <span>{{ $product->name }}</span>
    </nav>

    {{-- Product Detail --}}
    <div class="product-detail">
        <div class="product-detail-image">
            @if($product->hasDiscount())
                <span class="product-badge-discount" style="font-size:1rem; padding:0.4rem 1rem;">
                    -{{ round((($product->price - $product->discount_price) / $product->price) * 100) }}%
                </span>
            @endif
            @if($product->image)
                <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" id="mainImage">
            @else
                <img src="https://placehold.co/600x500/e2e8f0/64748b?text={{ urlencode($product->name) }}" alt="{{ $product->name }}" id="mainImage">
            @endif
        </div>

        <div class="product-detail-info">
            <div class="product-detail-category">
                <a href="{{ route('products.index', ['category' => $product->category->slug]) }}">
                    {{ $product->category->name }}
                </a>
            </div>
            <h1 class="product-detail-name">{{ $product->name }}</h1>

            <div class="product-detail-price">
                @if($product->hasDiscount())
                    <span class="price-original-lg">Rp {{ number_format($product->price, 0, ',', '.') }}</span>
                    <span class="price-discount-lg">Rp {{ number_format($product->discount_price, 0, ',', '.') }}</span>
                    <span class="discount-save">Hemat Rp {{ number_format($product->price - $product->discount_price, 0, ',', '.') }}</span>
                @else
                    <span class="price-normal-lg">Rp {{ number_format($product->price, 0, ',', '.') }}</span>
                @endif
            </div>

            <div class="product-detail-meta">
                <div class="meta-item">
                    <i class="fas fa-weight-hanging"></i>
                    <span>Berat: {{ number_format($product->weight, 0) }}g</span>
                </div>
                <div class="meta-item">
                    <i class="fas fa-cubes"></i>
                    <span>Stok: 
                        @if($product->stock > 0)
                            <strong style="color:var(--success);">{{ $product->stock }} tersedia</strong>
                        @else
                            <strong style="color:var(--danger);">Habis</strong>
                        @endif
                    </span>
                </div>
            </div>

            @if($product->description)
                <div class="product-detail-desc">
                    <h3>Deskripsi Produk</h3>
                    <p>{{ $product->description }}</p>
                </div>
            @endif

            {{-- Unit Selector + Add to Cart --}}
            @php $availableUnits = $product->getAvailableUnits(); @endphp
            @if($product->stock > 0)
                <div class="add-to-cart-section">
                    {{-- Pilih Satuan --}}
                    @if(count($availableUnits) > 1)
                    <div class="unit-selector-row">
                        <span class="qty-label">Satuan:</span>
                        <div class="unit-options" id="unitOptions">
                            @foreach($availableUnits as $u)
                            @php $maxForUnit = floor($product->stock / $u['conversion_value']); @endphp
                            <button type="button"
                                class="unit-btn {{ $loop->first ? 'active' : '' }}{{ $maxForUnit < 1 ? ' disabled' : '' }}"
                                data-unit="{{ $u['unit'] }}"
                                data-price="{{ $u['price'] }}"
                                data-conversion="{{ $u['conversion_value'] }}"
                                {{ $maxForUnit < 1 ? 'disabled' : '' }}>
                                {{ $u['unit'] }}
                                @if($u['conversion_value'] > 1)
                                    <small>({{ $u['conversion_value'] }} {{ $availableUnits[0]['unit'] }})</small>
                                @endif
                            </button>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    {{-- Harga sesuai satuan --}}
                    <div id="selectedPriceDisplay" class="selected-price-display">
                        <span class="selected-price-label">Harga / {{ $availableUnits[0]['unit'] }}:</span>
                        <span id="selectedPrice" class="selected-price-value">Rp {{ number_format($availableUnits[0]['price'], 0, ',', '.') }}</span>
                    </div>

                    <div class="qty-row">
                        <span class="qty-label">Jumlah:</span>
                        <div class="quantity-control">
                            <button type="button" class="qty-btn" id="qtyMinus"><i class="fas fa-minus"></i></button>
                            <input type="number" id="quantity" value="1" min="1" max="{{ $product->stock }}">
                            <button type="button" class="qty-btn" id="qtyPlus"><i class="fas fa-plus"></i></button>
                        </div>
                        <span id="stockHint" class="stock-hint">Stok: {{ $product->stock }} {{ $product->unit }}</span>
                    </div>
                    <input type="hidden" id="selectedUnit" value="{{ $product->unit }}">
                    <div class="action-buttons">
                        @guest
                        <a href="{{ route('goto-login', ['from' => url()->current()]) }}"
                           class="btn btn-primary btn-lg" style="flex:1; text-align:center;">
                            <i class="fas fa-sign-in-alt"></i> Login untuk Membeli
                        </a>
                        @else
                        <button class="btn btn-outline-primary btn-lg" id="addToCartBtn"
                                data-product-id="{{ $product->id }}">
                            <i class="fas fa-cart-plus"></i> Keranjang
                        </button>
                        <button class="btn btn-primary btn-lg" id="buyNowBtn"
                                data-product-id="{{ $product->id }}">
                            <i class="fas fa-bolt"></i> Beli Langsung
                        </button>
                        @endguest
                    </div>
                </div>
            @else
                <div class="add-to-cart-section">
                    <button class="btn btn-secondary btn-lg" style="width:100%;" disabled>
                        <i class="fas fa-times"></i> Stok Habis
                    </button>
                </div>
            @endif
        </div>
    </div>

    {{-- Related Products --}}
    @if($relatedProducts->count() > 0)
        <div class="related-products">
            <h2><i class="fas fa-th-large"></i> Produk Terkait</h2>
            <div class="products-grid">
                @foreach($relatedProducts as $related)
                    <a href="{{ route('products.show', $related->slug) }}" class="product-card">
                        <div class="product-card-image">
                            @if($related->hasDiscount())
                                <span class="product-badge-discount">
                                    -{{ round((($related->price - $related->discount_price) / $related->price) * 100) }}%
                                </span>
                            @endif
                            @if($related->image)
                                <img src="{{ asset('storage/' . $related->image) }}" alt="{{ $related->name }}">
                            @else
                                <img src="https://placehold.co/400x300/e2e8f0/64748b?text={{ urlencode($related->name) }}" alt="{{ $related->name }}">
                            @endif
                        </div>
                        <div class="product-card-body">
                            <div class="product-card-category">{{ $related->category->name }}</div>
                            <h3 class="product-card-name">{{ $related->name }}</h3>
                            <div class="product-card-price">
                                @if($related->hasDiscount())
                                    <span class="price-original">Rp {{ number_format($related->price, 0, ',', '.') }}</span>
                                    <span class="price-discount">Rp {{ number_format($related->discount_price, 0, ',', '.') }}</span>
                                @else
                                    <span>Rp {{ number_format($related->price, 0, ',', '.') }}</span>
                                @endif
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection

@push('styles')
<style>
.breadcrumb {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.85rem;
    color: var(--gray-400);
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
}
.breadcrumb a { color: var(--gray-500); }
.breadcrumb a:hover { color: var(--primary); }
.breadcrumb span { color: var(--gray-700); font-weight: 500; }
.breadcrumb i { font-size: 0.6rem; }

.product-detail {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 2.5rem;
    margin-bottom: 3rem;
}
.product-detail-image {
    position: relative;
    border-radius: var(--radius-md);
    overflow: hidden;
    background: var(--gray-100);
    aspect-ratio: 1/1;
}
.product-detail-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.product-detail-category a {
    font-size: 0.8rem;
    color: var(--primary);
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.product-detail-name {
    font-size: 1.75rem;
    font-weight: 800;
    color: var(--gray-900);
    margin: 0.5rem 0 1rem;
    line-height: 1.3;
}
.product-detail-price {
    background: var(--gray-50);
    border-radius: var(--radius);
    padding: 1rem 1.25rem;
    margin-bottom: 1.25rem;
    border: 1px solid var(--gray-100);
}
.price-original-lg {
    text-decoration: line-through;
    color: var(--gray-400);
    font-size: 1rem;
    display: block;
    margin-bottom: 0.2rem;
}
.price-discount-lg {
    color: var(--danger);
    font-size: 1.75rem;
    font-weight: 800;
}
.price-normal-lg {
    color: var(--gray-900);
    font-size: 1.75rem;
    font-weight: 800;
}
.discount-save {
    display: inline-block;
    background: rgba(220,38,38,0.1);
    color: var(--danger);
    padding: 0.2rem 0.6rem;
    border-radius: var(--radius-sm);
    font-size: 0.8rem;
    font-weight: 600;
    margin-left: 0.5rem;
}
.product-detail-meta {
    display: flex;
    gap: 1.5rem;
    margin-bottom: 1.25rem;
}
.meta-item {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.9rem;
    color: var(--gray-600);
}
.meta-item i { color: var(--gray-400); }
.product-detail-desc {
    margin-bottom: 1.5rem;
}
.product-detail-desc h3 {
    font-size: 0.95rem;
    font-weight: 700;
    color: var(--gray-800);
    margin-bottom: 0.5rem;
}
.product-detail-desc p {
    color: var(--gray-600);
    font-size: 0.9rem;
    line-height: 1.7;
}
.add-to-cart-section {
    display: flex;
    flex-direction: column;
    gap: 0.85rem;
    padding-top: 1.5rem;
    border-top: 1px solid var(--gray-100);
}
.unit-selector-row {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
}
.unit-options {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}
.unit-btn {
    padding: 0.35rem 0.85rem;
    border: 2px solid var(--gray-200);
    border-radius: var(--radius);
    background: var(--white);
    color: var(--gray-700);
    font-size: 0.85rem;
    font-weight: 600;
    cursor: pointer;
    transition: var(--transition);
    line-height: 1.3;
    text-align: center;
}
.unit-btn small {
    display: block;
    font-size: 0.7rem;
    font-weight: 400;
    color: var(--gray-500);
}
.unit-btn:hover {
    border-color: var(--primary);
    color: var(--primary);
}
.unit-btn.active {
    border-color: var(--primary);
    background: var(--primary);
    color: var(--white);
}
.unit-btn.active small { color: rgba(255,255,255,0.8); }
.unit-btn.disabled, .unit-btn:disabled {
    background: var(--gray-100);
    color: var(--gray-400);
    border-color: var(--gray-200);
    cursor: not-allowed;
    opacity: 0.6;
}
.unit-btn.disabled:hover, .unit-btn:disabled:hover {
    border-color: var(--gray-200);
    color: var(--gray-400);
}
.selected-price-display {
    background: var(--gray-50);
    border: 1px solid var(--gray-100);
    border-radius: var(--radius);
    padding: 0.6rem 1rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.selected-price-label {
    font-size: 0.8rem;
    color: var(--gray-500);
    font-weight: 500;
}
.selected-price-value {
    font-size: 1.4rem;
    font-weight: 800;
    color: var(--primary);
}
.qty-row {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}
.qty-label {
    font-size: 0.875rem;
    font-weight: 600;
    color: var(--gray-600);
    white-space: nowrap;
}
.stock-hint {
    font-size: 0.8rem;
    color: var(--gray-400);
    margin-left: auto;
}
.quantity-control {
    display: flex;
    align-items: center;
    border: 2px solid var(--gray-200);
    border-radius: var(--radius);
    overflow: hidden;
}
.qty-btn {
    width: 38px;
    height: 38px;
    border: none;
    background: var(--gray-50);
    color: var(--gray-700);
    cursor: pointer;
    font-size: 0.85rem;
    transition: var(--transition);
}
.qty-btn:hover { background: var(--gray-200); }
.quantity-control input {
    width: 48px;
    height: 38px;
    text-align: center;
    border: none;
    font-size: 1rem;
    font-weight: 600;
    color: var(--gray-800);
    background: var(--white);
    -moz-appearance: textfield;
}
.quantity-control input::-webkit-outer-spin-button,
.quantity-control input::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}
.action-buttons {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.75rem;
}
.action-buttons .btn {
    font-size: 0.95rem;
    padding: 0.7rem 0.5rem;
    justify-content: center;
    white-space: nowrap;
}
.btn-outline-primary {
    background: transparent;
    color: var(--primary);
    border: 2px solid var(--primary);
}
.btn-outline-primary:hover {
    background: var(--primary);
    color: var(--white);
}
.related-products {
    margin-top: 2rem;
    padding-top: 2rem;
    border-top: 2px solid var(--gray-100);
}
.related-products h2 {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--gray-900);
    margin-bottom: 1.25rem;
}

@media (max-width: 768px) {
    .product-detail {
        grid-template-columns: 1fr;
        gap: 1.5rem;
    }
    .product-detail-name { font-size: 1.3rem; }
    .action-buttons .btn { font-size: 0.875rem; }
}
</style>
@endpush

@push('scripts')
<script>
const baseStock    = {{ $product->stock }};
const baseUnit     = @json($product->unit);
const qtyInput     = document.getElementById('quantity');
const stockHint    = document.getElementById('stockHint');
const selectedUnit = document.getElementById('selectedUnit');
const priceDisplay = document.getElementById('selectedPrice');
const priceLabel   = document.querySelector('.selected-price-label');

let currentConversion = 1; // how many base units per 1 selected unit
let currentUnitPrice  = {{ $availableUnits[0]['price'] }};

function maxQtyForConversion(conv) {
    return Math.max(1, Math.floor(baseStock / conv));
}

function updatePriceDisplay() {
    const qty = parseInt(qtyInput.value) || 1;
    const total = currentUnitPrice * qty;
    priceDisplay.textContent = 'Rp ' + total.toLocaleString('id-ID');
}

// Unit selector
document.querySelectorAll('.unit-btn').forEach(btn => {
    btn.addEventListener('click', function () {
        if (this.disabled) return;
        document.querySelectorAll('.unit-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');

        const unit  = this.dataset.unit;
        const price = parseFloat(this.dataset.price);
        const conv  = parseInt(this.dataset.conversion);

        currentConversion = conv;
        currentUnitPrice = price;
        selectedUnit.value = unit;
        if (priceLabel) priceLabel.textContent = 'Harga / ' + unit + ':';

        const maxQty = maxQtyForConversion(conv);
        qtyInput.max   = maxQty;
        qtyInput.value = 1;
        updatePriceDisplay();
        if (stockHint) {
            stockHint.textContent = conv > 1
                ? `Stok: ${baseStock} ${baseUnit} (maks ${maxQty} ${unit})`
                : `Stok: ${baseStock} ${unit}`;
        }
    });
});

document.getElementById('qtyMinus')?.addEventListener('click', () => {
    let val = parseInt(qtyInput.value);
    if (val > 1) {
        qtyInput.value = val - 1;
        updatePriceDisplay();
    }
});

document.getElementById('qtyPlus')?.addEventListener('click', () => {
    let val = parseInt(qtyInput.value);
    const maxQty = maxQtyForConversion(currentConversion);
    if (val < maxQty) {
        qtyInput.value = val + 1;
        updatePriceDisplay();
    }
});

qtyInput.addEventListener('input', () => {
    const maxQty = maxQtyForConversion(currentConversion);
    let val = parseInt(qtyInput.value);
    if (isNaN(val) || val < 1) val = 1;
    if (val > maxQty) val = maxQty;
    qtyInput.value = val;
    updatePriceDisplay();
});

function addToCartRequest(productId, quantity, unit, onSuccess, onError) {
    return fetch('{{ route("cart.add") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ product_id: productId, quantity: quantity, unit: unit })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            const badge = document.getElementById('cart-count');
            if (badge) badge.textContent = data.cart_count;
            onSuccess(data);
        } else {
            onError(data.message || 'Gagal menambahkan ke keranjang');
        }
    })
    .catch(() => onError('Terjadi kesalahan'));
}

// Tambah ke Keranjang
document.getElementById('addToCartBtn')?.addEventListener('click', function() {
    const btn = this;
    const productId = btn.dataset.productId;
    const quantity  = parseInt(qtyInput.value);
    const unit      = selectedUnit ? selectedUnit.value : baseUnit;

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

    addToCartRequest(productId, quantity, unit,
        () => {
            btn.innerHTML = '<i class="fas fa-check"></i> Ditambahkan!';
            btn.classList.replace('btn-outline-primary', 'btn-success');
            setTimeout(() => {
                btn.innerHTML = '<i class="fas fa-cart-plus"></i> Keranjang';
                btn.classList.replace('btn-success', 'btn-outline-primary');
                btn.disabled = false;
            }, 2000);
        },
        (msg) => {
            alert(msg);
            btn.innerHTML = '<i class="fas fa-cart-plus"></i> Keranjang';
            btn.disabled = false;
        }
    );
});

// Beli Langsung
document.getElementById('buyNowBtn')?.addEventListener('click', function() {
    const btn = this;
    const productId = btn.dataset.productId;
    const quantity  = parseInt(qtyInput.value);
    const unit      = selectedUnit ? selectedUnit.value : baseUnit;

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';

    addToCartRequest(productId, quantity, unit,
        () => { window.location.href = '{{ route("checkout.index") }}'; },
        (msg) => {
            alert(msg);
            btn.innerHTML = '<i class="fas fa-bolt"></i> Beli Langsung';
            btn.disabled = false;
        }
    );
});
</script>
@endpush
