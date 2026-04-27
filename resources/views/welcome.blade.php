@extends('layouts.customer')

@section('title', 'Pusat Plastik Wijaya - Toko Plastik Online Terpercaya')

@section('content')
    {{-- Hero Section --}}
    <section class="lp-hero">
        <div class="container lp-hero-inner">
            <h1>Selamat Datang di <span>asdd Plastik Wijaya</span></h1>
            <p>Menyediakan berbagai produk plastik berkualitas dengan harga terjangkau. Belanja mudah, cepat, dan
                terpercaya.</p>

            <div class="lp-hero-actions">
                <a href="{{ route('products.index') }}" class="btn btn-primary btn-lg lp-btn-main">
                    <i class="fas fa-box-open"></i> Lihat Semua Produk
                </a>
                @guest
                    <a href="{{ route('login') }}" class="btn btn-outline-light btn-lg">
                        <i class="fas fa-sign-in-alt"></i> Masuk
                    </a>
                    <a href="{{ route('register') }}" class="btn btn-outline-light btn-lg">
                        <i class="fas fa-user-plus"></i> Daftar Gratis
                    </a>
                @endguest
            </div>
        </div>
    </section>


    {{-- Kategori --}}
    @if($categories->count() > 0)
        <section class="lp-categories">
            <div class="container">
                <h2 class="lp-section-title">Kategori Produk</h2>
                <div class="lp-cat-grid">
                    @foreach($categories as $cat)
                        <a href="{{ route('products.index', ['category' => $cat->slug]) }}" class="lp-cat-item">
                            <i class="fas fa-tag"></i>
                            <span>{{ $cat->name }}</span>
                            <small>{{ $cat->products_count }} produk</small>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- Promo Products --}}
    @if($promoProducts->count() > 0)
        <section class="lp-products-section">
            <div class="container">
                <div class="lp-products-header">
                    <h2 class="lp-section-title" style="margin-bottom:0;"><i class="fas fa-fire" style="color:#f97316;"></i>
                        Produk Promo</h2>
                    <a href="{{ route('products.index') }}" class="lp-see-all">Lihat Semua <i
                            class="fas fa-arrow-right"></i></a>
                </div>
                <div class="lp-products-grid">
                    @foreach($promoProducts as $product)
                        <a href="{{ route('products.show', $product->slug) }}" class="lp-product-card">
                            <div class="lp-product-img">
                                @if($product->hasDiscount())
                                    <span
                                        class="lp-badge-discount">-{{ round((($product->price - $product->discount_price) / $product->price) * 100) }}%</span>
                                @endif
                                @if($product->image)
                                    <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}">
                                @else
                                    <img src="https://placehold.co/300x240/e2e8f0/64748b?text={{ urlencode($product->name) }}"
                                        alt="{{ $product->name }}">
                                @endif
                            </div>
                            <div class="lp-product-body">
                                <div class="lp-product-cat">{{ $product->category->name }}</div>
                                <h3 class="lp-product-name">{{ $product->name }}</h3>
                                <div class="lp-product-price">
                                    <span class="lp-price-original">Rp {{ number_format($product->price, 0, ',', '.') }}</span>
                                    <span class="lp-price-discount">Rp
                                        {{ number_format($product->discount_price, 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- Featured Products --}}
    @if($featuredProducts->count() > 0)
        <section class="lp-products-section">
            <div class="container">
                <div class="lp-products-header">
                    <h2 class="lp-section-title" style="margin-bottom:0;"><i class="fas fa-star" style="color:#f59e0b;"></i>
                        Produk Terbaru</h2>
                    <a href="{{ route('products.index') }}" class="lp-see-all">Lihat Semua <i
                            class="fas fa-arrow-right"></i></a>
                </div>
                <div class="lp-products-grid">
                    @foreach($featuredProducts as $product)
                        <a href="{{ route('products.show', $product->slug) }}" class="lp-product-card">
                            <div class="lp-product-img">
                                @if($product->hasDiscount())
                                    <span
                                        class="lp-badge-discount">-{{ round((($product->price - $product->discount_price) / $product->price) * 100) }}%</span>
                                @endif
                                @if($product->image)
                                    <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}">
                                @else
                                    <img src="https://placehold.co/300x240/e2e8f0/64748b?text={{ urlencode($product->name) }}"
                                        alt="{{ $product->name }}">
                                @endif
                            </div>
                            <div class="lp-product-body">
                                <div class="lp-product-cat">{{ $product->category->name }}</div>
                                <h3 class="lp-product-name">{{ $product->name }}</h3>
                                <div class="lp-product-price">
                                    @if($product->hasDiscount())
                                        <span class="lp-price-original">Rp {{ number_format($product->price, 0, ',', '.') }}</span>
                                        <span class="lp-price-discount">Rp
                                            {{ number_format($product->discount_price, 0, ',', '.') }}</span>
                                    @else
                                        <span class="lp-price-normal">Rp {{ number_format($product->price, 0, ',', '.') }}</span>
                                    @endif
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

@endsection

@push('styles')
    <style>
        /* Hero */
        .lp-hero {
            background-image: linear-gradient(rgba(15, 23, 42, 0.7), rgba(15, 23, 42, 0.8)), url('{{ asset('storage/banner.jpeg') }}');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            padding: 4.5rem 0 5.5rem;
            color: #fff;
            text-align: center;
        }

        .lp-hero-inner {
            max-width: 680px;
            margin: 0 auto;
        }

        .lp-hero h1 {
            font-size: 2.2rem;
            font-weight: 800;
            line-height: 1.3;
            margin-bottom: 0.6rem;
        }

        .lp-hero h1 span {
            color: var(--accent);
        }

        .lp-hero p {
            font-size: 1.05rem;
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 1rem;
            line-height: 1.7;
        }

        .lp-hero-actions {
            display: flex;
            gap: 0.75rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .lp-btn-main {
            background: linear-gradient(135deg, #ffd60a 0%, #f5a623 100%);
            border: none;
            color: #1a1a1a !important;
            font-weight: 700;
        }

        .lp-btn-main:hover {
            opacity: 0.9;
        }

        /* Features */
        .lp-features {
            padding: 3rem 0;
            background: #f8fafc;
        }

        .lp-section-title {
            text-align: center;
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--gray-900);
            margin-bottom: 1.75rem;
        }

        .lp-features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1.25rem;
        }

        .lp-feature-card {
            background: #fff;
            border-radius: 12px;
            padding: 1.75rem 1.25rem;
            text-align: center;
            box-shadow: 0 1px 8px rgba(0, 0, 0, 0.06);
            border: 1px solid #e8eff5;
            transition: var(--transition);
        }

        .lp-feature-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
        }

        .lp-feature-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            background: var(--primary-light);
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            margin: 0 auto 1rem;
        }

        .lp-feature-card h3 {
            font-size: 1rem;
            font-weight: 700;
            color: var(--gray-900);
            margin-bottom: 0.4rem;
        }

        .lp-feature-card p {
            font-size: 0.85rem;
            color: var(--gray-500);
            line-height: 1.6;
        }

        /* Categories */
        .lp-categories {
            padding: 2.5rem 0;
            background: #fff;
        }

        .lp-cat-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            justify-content: center;
        }

        .lp-cat-item {
            display: inline-flex;
            flex-direction: column;
            align-items: center;
            gap: 0.25rem;
            background: #f0f9ff;
            border: 1.5px solid #bae6fd;
            border-radius: 12px;
            padding: 0.75rem 1.25rem;
            text-decoration: none;
            color: var(--primary-dark);
            font-weight: 600;
            font-size: 0.875rem;
            transition: var(--transition);
            min-width: 100px;
            text-align: center;
        }

        .lp-cat-item:hover {
            background: var(--primary);
            color: #fff;
            border-color: var(--primary);
            transform: translateY(-2px);
        }

        .lp-cat-item i {
            font-size: 1rem;
            margin-bottom: 0.15rem;
        }

        .lp-cat-item small {
            font-size: 0.72rem;
            opacity: 0.7;
            font-weight: 400;
        }

        /* Products sections */
        .lp-products-section {
            padding: 2.5rem 0;
        }

        .lp-products-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .lp-see-all {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--primary);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.35rem;
            white-space: nowrap;
        }

        .lp-see-all:hover {
            color: var(--primary-dark);
        }

        .lp-products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 1rem;
        }

        .lp-product-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 1px 6px rgba(0, 0, 0, 0.07);
            border: 1px solid var(--gray-100);
            overflow: hidden;
            text-decoration: none;
            color: inherit;
            transition: var(--transition);
            display: block;
        }

        .lp-product-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
        }

        .lp-product-img {
            position: relative;
            aspect-ratio: 4/3;
            background: var(--gray-100);
            overflow: hidden;
        }

        .lp-product-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .lp-badge-discount {
            position: absolute;
            top: 8px;
            left: 8px;
            background: #ef4444;
            color: #fff;
            font-size: 0.72rem;
            font-weight: 700;
            padding: 0.2rem 0.5rem;
            border-radius: 999px;
        }

        .lp-product-body {
            padding: 0.875rem;
        }

        .lp-product-cat {
            font-size: 0.72rem;
            color: var(--primary);
            font-weight: 600;
            text-transform: uppercase;
            margin-bottom: 0.25rem;
        }

        .lp-product-name {
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--gray-800);
            margin: 0 0 0.5rem;
            line-height: 1.4;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .lp-product-price {
            display: flex;
            flex-direction: column;
            gap: 0.1rem;
        }

        .lp-price-original {
            text-decoration: line-through;
            color: var(--gray-400);
            font-size: 0.78rem;
        }

        .lp-price-discount {
            color: #ef4444;
            font-weight: 700;
            font-size: 1rem;
        }

        .lp-price-normal {
            color: var(--gray-800);
            font-weight: 700;
            font-size: 1rem;
        }

        /* CTA */
        .lp-cta {
            padding: 3.5rem 0;
            text-align: center;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: #fff;
        }

        .lp-cta h2 {
            font-size: 1.5rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
        }

        .lp-cta p {
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 1.5rem;
            font-size: 1rem;
        }

        @media (max-width: 768px) {
            .lp-hero h1 {
                font-size: 1.6rem;
            }

            .lp-hero {
                padding: 1.5rem 0 1.25rem;
            }

            .lp-products-grid {
                grid-template-columns: repeat(auto-fill, minmax(155px, 1fr));
            }
        }
    </style>
@endpush