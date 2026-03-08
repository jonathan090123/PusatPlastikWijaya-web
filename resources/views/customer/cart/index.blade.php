@extends('layouts.customer')

@section('title', 'Keranjang Belanja - Pusat Plastik Wijaya')

@section('content')
<div class="container" style="padding: 2rem 1rem;">
    <div class="cart-header">
        <h1><i class="fas fa-shopping-cart"></i> Keranjang Belanja</h1>
        <a href="{{ route('products.index') }}" class="btn btn-outline-primary btn-sm">
            <i class="fas fa-arrow-left"></i> Lanjut Belanja
        </a>
    </div>

    @if($cart->items->count() > 0)
        <div class="cart-layout">
            <div class="cart-items">
                @foreach($cart->items as $item)
                    <div class="cart-item" id="cart-item-{{ $item->id }}">
                        <div class="cart-item-image">
                            @if($item->product->image)
                                <img src="{{ asset('storage/' . $item->product->image) }}" alt="{{ $item->product->name }}">
                            @else
                                <div class="cart-item-placeholder">
                                    <i class="fas fa-image"></i>
                                </div>
                            @endif
                        </div>
                        <div class="cart-item-info">
                            <a href="{{ route('products.show', $item->product->slug) }}" class="cart-item-name">
                                {{ $item->product->name }}
                            </a>
                            <span class="cart-item-category">{{ $item->product->category->name }}</span>
                            <div class="cart-item-price">
                                @if($item->product->hasDiscount())
                                    <span class="price-original" style="font-size:0.75rem;">Rp {{ number_format($item->product->price, 0, ',', '.') }}</span>
                                    <span style="color:var(--danger); font-weight:700;">Rp {{ number_format($item->product->getEffectivePrice(), 0, ',', '.') }}</span>
                                @else
                                    <span style="font-weight:700;">Rp {{ number_format($item->product->price, 0, ',', '.') }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="cart-item-actions">
                            <div class="quantity-control">
                                <button type="button" class="qty-btn cart-qty-minus" data-item-id="{{ $item->id }}">
                                    <i class="fas fa-minus"></i>
                                </button>
                                <input type="number" class="cart-qty-input" value="{{ $item->quantity }}" readonly
                                       data-item-id="{{ $item->id }}" data-max="{{ $item->product->stock }}">
                                <button type="button" class="qty-btn cart-qty-plus" data-item-id="{{ $item->id }}"
                                        data-max="{{ $item->product->stock }}">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                            <div class="cart-item-subtotal" id="subtotal-{{ $item->id }}">
                                Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                            </div>
                            <button class="btn btn-icon btn-danger btn-sm cart-remove-btn" data-item-id="{{ $item->id }}" title="Hapus">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="cart-summary">
                <div class="summary-card">
                    <h3>Ringkasan Belanja</h3>
                    <div class="summary-row">
                        <span>Total Item</span>
                        <span id="summary-items">{{ $cart->total_items }} produk</span>
                    </div>
                    <div class="summary-row summary-total">
                        <span>Total Harga</span>
                        <span id="summary-total">Rp {{ number_format($cart->total, 0, ',', '.') }}</span>
                    </div>
                    <button class="btn btn-primary btn-lg" style="width:100%; margin-top:1rem;" disabled>
                        <i class="fas fa-credit-card"></i> Checkout
                    </button>
                    <p style="font-size:0.75rem; color:var(--gray-400); text-align:center; margin-top:0.5rem;">
                        Fitur checkout akan segera hadir
                    </p>
                </div>
            </div>
        </div>
    @else
        <div class="empty-state" style="padding:3rem;">
            <i class="fas fa-shopping-cart" style="font-size:3rem;"></i>
            <h3>Keranjang Kosong</h3>
            <p>Belum ada produk di keranjang kamu</p>
            <a href="{{ route('products.index') }}" class="btn btn-primary">
                <i class="fas fa-shopping-bag"></i> Mulai Belanja
            </a>
        </div>
    @endif
</div>
@endsection

@push('styles')
<style>
.cart-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1.5rem;
}
.cart-header h1 {
    font-size: 1.5rem;
    font-weight: 800;
    color: var(--gray-900);
}
.cart-layout {
    display: grid;
    grid-template-columns: 1fr 350px;
    gap: 2rem;
    align-items: start;
}
.cart-items {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}
.cart-item {
    background: var(--white);
    border-radius: var(--radius-md);
    padding: 1.25rem;
    box-shadow: var(--shadow);
    border: 1px solid var(--gray-100);
    display: flex;
    align-items: center;
    gap: 1.25rem;
    transition: var(--transition);
}
.cart-item:hover {
    border-color: var(--primary-light);
}
.cart-item-image {
    width: 80px;
    height: 80px;
    border-radius: var(--radius);
    overflow: hidden;
    flex-shrink: 0;
}
.cart-item-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.cart-item-placeholder {
    width: 100%;
    height: 100%;
    background: var(--gray-100);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--gray-400);
    font-size: 1.5rem;
}
.cart-item-info {
    flex: 1;
    min-width: 0;
}
.cart-item-name {
    font-weight: 600;
    color: var(--gray-800);
    font-size: 0.95rem;
    display: block;
    margin-bottom: 0.2rem;
}
.cart-item-name:hover { color: var(--primary); }
.cart-item-category {
    font-size: 0.75rem;
    color: var(--gray-400);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.cart-item-price {
    margin-top: 0.4rem;
}
.cart-item-actions {
    display: flex;
    align-items: center;
    gap: 1.25rem;
    flex-shrink: 0;
}
.cart-item-subtotal {
    font-weight: 700;
    color: var(--gray-900);
    font-size: 1rem;
    min-width: 130px;
    text-align: right;
}
.quantity-control {
    display: flex;
    align-items: center;
    border: 2px solid var(--gray-200);
    border-radius: var(--radius);
    overflow: hidden;
}
.qty-btn {
    width: 32px;
    height: 32px;
    border: none;
    background: var(--gray-50);
    color: var(--gray-700);
    cursor: pointer;
    font-size: 0.75rem;
    transition: var(--transition);
}
.qty-btn:hover { background: var(--gray-200); }
.cart-qty-input {
    width: 40px;
    height: 32px;
    text-align: center;
    border: none;
    font-size: 0.9rem;
    font-weight: 600;
    color: var(--gray-800);
    background: var(--white);
}
.summary-card {
    background: var(--white);
    border-radius: var(--radius-md);
    padding: 1.5rem;
    box-shadow: var(--shadow);
    border: 1px solid var(--gray-100);
    position: sticky;
    top: 5rem;
}
.summary-card h3 {
    font-size: 1.1rem;
    font-weight: 700;
    color: var(--gray-800);
    margin-bottom: 1rem;
    padding-bottom: 0.75rem;
    border-bottom: 2px solid var(--gray-100);
}
.summary-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.5rem 0;
    font-size: 0.9rem;
    color: var(--gray-600);
}
.summary-total {
    border-top: 2px solid var(--gray-100);
    margin-top: 0.5rem;
    padding-top: 0.75rem;
    font-size: 1.1rem;
    font-weight: 700;
    color: var(--gray-900);
}

@media (max-width: 768px) {
    .cart-layout {
        grid-template-columns: 1fr;
    }
    .cart-item {
        flex-wrap: wrap;
    }
    .cart-item-actions {
        width: 100%;
        justify-content: space-between;
        padding-top: 0.75rem;
        border-top: 1px solid var(--gray-100);
    }
    .summary-card {
        position: static;
    }
}
</style>
@endpush

@push('scripts')
<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

function formatRupiah(num) {
    return 'Rp ' + num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
}

// Quantity minus
document.querySelectorAll('.cart-qty-minus').forEach(btn => {
    btn.addEventListener('click', function() {
        const itemId = this.dataset.itemId;
        const input = document.querySelector(`.cart-qty-input[data-item-id="${itemId}"]`);
        let val = parseInt(input.value);
        if (val > 1) {
            updateCartItem(itemId, val - 1);
        }
    });
});

// Quantity plus
document.querySelectorAll('.cart-qty-plus').forEach(btn => {
    btn.addEventListener('click', function() {
        const itemId = this.dataset.itemId;
        const max = parseInt(this.dataset.max);
        const input = document.querySelector(`.cart-qty-input[data-item-id="${itemId}"]`);
        let val = parseInt(input.value);
        if (val < max) {
            updateCartItem(itemId, val + 1);
        }
    });
});

// Remove item
document.querySelectorAll('.cart-remove-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        if (!confirm('Hapus item ini dari keranjang?')) return;
        const itemId = this.dataset.itemId;

        fetch(`/cart/${itemId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const el = document.getElementById(`cart-item-${itemId}`);
                el.style.opacity = '0';
                el.style.transform = 'translateX(-20px)';
                setTimeout(() => {
                    el.remove();
                    document.getElementById('summary-total').textContent = formatRupiah(data.total);
                    document.getElementById('summary-items').textContent = data.cart_count + ' produk';
                    const badge = document.getElementById('cart-count');
                    if (badge) badge.textContent = data.cart_count;

                    if (data.cart_count === 0) location.reload();
                }, 300);
            }
        });
    });
});

function updateCartItem(itemId, quantity) {
    fetch(`/cart/${itemId}`, {
        method: 'PATCH',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ quantity: quantity })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            const input = document.querySelector(`.cart-qty-input[data-item-id="${itemId}"]`);
            input.value = quantity;
            document.getElementById(`subtotal-${itemId}`).textContent = formatRupiah(data.subtotal);
            document.getElementById('summary-total').textContent = formatRupiah(data.total);
            document.getElementById('summary-items').textContent = data.cart_count + ' produk';
            const badge = document.getElementById('cart-count');
            if (badge) badge.textContent = data.cart_count;
        } else {
            alert(data.message);
        }
    });
}
</script>
@endpush
