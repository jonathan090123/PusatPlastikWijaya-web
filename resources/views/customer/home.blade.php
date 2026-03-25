@extends('layouts.customer')

@section('title', 'Beranda - Pusat Plastik Wijaya')

@section('content')
{{-- Hero Section --}}
<section class="hero-section">
    <div class="container">
        <div class="hero-content">
            <h1>Selamat Datang di <span>Pusat Plastik Wijaya</span></h1>
            <p>Temukan berbagai produk plastik berkualitas dengan harga terjangkau. Belanja mudah, cepat, dan terpercaya.</p>
            <a href="{{ route('products.index') }}" class="btn btn-primary btn-lg">
                <i class="fas fa-shopping-bag"></i> Belanja Sekarang
            </a>
        </div>
    </div>
</section>

{{-- Categories Section --}}
<section class="section">
        <div class="section-header">
            <h2>Kategori Produk</h2>
            <p>Temukan produk berdasarkan kategori</p>
        </div>
        <div class="categories-grid">
            @forelse($categories as $category)
                <a href="{{ route('products.index', ['category' => $category->slug]) }}" class="category-card">
                    <div class="category-img">
                        @if($category->image)
                            <img src="{{ asset('storage/' . $category->image) }}" alt="{{ $category->name }}">
                        @else
                            <div class="category-img-placeholder"><i class="fas fa-box"></i></div>
                        @endif
                    </div>
                    <div class="category-card-body">
                        <h3>{{ $category->name }}</h3>
                        <p>{{ $category->products_count ?? 0 }} Produk</p>
                    </div>
                </a>
            @empty
                <div class="empty-state" style="grid-column: 1 / -1;">
                    <i class="fas fa-tags"></i>
                    <h3>Belum ada kategori</h3>
                </div>
            @endforelse
        </div>
</section>

{{-- Promo / Diskon --}}
@if($promoProducts->isNotEmpty())
<section class="section promo-section">
    <div class="promo-heading">
        <div class="promo-heading-left">
            <span class="promo-fire">🔥</span>
            <div>
                <div class="promo-heading-title">Promo <span class="promo-heading-highlight">&amp; Diskon</span></div>
                <div class="promo-heading-sub">Penawaran terbatas! jangan sampai kehabisan!</div>
            </div>
        </div>
        <a href="{{ route('products.index', ['promo' => '1']) }}" class="btn btn-danger btn-sm promo-cta">
            Lihat Semua <i class="fas fa-arrow-right"></i>
        </a>
    </div>
    <div class="products-grid">
        @foreach($promoProducts as $product)
        <a href="{{ route('products.show', $product->slug) }}" class="product-card promo-card">
            <div class="product-card-image">
                <span class="product-badge-discount">
                    -{{ round((($product->price - $product->discount_price) / $product->price) * 100) }}%
                </span>
                @if($product->image)
                    <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}">
                @else
                    <img src="https://placehold.co/400x300/fee2e2/dc2626?text={{ urlencode($product->name) }}" alt="{{ $product->name }}">
                @endif
            </div>
            <div class="product-card-body">
                <div class="product-card-category">{{ $product->category->name }}</div>
                <h3 class="product-card-name">{{ $product->name }}</h3>
                <div class="product-card-price">
                    <span class="price-original">Rp {{ number_format($product->price, 0, ',', '.') }}</span>
                    <span class="price-discount">Rp {{ number_format($product->discount_price, 0, ',', '.') }}</span>
                </div>
                <div class="promo-saving">
                    Hemat Rp {{ number_format($product->price - $product->discount_price, 0, ',', '.') }}
                </div>
            </div>
        </a>
        @endforeach
    </div>
</section>
@endif

{{-- Latest Products --}}
<section class="section section-gray">
        <div class="section-header">
            <h2>Produk Terbaru</h2>
            <a href="{{ route('products.index') }}" class="btn btn-outline-primary btn-sm">Lihat Semua <i class="fas fa-arrow-right"></i></a>
        </div>
        <div class="products-grid">
            @forelse($latestProducts as $product)
                <a href="{{ route('products.show', $product->slug) }}" class="product-card">
                    <div class="product-card-image">
                        @if($product->hasDiscount())
                            <span class="product-badge-discount">
                                -{{ round((($product->price - $product->discount_price) / $product->price) * 100) }}%
                            </span>
                        @endif
                        @if($product->image)
                            <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}">
                        @else
                            <img src="https://placehold.co/400x300/e2e8f0/64748b?text={{ urlencode($product->name) }}" alt="{{ $product->name }}">
                        @endif
                    </div>
                    <div class="product-card-body">
                        <div class="product-card-category">{{ $product->category->name }}</div>
                        <h3 class="product-card-name">{{ $product->name }}</h3>
                        <div class="product-card-price">
                            @if($product->hasDiscount())
                                <span class="price-original">Rp {{ number_format($product->price, 0, ',', '.') }}</span>
                                <span class="price-discount">Rp {{ number_format($product->discount_price, 0, ',', '.') }}</span>
                            @else
                                <span>Rp {{ number_format($product->price, 0, ',', '.') }}</span>
                            @endif
                        </div>
                    </div>
                </a>
            @empty
                <div class="empty-state" style="grid-column: 1 / -1;">
                    <i class="fas fa-box-open"></i>
                    <h3>Belum ada produk</h3>
                    <p>Produk akan segera hadir!</p>
                </div>
            @endforelse
        </div>
</section>
@endsection

@push('styles')
<style>
.hero-section {
    background: linear-gradient(135deg, var(--secondary) 0%, #1e3a5f 50%, var(--primary-dark) 100%);
    padding: 4rem 0;
    text-align: center;
    color: var(--white);
    margin: -1.5rem -1.5rem 0 -1.5rem;
}
.hero-content h1 {
    font-size: 2.2rem;
    font-weight: 800;
    margin-bottom: 1rem;
    line-height: 1.3;
}
.hero-content h1 span { color: var(--accent); }
.hero-content p {
    font-size: 1.05rem;
    color: var(--gray-300);
    margin-bottom: 2rem;
    max-width: 550px;
    margin-left: auto;
    margin-right: auto;
}
.section { padding: 2rem 0; }

/* Promo section */
.promo-section {
    background: linear-gradient(130deg, #fff7ed 0%, #fef2f2 60%, #fdf4ff 100%);
    padding: 2rem 0;
    margin: 0 -1.5rem;
    padding-left: 1.5rem;
    padding-right: 1.5rem;
    border-top: 3px solid #fb923c;
    border-bottom: 3px solid #fb923c;
}
.promo-heading {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
    gap: 0.75rem;
}
.promo-heading-left {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}
.promo-fire {
    font-size: 2.6rem;
    line-height: 1;
    animation: fireWiggle 1.4s ease-in-out infinite;
    filter: drop-shadow(0 2px 6px rgba(251,146,60,0.5));
}
@keyframes fireWiggle {
    0%,100% { transform: rotate(-6deg) scale(1); }
    50%      { transform: rotate(6deg) scale(1.12); }
}
.promo-heading-title {
    font-size: 1.6rem;
    font-weight: 900;
    color: #9a3412;
    line-height: 1.1;
    letter-spacing: -0.5px;
}
.promo-heading-highlight {
    background: linear-gradient(90deg, #ea580c, #dc2626);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}
.promo-heading-sub {
    font-size: 0.8rem;
    color: #c2410c;
    margin-top: 0.2rem;
    font-weight: 500;
}
.promo-cta {
    background: linear-gradient(90deg, #ea580c, #dc2626);
    border: none;
    font-weight: 700;
    letter-spacing: 0.3px;
    box-shadow: 0 4px 14px rgba(220,38,38,0.35);
    transition: transform 0.15s, box-shadow 0.15s;
}
.promo-cta:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 18px rgba(220,38,38,0.45);
}
.promo-title-icon { font-size: 1.25rem; margin-right: 0.25rem; }
.promo-card .product-card-image {
    border: 2px solid #fecaca;
}
.promo-saving {
    display: inline-block;
    margin-top: 0.4rem;
    background: #fee2e2;
    color: #b91c1c;
    font-size: 0.72rem;
    font-weight: 700;
    padding: 0.2rem 0.6rem;
    border-radius: 999px;
}

.section-gray {
    background: var(--gray-100);
    padding: 2rem 0;
    margin: 0 -1.5rem;
    padding-left: 1.5rem;
    padding-right: 1.5rem;
}
.section-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1.5rem;
}
.section-header h2 { font-size: 1.35rem; font-weight: 700; color: var(--gray-900); }
.section-header p { color: var(--gray-500); font-size: 0.875rem; }

.categories-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
    gap: 1rem;
}
.category-card {
    background: var(--white);
    border-radius: var(--radius-md);
    overflow: hidden;
    box-shadow: var(--shadow);
    border: 1px solid var(--gray-100);
    transition: var(--transition);
    color: var(--gray-700);
}
.category-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-lg);
    color: var(--primary);
    border-color: var(--primary-light);
}
.category-img {
    width: 100%;
    aspect-ratio: 4 / 3;
    overflow: hidden;
}
.category-img img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    padding: 0.5rem;
    background: var(--gray-50);
}
.category-img-placeholder {
    width: 100%;
    height: 100%;
    background: var(--primary-light);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    color: var(--primary);
}
.category-card-body {
    padding: 0.6rem 0.75rem 0.75rem;
    text-align: center;
    border-top: 1px solid var(--gray-100);
}
.category-card h3 { font-size: 0.875rem; font-weight: 600; margin-bottom: 0.2rem; }
.category-card p { font-size: 0.75rem; color: var(--gray-500); margin: 0; }

@media (max-width: 768px) {
    .hero-content h1 { font-size: 1.6rem; }
    .section-header { flex-direction: column; align-items: flex-start; gap: 0.5rem; }
    .categories-grid { grid-template-columns: repeat(3, 1fr); gap: 0.75rem; }
    .hero-section { padding: 2.5rem 0; }
    .promo-heading-title { font-size: 1.3rem; }
}
@media (max-width: 480px) {
    .hero-content h1 { font-size: 1.3rem; }
    .hero-content p { font-size: 0.9rem; margin-bottom: 1.25rem; }
    .hero-section { padding: 1.75rem 0; }
    .categories-grid { grid-template-columns: repeat(2, 1fr); gap: 0.5rem; }
    .category-card-body { padding: 0.4rem 0.5rem 0.5rem; }
    .category-card h3 { font-size: 0.78rem; }
    .promo-heading-title { font-size: 1.15rem; }
    .promo-fire { font-size: 2rem; }
    .promo-saving { font-size: 0.68rem; }
    .section { padding: 1.25rem 0; }
}
</style>
@endpush