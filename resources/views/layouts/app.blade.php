<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Pusat Plastik Wijaya')</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body>
    {{-- Navbar --}}
    <nav class="navbar">
        <div class="container navbar-content">
            <a href="{{ route('home') }}" class="navbar-brand">
                <i class="fas fa-store"></i> Pusat Plastik Wijaya
            </a>

            @unless(request()->routeIs('login') || request()->routeIs('register'))
            <div class="navbar-search">
                <form action="{{ route('products.index') }}" method="GET">
                    <div class="search-input-group">
                        <i class="fas fa-search"></i>
                        <input type="text" name="search" placeholder="Cari produk..." value="{{ request('search') }}">
                    </div>
                </form>
            </div>
            @endunless

            <div class="navbar-actions">
                @auth
                    @unless(request()->routeIs('login') || request()->routeIs('register'))
                    <a href="{{ route('cart.index') }}" class="nav-icon-link">
                        <i class="fas fa-shopping-cart"></i>
                        <span class="badge" id="cart-count">0</span>
                    </a>
                    <div class="nav-dropdown">
                        <button class="nav-dropdown-toggle">
                            <i class="fas fa-user-circle"></i>
                            <span>{{ Auth::user()->name }}</span>
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="nav-dropdown-menu">
                            <a href="{{ route('profile.edit') }}"><i class="fas fa-user"></i> Profil Saya</a>
                            <a href="{{ route('orders.index') }}"><i class="fas fa-box"></i> Pesanan Saya</a>
                            <div class="dropdown-divider"></div>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit"><i class="fas fa-sign-out-alt"></i> Logout</button>
                            </form>
                        </div>
                    </div>
                    @endunless
                @else
                    @if(request()->routeIs('login'))
                        <a href="{{ route('register') }}" class="btn btn-light btn-sm">Daftar</a>
                    @elseif(request()->routeIs('register'))
                        <a href="{{ route('login') }}" class="btn btn-outline-light btn-sm">Masuk</a>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-outline-light btn-sm">Masuk</a>
                        <a href="{{ route('register') }}" class="btn btn-light btn-sm">Daftar</a>
                    @endif
                @endauth
            </div>

            @unless(request()->routeIs('login') || request()->routeIs('register'))
            <button class="navbar-toggler" id="navbarToggler">
                <i class="fas fa-bars"></i>
            </button>
            @endunless
        </div>
    </nav>

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
        </div>
    @endif

    {{-- Main Content --}}
    <main class="main-content">
        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-col">
                    <h3><i class="fas fa-store"></i> Pusat Plastik Wijaya</h3>
                    <p>Menyediakan berbagai macam produk plastik berkualitas dengan harga terjangkau.</p>
                </div>
                <div class="footer-col">
                    <h4>Menu</h4>
                    <ul>
                        <li><a href="{{ route('home') }}">Beranda</a></li>
                        <li><a href="{{ route('products.index') }}">Produk</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h4>Kontak</h4>
                    <ul>
                        <li><i class="fas fa-phone"></i> +62 xxx-xxxx-xxxx</li>
                        <li><i class="fas fa-envelope"></i> info@plastikwijaya.com</li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; {{ date('Y') }} Pusat Plastik Wijaya. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        // Dropdown toggle
        document.querySelectorAll('.nav-dropdown-toggle').forEach(btn => {
            btn.addEventListener('click', function() {
                this.parentElement.classList.toggle('active');
            });
        });

        // Close dropdown on outside click
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.nav-dropdown')) {
                document.querySelectorAll('.nav-dropdown').forEach(d => d.classList.remove('active'));
            }
        });

        // Mobile toggle
        document.getElementById('navbarToggler')?.addEventListener('click', function() {
            document.querySelector('.navbar-actions').classList.toggle('show');
            document.querySelector('.navbar-search').classList.toggle('show');
        });

        // Auto-hide flash messages
        document.querySelectorAll('.alert').forEach(alert => {
            setTimeout(() => {
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-10px)';
                setTimeout(() => alert.remove(), 300);
            }, 4000);
        });
    </script>
    @stack('scripts')
</body>
</html>
