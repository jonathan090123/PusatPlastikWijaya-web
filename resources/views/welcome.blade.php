@extends('layouts.customer')

@section('title', 'Pusat Plastik Wijaya - Toko Plastik Online Terpercaya')

@section('content')
{{-- Hero Section --}}
<section class="lp-hero">
    <div class="container lp-hero-inner">
        <h1>Selamat Datang di <span>Pusat Plastik Wijaya</span></h1>
        <p>Menyediakan berbagai produk plastik berkualitas dengan harga terjangkau. Belanja mudah, cepat, dan terpercaya.</p>
        <div class="lp-hero-actions">
            <a href="{{ route('register') }}" class="btn btn-primary btn-lg lp-btn-main">
                <i class="fas fa-user-plus"></i> Daftar Sekarang
            </a>
            <a href="{{ route('login') }}" class="btn btn-outline-light btn-lg">
                <i class="fas fa-sign-in-alt"></i> Masuk
            </a>
        </div>
    </div>
</section>

{{-- Keunggulan Section --}}
<section class="lp-features">
    <div class="container">
        <h2 class="lp-section-title">Mengapa Belanja di Sini?</h2>
        <div class="lp-features-grid">
            <div class="lp-feature-card">
                <div class="lp-feature-icon"><i class="fas fa-medal"></i></div>
                <h3>Kualitas Terjamin</h3>
                <p>Produk plastik yang telah melalui seleksi ketat dengan standar kualitas tinggi.</p>
            </div>
            <div class="lp-feature-card">
                <div class="lp-feature-icon"><i class="fas fa-tags"></i></div>
                <h3>Harga Terjangkau</h3>
                <p>Harga kompetitif langsung dari distributor tanpa perantara.</p>
            </div>
            <div class="lp-feature-card">
                <div class="lp-feature-icon"><i class="fas fa-truck-fast"></i></div>
                <h3>Pengiriman Cepat</h3>
                <p>Pesanan diproses dengan cepat dan dikirim ke seluruh Indonesia.</p>
            </div>
        </div>
    </div>
</section>


@endsection

@push('styles')
<style>
/* Hero */
.lp-hero {
    background: linear-gradient(135deg, var(--secondary) 0%, #1e3a5f 50%, var(--primary-dark) 100%);
    padding: 5rem 0 4rem;
    color: #fff;
    text-align: center;
}
.lp-hero-inner { max-width: 620px; margin: 0 auto; }
.lp-hero h1 {
    font-size: 2.2rem;
    font-weight: 800;
    line-height: 1.3;
    margin-bottom: 1rem;
}
.lp-hero h1 span { color: var(--accent); }
.lp-hero p {
    font-size: 1.05rem;
    color: rgba(255,255,255,0.8);
    margin-bottom: 2rem;
    line-height: 1.7;
}
.lp-hero-actions { display: flex; gap: 0.75rem; justify-content: center; flex-wrap: wrap; }
.lp-btn-main {
    background: linear-gradient(135deg, #ffd60a 0%, #f5a623 100%);
    border: none;
    color: #1a1a1a !important;
    font-weight: 700;
}
.lp-btn-main:hover { opacity: 0.9; }

/* Features */
.lp-features { padding: 3.5rem 0; background: #f8fafc; }
.lp-section-title {
    text-align: center;
    font-size: 1.4rem;
    font-weight: 700;
    color: var(--gray-900);
    margin-bottom: 2rem;
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
    box-shadow: 0 1px 8px rgba(0,0,0,0.06);
    border: 1px solid #e8eff5;
    transition: var(--transition);
}
.lp-feature-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-lg);
}
.lp-feature-icon {
    width: 50px; height: 50px;
    border-radius: 12px;
    background: var(--primary-light);
    color: var(--primary);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.3rem;
    margin: 0 auto 1rem;
}
.lp-feature-card h3 { font-size: 1rem; font-weight: 700; color: var(--gray-900); margin-bottom: 0.4rem; }
.lp-feature-card p { font-size: 0.85rem; color: var(--gray-500); line-height: 1.6; }

/* CTA */
.lp-cta {
    padding: 3.5rem 0;
    text-align: center;
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
    color: #fff;
}
.lp-cta h2 { font-size: 1.5rem; font-weight: 800; margin-bottom: 0.5rem; }
.lp-cta p { color: rgba(255,255,255,0.8); margin-bottom: 1.5rem; font-size: 1rem; }

@media (max-width: 768px) {
    .lp-hero h1 { font-size: 1.6rem; }
    .lp-hero { padding: 3rem 0 2.5rem; }
}
</style>
@endpush
