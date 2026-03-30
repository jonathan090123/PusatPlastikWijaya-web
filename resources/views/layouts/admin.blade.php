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
    <div class="admin-wrapper" id="adminWrapper">
        <script>if(window.matchMedia('(min-width:1025px)').matches&&localStorage.getItem('admin-sidebar-collapsed')==='true')document.getElementById('adminWrapper').classList.add('sidebar-collapsed');</script>
        <div class="sidebar-overlay" id="adminOverlay"></div>
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
                    @if($adminActiveOrdersCount > 0)
                        <span class="nav-badge">{{ $adminActiveOrdersCount }}</span>
                    @endif
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
                <button class="sidebar-toggle" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
                @if($adminNewOrdersCount > 0)
                <a href="{{ route('admin.orders.index') }}" class="topbar-order-alert">
                    <i class="fas fa-bell topbar-order-bell"></i>
                    <span>{{ $adminNewOrdersCount }} pesanan baru menunggu anda</span>
                    <i class="fas fa-arrow-right" style="font-size:0.75rem;"></i>
                </a>
                @endif
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
        const adminSidebar = document.getElementById('adminSidebar');
        const adminWrapper = document.getElementById('adminWrapper');
        const adminOverlay = document.getElementById('adminOverlay');

        const isMobile = () => window.matchMedia('(max-width: 1024px)').matches;

        function adminToggleMobileSidebar(show) {
            adminSidebar.classList.toggle('show', show);
            adminOverlay.classList.toggle('show', show);
        }

        document.getElementById('sidebarToggle')?.addEventListener('click', function() {
            if (isMobile()) {
                adminToggleMobileSidebar(!adminSidebar.classList.contains('show'));
            } else {
                adminWrapper.classList.toggle('sidebar-collapsed');
                localStorage.setItem('admin-sidebar-collapsed', adminWrapper.classList.contains('sidebar-collapsed'));
            }
        });

        // Clicking sidebar links: on mobile close sidebar
        document.querySelectorAll('#adminSidebar a.sidebar-link').forEach(link => {
            link.addEventListener('click', function() {
                if (isMobile()) {
                    adminToggleMobileSidebar(false);
                }
            });
        });

        // Close sidebar when clicking overlay (mobile)
        adminOverlay?.addEventListener('click', function() {
            adminToggleMobileSidebar(false);
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

    {{-- Global Confirm Modal --}}
    <div id="wwConfirmModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.4); z-index:99999; align-items:center; justify-content:center; padding:1rem;">
        <div style="background:#fff; border-radius:12px; padding:1.75rem 1.5rem 1.5rem; max-width:340px; width:100%; text-align:center; box-shadow:0 16px 40px rgba(0,0,0,0.14); animation:wwPop 0.22s cubic-bezier(0.34,1.56,0.64,1);">
            <div id="wwConfirmIcon" style="width:48px; height:48px; border-radius:50%; background:#fef2f2; display:flex; align-items:center; justify-content:center; margin:0 auto 1rem;">
                <i class="fas fa-trash" style="color:#ef4444; font-size:1.1rem;"></i>
            </div>
            <h3 id="wwConfirmTitle" style="font-size:1rem; font-weight:800; color:#111827; margin-bottom:0.4rem;"></h3>
            <p id="wwConfirmMsg" style="font-size:0.83rem; color:#6b7280; line-height:1.6; margin-bottom:1.35rem;"></p>
            <div style="display:flex; gap:0.6rem;">
                <button id="wwConfirmNo" style="flex:1; padding:0.6rem; border-radius:8px; border:1.5px solid #e5e7eb; background:#fff; color:#374151; font-weight:700; font-size:0.85rem; cursor:pointer;">Batal</button>
                <button id="wwConfirmYes" style="flex:1; padding:0.6rem; border-radius:8px; border:none; background:#ef4444; color:#fff; font-weight:700; font-size:0.85rem; cursor:pointer;">Hapus</button>
            </div>
        </div>
    </div>
    <style>
    @keyframes wwPop { from{opacity:0;transform:scale(0.9)} to{opacity:1;transform:scale(1)} }
    </style>
    <script>
    (function(){
        var modal   = document.getElementById('wwConfirmModal');
        var btnNo   = document.getElementById('wwConfirmNo');
        var btnYes  = document.getElementById('wwConfirmYes');
        var _cb     = null;

        function close(){ modal.style.display='none'; _cb=null; btnYes.textContent='Hapus'; btnYes.disabled=false; }

        btnNo.addEventListener('click', close);
        modal.addEventListener('click', function(e){ if(e.target===modal) close(); });
        document.addEventListener('keydown', function(e){ if(e.key==='Escape') close(); });
        btnYes.addEventListener('click', function(){
            btnYes.textContent='Menghapus...'; btnYes.disabled=true;
            if(_cb) _cb();
        });

        window.wwConfirm = function(title, msg, cb, opts){
            opts = opts || {};
            document.getElementById('wwConfirmTitle').textContent = title;
            document.getElementById('wwConfirmMsg').textContent   = msg;
            btnYes.textContent  = opts.confirmText  || 'Hapus';
            btnYes.style.background = opts.confirmColor || '#ef4444';
            btnYes.disabled = false;
            _cb = cb;
            modal.style.display='flex';
        };
    })();
    </script>
</body>
</html>
