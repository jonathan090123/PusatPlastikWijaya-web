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

@auth
@unless(request()->routeIs('login') || request()->routeIs('register'))
{{-- === LOGGED-IN CUSTOMER: Sidebar Layout (same structure as admin) === --}}
<body class="admin-body">
    <div class="admin-wrapper">
        {{-- Sidebar --}}
        <aside class="admin-sidebar" id="customerSidebar">
            <div class="sidebar-header">
                <h2><i class="fas fa-store"></i> <span>Plastik Wijaya</span></h2>
                <p class="sidebar-greeting">
                    <span>
                        <span id="customer-greeting">Selamat...</span>, {{ Auth::user()->name }} 👋
                        <script>
                            (function() {
                                const hour = new Date().getHours();
                                let greeting = 'Selamat Malam';
                                if (hour >= 1 && hour < 10) greeting = 'Selamat Pagi';
                                else if (hour >= 10 && hour < 15) greeting = 'Selamat Siang';
                                else if (hour >= 15 && hour < 18) greeting = 'Selamat Sore';
                                document.getElementById('customer-greeting').textContent = greeting;
                            })();
                        </script>
                    </span>
                </p>
            </div>
            <nav class="sidebar-nav">
                <a href="{{ route('home') }}" class="sidebar-link {{ request()->routeIs('home') ? 'active' : '' }}">
                    <i class="fas fa-home"></i> <span>Beranda</span>
                </a>
                <a href="{{ route('products.index') }}" class="sidebar-link {{ request()->routeIs('products.*') ? 'active' : '' }}">
                    <i class="fas fa-box-open"></i> <span>Katalog Produk</span>
                </a>
                <a href="{{ route('cart.index') }}" class="sidebar-link {{ request()->routeIs('cart.*') ? 'active' : '' }}">
                    <i class="fas fa-shopping-cart"></i> <span>Keranjang</span>
                </a>
                <a href="{{ route('orders.index') }}" class="sidebar-link {{ request()->routeIs('orders.*') ? 'active' : '' }}">
                    <i class="fas fa-clipboard-list"></i> <span>Pesanan Saya</span>
                </a>
            </nav>
            <div style="margin-top:auto; padding:1rem;">
                <a href="{{ route('profile.edit') }}" class="sidebar-link {{ request()->routeIs('profile.*') ? 'active' : '' }}" style="margin-bottom:0.5rem; background:rgba(96,165,250,0.15); border:1px solid rgba(96,165,250,0.25); border-radius:var(--radius-sm); font-weight:600; color:#93c5fd;">
                    <i class="fas fa-user-cog"></i> <span>Profil Saya</span>
                </a>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="sidebar-link" style="width:100%; color:#fecaca; border:1px solid rgba(252,165,165,0.25); border-radius:var(--radius-sm); background:rgba(185,28,28,0.6); justify-content:flex-start; font-weight:600; cursor:pointer;">
                        <i class="fas fa-sign-out-alt"></i> <span>Logout</span>
                    </button>
                </form>
            </div>
        </aside>

        {{-- Main Content --}}
        <div class="admin-main">
            {{-- Top Bar --}}
            <header class="admin-topbar">
                <button class="sidebar-toggle" id="customerSidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="topbar-search">
                    <form action="{{ route('products.index') }}" method="GET">
                        <div class="search-input-group">
                            <i class="fas fa-search"></i>
                            <input type="text" name="search" placeholder="Cari produk..." value="{{ request('search') }}">
                        </div>
                    </form>
                </div>
                <div class="topbar-right">
                    <a href="{{ route('cart.index') }}" style="position:relative; color:var(--gray-300); font-size:1.1rem;">
                        <i class="fas fa-shopping-cart"></i>
                        <span class="badge" id="cart-count" style="position:absolute; top:-6px; right:-10px; background:var(--danger); color:#fff; font-size:0.65rem; padding:0.1rem 0.4rem; border-radius:999px;">0</span>
                    </a>
                    <div class="nav-dropdown">
                        <button class="nav-dropdown-toggle">
                            <i class="fas fa-user-circle"></i>
                            <span>{{ Auth::user()->name }}</span>
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="nav-dropdown-menu">
                            <a href="{{ route('profile.edit') }}"><i class="fas fa-user"></i> Profil Saya</a>
                            <div class="dropdown-divider"></div>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit"><i class="fas fa-sign-out-alt"></i> Logout</button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            {{-- Page Content --}}
            <div class="admin-content">
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

                @yield('content')
            </div>
        </div>
    </div>

    <script>
        // Sidebar toggle
        document.getElementById('customerSidebarToggle')?.addEventListener('click', function() {
            document.querySelector('.admin-wrapper').classList.toggle('sidebar-collapsed');
        });

        // Dropdown toggle
        document.querySelectorAll('.nav-dropdown-toggle').forEach(btn => {
            btn.addEventListener('click', function() {
                this.parentElement.classList.toggle('active');
            });
        });

        document.addEventListener('click', function(e) {
            if (!e.target.closest('.nav-dropdown')) {
                document.querySelectorAll('.nav-dropdown').forEach(d => d.classList.remove('active'));
            }
        });

        // Auto-hide flash messages
        document.querySelectorAll('.alert').forEach(alert => {
            setTimeout(() => {
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-10px)';
                setTimeout(() => alert.remove(), 300);
            }, 4000);
        });

        // Auto-fetch cart count
        fetch('{{ route("cart.count") }}', { headers: { 'Accept': 'application/json' } })
            .then(res => res.json())
            .then(data => {
                const badge = document.getElementById('cart-count');
                if (badge) badge.textContent = data.count;
            })
            .catch(() => {});
    </script>
    @stack('scripts')
</body>

@else
{{-- === AUTH PAGES (login/register) - no sidebar === --}}
<body>
    @yield('content')
    @stack('scripts')
</body>
@endunless

@else
{{-- === GUEST VIEW - navbar only === --}}
<body>
    <nav class="navbar">
        <div class="container navbar-content">
            <a href="{{ route('home') }}" class="navbar-brand">
                <i class="fas fa-store"></i> Pusat Plastik Wijaya
            </a>
            <div class="navbar-actions">
                @if(request()->routeIs('login'))
                    <a href="{{ route('register') }}" class="btn btn-light btn-sm">Daftar</a>
                @elseif(request()->routeIs('register'))
                    <a href="{{ route('login') }}" class="btn btn-outline-light btn-sm">Masuk</a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-outline-light btn-sm">Masuk</a>
                    <a href="{{ route('register') }}" class="btn btn-light btn-sm">Daftar</a>
                @endif
            </div>
        </div>
    </nav>

    <main class="main-content">
        @yield('content')
    </main>

    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-col">
                    <h3><i class="fas fa-store"></i> Pusat Plastik Wijaya</h3>
                    <p>Menyediakan berbagai macam produk plastik berkualitas dengan harga terjangkau.</p>
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
@endauth
</html>
