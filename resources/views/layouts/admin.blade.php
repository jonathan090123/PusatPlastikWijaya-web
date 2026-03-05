<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin - Pusat Plastik Wijaya')</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="admin-body">
    <div class="admin-wrapper">
        {{-- Sidebar --}}
        <aside class="admin-sidebar" id="adminSidebar">
            <div class="sidebar-header">
                <h2><i class="fas fa-store"></i> <span>Admin Panel</span></h2>
                <p class="sidebar-greeting">
                    <span>
                        <span id="admin-greeting">Selamat...</span>, {{ Auth::user()->name }} 👋
                        <script>
                            (function() {
                                const hour = new Date().getHours();
                                let greeting = 'Selamat Malam';
                                if (hour >= 1 && hour < 10) greeting = 'Selamat Pagi';
                                else if (hour >= 10 && hour < 15) greeting = 'Selamat Siang';
                                else if (hour >= 15 && hour < 18) greeting = 'Selamat Sore';
                                document.getElementById('admin-greeting').textContent = greeting;
                            })();
                        </script>
                    </span>
                </p>
            </div>
            <nav class="sidebar-nav">
                <a href="{{ route('admin.dashboard') }}" class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-tachometer-alt"></i> <span>Dashboard</span>
                </a>
                <a href="{{ route('admin.products.index') }}" class="sidebar-link {{ request()->routeIs('admin.products.*') ? 'active' : '' }}">
                    <i class="fas fa-box"></i> <span>Produk</span>
                </a>
                <a href="{{ route('admin.categories.index') }}" class="sidebar-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
                    <i class="fas fa-tags"></i> <span>Kategori</span>
                </a>
                <a href="{{ route('admin.orders.index') }}" class="sidebar-link {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">
                    <i class="fas fa-shopping-bag"></i> <span>Pesanan</span>
                </a>
                <a href="{{ route('admin.customers.index') }}" class="sidebar-link {{ request()->routeIs('admin.customers.*') ? 'active' : '' }}">
                    <i class="fas fa-users"></i> <span>Pelanggan</span>
                </a>
                <a href="{{ route('admin.shipping.index') }}" class="sidebar-link {{ request()->routeIs('admin.shipping.*') ? 'active' : '' }}">
                    <i class="fas fa-truck"></i> <span>Pengiriman</span>
                </a>
                <a href="{{ route('admin.reports.index') }}" class="sidebar-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
                    <i class="fas fa-chart-bar"></i> <span>Laporan</span>
                </a>
            </nav>
            <div style="margin-top:auto; padding:1rem;">
                <a href="{{ route('profile.edit') }}" class="sidebar-link {{ request()->routeIs('profile.*') ? 'active' : '' }}" style="margin-bottom:0.5rem; background:rgba(96,165,250,0.15); border:1px solid rgba(96,165,250,0.25); border-radius:var(--radius-sm); font-weight:600; color:#93c5fd;">
                    <i class="fas fa-user-cog"></i> <span>Profil</span>
                </a>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="sidebar-link" style="width:100%; color:#fecaca; border:1px solid rgba(252,165,165,0.25); border-radius:var(--radius-sm); background:rgba(185,28,28,0.6); justify-content:flex-start; font-weight:600;">
                        <i class="fas fa-sign-out-alt"></i> <span>Logout</span>
                    </button>
                </form>
            </div>
        </aside>

        {{-- Main Content --}}
        <div class="admin-main">
            {{-- Top Bar --}}
            <header class="admin-topbar">
                <button class="sidebar-toggle" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="topbar-right">
                    <div class="nav-dropdown">
                        <button class="nav-dropdown-toggle">
                            <i class="fas fa-user-circle"></i>
                            <span>{{ Auth::user()->name }}</span>
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="nav-dropdown-menu">
                            <a href="{{ route('profile.edit') }}"><i class="fas fa-user-cog"></i> Profil</a>
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
        document.getElementById('sidebarToggle')?.addEventListener('click', function() {
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
    </script>
    @stack('scripts')
</body>
</html>
