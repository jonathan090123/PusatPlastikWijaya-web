@extends('layouts.customer')

@section('title', 'Katalog Produk - Pusat Plastik Wijaya')

@section('content')
<div style="padding: 0.5rem;">
    {{-- Page Header --}}
    <div class="catalog-header">
        <h1><i class="fas fa-box-open"></i> Katalog Produk</h1>
        <p>Temukan produk plastik berkualitas dengan harga terbaik</p>
    </div>

    {{-- Mobile filter toggle --}}
    <button class="filter-toggle-btn" id="filterToggleBtn" aria-expanded="false">
        <i class="fas fa-filter"></i> Filter & Urutkan
        @if(request('category') || request('sort') && request('sort') !== 'terbaru')
            <span class="filter-active-dot"></span>
        @endif
        <i class="fas fa-chevron-down filter-toggle-icon" id="filterToggleIcon"></i>
    </button>

    <div class="catalog-layout">
        {{-- Sidebar Filters --}}
        <aside class="catalog-sidebar" id="catalogSidebar">
            <div class="filter-card">
                <h3><i class="fas fa-filter"></i> Filter</h3>

                <form action="{{ route('products.index') }}" method="GET" id="filterForm">
                    @if(request('search'))
                        <input type="hidden" name="search" value="{{ request('search') }}">
                    @endif

                    {{-- Category Filter --}}
                    <div class="filter-group">
                        <h4>Kategori</h4>
                        <a href="{{ route('products.index', request()->only('search', 'sort')) }}"
                           class="filter-item {{ !request('category') ? 'active' : '' }}">
                            <i class="fas fa-th-large"></i> Semua Kategori
                        </a>
                        @foreach($categories as $cat)
                            <a href="{{ route('products.index', array_merge(request()->only('search', 'sort'), ['category' => $cat->slug])) }}"
                               class="filter-item {{ request('category') == $cat->slug ? 'active' : '' }}">
                                <i class="fas fa-tag"></i> {{ $cat->name }}
                                <span class="filter-count">{{ $cat->products_count }}</span>
                            </a>
                        @endforeach
                    </div>

                    {{-- Sort --}}
                    <div class="filter-group">
                        <h4>Urutkan</h4>
                        <select name="sort" onchange="this.form.submit()">
                            <option value="terbaru" {{ request('sort') == 'terbaru' ? 'selected' : '' }}>Terbaru</option>
                            <option value="harga-rendah" {{ request('sort') == 'harga-rendah' ? 'selected' : '' }}>Harga: Rendah → Tinggi</option>
                            <option value="harga-tinggi" {{ request('sort') == 'harga-tinggi' ? 'selected' : '' }}>Harga: Tinggi → Rendah</option>
                            <option value="nama" {{ request('sort') == 'nama' ? 'selected' : '' }}>Nama A-Z</option>
                        </select>
                    </div>
                </form>
            </div>
        </aside>

        {{-- Product Grid --}}
        <div class="catalog-content">
            {{-- Active Filters Info --}}
            <div class="catalog-info">
                <span>Menampilkan <strong>{{ $products->total() }}</strong> produk</span>
                @if(request('search'))
                    <span class="active-filter">
                        "{{ request('search') }}"
                        <a href="{{ route('products.index', request()->except('search')) }}"><i class="fas fa-times"></i></a>
                    </span>
                @endif
            </div>

            <div class="products-grid">
                @forelse($products as $product)
                    <a href="{{ route('products.show', $product->slug) }}" class="product-card" id="product-{{ $product->id }}">
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
                            @if($product->stock <= 0)
                                <span class="product-badge-stock">Habis</span>
                            @endif
                        </div>
                    </a>
                @empty
                    <div class="empty-state" style="grid-column: 1 / -1;">
                        <i class="fas fa-search"></i>
                        <h3>Produk tidak ditemukan</h3>
                        <p>Coba ubah kata kunci atau filter pencarian</p>
                        <a href="{{ route('products.index') }}" class="btn btn-primary btn-sm">Lihat Semua Produk</a>
                    </div>
                @endforelse
            </div>

            @if($products->hasPages())
                <div class="catalog-pagination">
                    {{ $products->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.catalog-header {
    text-align: center;
    margin-bottom: 2rem;
}
.catalog-header h1 {
    font-size: 1.75rem;
    font-weight: 800;
    color: var(--gray-900);
    margin-bottom: 0.5rem;
}
.catalog-header p {
    color: var(--gray-500);
    font-size: 0.95rem;
}
.catalog-layout {
    display: grid;
    grid-template-columns: 260px 1fr;
    gap: 2rem;
    align-items: start;
}
.filter-card {
    background: var(--white);
    border-radius: var(--radius-md);
    padding: 1.25rem;
    box-shadow: var(--shadow);
    border: 1px solid var(--gray-100);
    position: sticky;
    top: 5rem;
}
.filter-card h3 {
    font-size: 1rem;
    font-weight: 700;
    color: var(--gray-800);
    margin-bottom: 1rem;
    padding-bottom: 0.75rem;
    border-bottom: 2px solid var(--gray-100);
}
.filter-group { margin-bottom: 1.25rem; }
.filter-group h4 {
    font-size: 0.8rem;
    font-weight: 600;
    color: var(--gray-500);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 0.5rem;
}
.filter-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 0.75rem;
    border-radius: var(--radius-sm);
    color: var(--gray-600);
    font-size: 0.85rem;
    transition: var(--transition);
}
.filter-item:hover {
    background: var(--primary-light);
    color: var(--primary);
}
.filter-item.active {
    background: var(--primary);
    color: var(--white);
    font-weight: 600;
}
.filter-count {
    margin-left: auto;
    font-size: 0.75rem;
    background: rgba(0,0,0,0.08);
    padding: 0.1rem 0.5rem;
    border-radius: 999px;
}
.filter-item.active .filter-count {
    background: rgba(255,255,255,0.25);
}
.filter-group select {
    width: 100%;
    padding: 0.5rem 0.75rem;
    border-radius: var(--radius-sm);
    border: 1px solid var(--gray-200);
    font-size: 0.85rem;
    color: var(--gray-700);
    background: var(--white);
}
.catalog-info {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 1rem;
    font-size: 0.875rem;
    color: var(--gray-500);
}
.active-filter {
    background: var(--primary-light);
    color: var(--primary);
    padding: 0.25rem 0.75rem;
    border-radius: 999px;
    font-size: 0.8rem;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
}
.active-filter a { color: var(--primary); }
.products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
    gap: 0.75rem;
}
.product-card {
    background: var(--white);
    border-radius: var(--radius-md);
    overflow: hidden;
    box-shadow: var(--shadow);
    border: 1px solid var(--gray-100);
    transition: var(--transition);
    color: var(--gray-700);
    display: flex;
    flex-direction: column;
}
.product-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-lg);
    border-color: var(--primary-light);
}
.product-card-image {
    position: relative;
    aspect-ratio: 4/3;
    overflow: hidden;
    background: var(--gray-100);
}
.product-card-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}
.product-card:hover .product-card-image img {
    transform: scale(1.05);
}
.product-badge-discount {
    position: absolute;
    top: 0.35rem;
    left: 0.35rem;
    background: var(--danger);
    color: var(--white);
    padding: 0.1rem 0.4rem;
    border-radius: var(--radius-sm);
    font-size: 0.65rem;
    font-weight: 700;
    z-index: 1;
}
.product-badge-stock {
    display: inline-block;
    background: var(--gray-200);
    color: var(--gray-500);
    padding: 0.15rem 0.5rem;
    border-radius: var(--radius-sm);
    font-size: 0.7rem;
    font-weight: 600;
    margin-top: 0.4rem;
}
.product-card-body {
    padding: 0.55rem 0.65rem;
    flex: 1;
    display: flex;
    flex-direction: column;
}
.product-card-category {
    font-size: 0.62rem;
    color: var(--primary);
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 0.2rem;
}
.product-card-name {
    font-size: 0.78rem;
    font-weight: 600;
    color: var(--gray-800);
    margin-bottom: 0.35rem;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    line-clamp: 2;
    line-height: 1.35;
}
.product-card-price {
    margin-top: auto;
    font-weight: 700;
    font-size: 0.82rem;
    color: var(--gray-900);
}
.price-original {
    text-decoration: line-through;
    color: var(--gray-400);
    font-size: 0.68rem;
    font-weight: 400;
    display: block;
}
.price-discount {
    color: var(--danger);
    font-size: 0.82rem;
}
.catalog-pagination {
    margin-top: 2rem;
    display: flex;
    justify-content: center;
}

/* Mobile filter toggle button — hidden on desktop */
.filter-toggle-btn {
    display: none;
    width: 100%;
    padding: 0.65rem 1rem;
    background: var(--white);
    border: 1.5px solid var(--gray-200);
    border-radius: var(--radius);
    font-size: 0.88rem;
    font-weight: 600;
    color: var(--gray-700);
    cursor: pointer;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.75rem;
    box-shadow: var(--shadow-sm);
}
.filter-active-dot {
    width: 8px; height: 8px;
    background: var(--primary);
    border-radius: 50%;
    display: inline-block;
}
.filter-toggle-icon {
    margin-left: auto;
    transition: transform 0.25s;
}
.filter-toggle-btn[aria-expanded="true"] .filter-toggle-icon {
    transform: rotate(180deg);
}

@media (max-width: 768px) {
    .filter-toggle-btn { display: inline-flex; }
    .catalog-layout {
        grid-template-columns: 1fr;
    }
    .catalog-sidebar {
        order: -1;
        overflow: hidden;
        max-height: 0;
        transition: max-height 0.3s ease;
    }
    .catalog-sidebar.open {
        max-height: 1000px;
    }
    .filter-card {
        position: static;
        margin-bottom: 0.5rem;
    }
    .products-grid {
        grid-template-columns: repeat(3, 1fr);
        gap: 0.5rem;
    }
}
@media (max-width: 480px) {
    .products-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 0.4rem;
    }
    .catalog-header h1 { font-size: 1.3rem; }
    .catalog-header p { font-size: 0.82rem; }
}
</style>
@endpush

@push('scripts')
<script>
(function () {
    var btn     = document.getElementById('filterToggleBtn');
    var sidebar = document.getElementById('catalogSidebar');
    if (!btn || !sidebar) return;

    // On load: open sidebar if screen >= 769px
    function checkOpen() {
        if (window.innerWidth >= 769) {
            sidebar.classList.add('open');
            btn.setAttribute('aria-expanded', 'true');
        } else {
            // Keep closed unless filter is active
            var hasActive = btn.querySelector('.filter-active-dot');
            if (hasActive) {
                sidebar.classList.add('open');
                btn.setAttribute('aria-expanded', 'true');
            }
        }
    }
    checkOpen();

    btn.addEventListener('click', function () {
        var isOpen = sidebar.classList.toggle('open');
        btn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    });
})();
</script>
@endpush
