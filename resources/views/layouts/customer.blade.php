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
    <div class="admin-wrapper" id="customerWrapper">
        <script>if(window.matchMedia('(min-width:1025px)').matches&&localStorage.getItem('customer-sidebar-collapsed')==='true')document.getElementById('customerWrapper').classList.add('sidebar-collapsed');</script>
        <div class="sidebar-overlay" id="customerOverlay"></div>
        {{-- Sidebar --}}
        <aside class="admin-sidebar" id="customerSidebar">
            <div class="sidebar-header">
                <h2 style="display: flex; align-items: center; gap: 0.75rem; margin: 0; padding: 0.25rem 0;">
                    <img src="{{ asset('storage/logo-navbar.png') }}" alt="Logo" style="max-height: 42px; width: auto; background: rgba(37, 99, 235, 0.15); border: 1px solid rgba(37, 99, 235, 0.3); padding: 4px 6px; border-radius: 6px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <div style="display: flex; flex-direction: column; justify-content: center;">
                        <span style="font-size: 0.95rem; font-weight: 900; color: #f59e0b; text-transform: uppercase; letter-spacing: 0.5px; line-height: 1.1; text-shadow: 0 1px 2px rgba(0,0,0,0.4);">Pusat Plastik</span>
                        <span style="font-size: 0.95rem; font-weight: 900; color: #fcd34d; text-transform: uppercase; letter-spacing: 1px; line-height: 1.1; text-shadow: 0 1px 2px rgba(0,0,0,0.4);">Wijaya</span>
                    </div>
                </h2>
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
                    @if($customerUnreadOrdersCount > 0)
                        <span id="customer-orders-nav-badge" class="nav-badge">{{ $customerUnreadOrdersCount }}</span>
                    @endif
                </a>
                <a href="{{ route('points.index') }}" class="sidebar-link {{ request()->routeIs('points.*') ? 'active' : '' }}">
                    <i class="fas fa-star"></i> <span>Poin Saya</span>
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
                    <form action="{{ route('products.index') }}" method="GET" id="topSearchForm" autocomplete="off">
                        <div class="search-input-group" style="position:relative;">
                            <i class="fas fa-search"></i>
                            <input type="text" name="search" id="topSearchInput"
                                   placeholder="Cari produk..." value="{{ request('search') }}"
                                   autocomplete="off">
                        </div>
                        <div id="topSearchDropdown" class="search-suggest-dropdown" style="display:none;"></div>
                    </form>
                </div>
                <div class="topbar-right">
                    <a href="{{ route('cart.index') }}" style="position:relative; color:var(--gray-300); font-size:1.1rem;">
                        <i class="fas fa-shopping-cart"></i>
                        <span class="badge" id="cart-count" style="position:absolute; top:-6px; right:-10px; background:var(--danger); color:#fff; font-size:0.65rem; padding:0.1rem 0.4rem; border-radius:999px; display:none;">0</span>
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
        const customerSidebar = document.getElementById('customerSidebar');
        const customerWrapper = document.getElementById('customerWrapper');
        const customerOverlay = document.getElementById('customerOverlay');

        const isMobile = () => window.matchMedia('(max-width: 1024px)').matches;

        function customerToggleMobileSidebar(show) {
            customerSidebar.classList.toggle('show', show);
            customerOverlay.classList.toggle('show', show);
        }

        document.getElementById('customerSidebarToggle')?.addEventListener('click', function() {
            if (isMobile()) {
                customerToggleMobileSidebar(!customerSidebar.classList.contains('show'));
            } else {
                customerWrapper.classList.toggle('sidebar-collapsed');
                localStorage.setItem('customer-sidebar-collapsed', customerWrapper.classList.contains('sidebar-collapsed'));
            }
        });

        // Clicking sidebar links: on mobile close sidebar
        document.querySelectorAll('#customerSidebar a.sidebar-link').forEach(link => {
            link.addEventListener('click', function() {
                if (isMobile()) {
                    customerToggleMobileSidebar(false);
                }
            });
        });

        // Close sidebar when clicking overlay (mobile)
        customerOverlay?.addEventListener('click', function() {
            customerToggleMobileSidebar(false);
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

        // Hide orders nav-badge if all unread orders already seen in this session
        (function() {
            const badge = document.getElementById('customer-orders-nav-badge');
            if (!badge) return;
            const serverIds = @json($customerUnreadOrderIds ?? []).map(String);
            if (serverIds.length === 0) return;
            const seen = new Set(JSON.parse(sessionStorage.getItem('customer_seen_orders') || '[]'));
            if (serverIds.every(id => seen.has(id))) {
                badge.style.display = 'none';
            }
        })();

        // Auto-fetch cart count
        fetch('{{ route("cart.count") }}', { headers: { 'Accept': 'application/json' } })
            .then(res => res.json())
            .then(data => {
                const badge = document.getElementById('cart-count');
                if (badge) {
                    badge.textContent = data.count;
                    badge.style.display = data.count > 0 ? 'inline-block' : 'none';
                }
            })
            .catch(() => {});

    </script>
    @stack('scripts')

    {{-- Search Autocomplete CSS → app.css (shared with guest mode) --}}

    <script>
    (function () {
        const input    = document.getElementById('topSearchInput');
        const dropdown = document.getElementById('topSearchDropdown');
        const form     = document.getElementById('topSearchForm');
        if (!input || !dropdown) return;

        const SUGGEST_URL = '{{ route("products.suggest") }}';
        const SEARCH_URL  = '{{ route("products.index") }}';
        let timer = null;
        let activeIdx = -1;

        function escHtml(s) {
            return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
        }

        function highlight(text, query) {
            if (!query) return escHtml(text);
            const escaped = query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
            return escHtml(text).replace(new RegExp('(' + escaped + ')', 'gi'),
                '<mark style="background:#dbeafe;color:#1d4ed8;border-radius:2px;padding:0 1px;">$1</mark>');
        }

        function renderDropdown(items, query) {
            if (!items.length) {
                dropdown.innerHTML = '<div class="ssd-empty"><i class="fas fa-search" style="margin-right:0.4rem;"></i>Produk tidak ditemukan</div>';
                dropdown.style.display = 'block';
                return;
            }
            let html = '';
            items.forEach(function (p, i) {
                const imgHtml = p.image
                    ? '<img class="ssd-img" src="' + p.image + '" alt="" loading="lazy">'
                    : '<div class="ssd-img-placeholder"><i class="fas fa-box"></i></div>';
                html += '<a class="ssd-item" href="' + SEARCH_URL + '?search=' + encodeURIComponent(p.name) + '" data-slug="' + p.slug + '">'
                    + imgHtml
                    + '<div class="ssd-info">'
                    +   '<div class="ssd-name">' + highlight(p.name, query) + '</div>'
                    +   '<div class="ssd-meta">' + escHtml(p.category) + '</div>'
                    + '</div>'
                    + '<div class="ssd-price-wrap">'
                    +   (p.is_promo && p.price_original ? '<span class="ssd-price-original">' + escHtml(p.price_original) + '</span>' : '')
                    +   '<span class="ssd-price' + (p.is_promo ? ' promo' : '') + '">' + escHtml(p.price) + '</span>'
                    + '</div>'
                    + '</a>';
            });
            // "Lihat semua hasil" footer
            html += '<div class="ssd-footer" id="ssd-see-all"><i class="fas fa-search"></i> Lihat semua hasil untuk "<strong>' + escHtml(query) + '</strong>"</div>';
            dropdown.innerHTML = html;
            dropdown.style.display = 'block';
            activeIdx = -1;

            dropdown.querySelectorAll('.ssd-item').forEach(function (el) {
                el.addEventListener('mousedown', function (e) {
                    e.preventDefault();
                    window.location.href = el.href;
                });
            });
            const seeAll = document.getElementById('ssd-see-all');
            if (seeAll) {
                seeAll.addEventListener('mousedown', function (e) {
                    e.preventDefault();
                    form.submit();
                });
            }
        }

        function closeDropdown() {
            dropdown.style.display = 'none';
            activeIdx = -1;
        }

        function fetchSuggestions(q) {
            fetch(SUGGEST_URL + '?q=' + encodeURIComponent(q), {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (input.value.trim().length >= 2) renderDropdown(data, input.value.trim());
            })
            .catch(function () {});
        }

        input.addEventListener('input', function () {
            clearTimeout(timer);
            const q = input.value.trim();
            if (q.length < 2) { closeDropdown(); return; }
            timer = setTimeout(function () { fetchSuggestions(q); }, 280);
        });

        // Keyboard navigation
        input.addEventListener('keydown', function (e) {
            const items = dropdown.querySelectorAll('.ssd-item, #ssd-see-all');
            if (!items.length) return;
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                activeIdx = Math.min(activeIdx + 1, items.length - 1);
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                activeIdx = Math.max(activeIdx - 1, -1);
            } else if (e.key === 'Enter' && activeIdx >= 0) {
                e.preventDefault();
                const el = items[activeIdx];
                if (el.id === 'ssd-see-all') { form.submit(); }
                else { window.location.href = el.href; }
                return;
            } else if (e.key === 'Escape') {
                closeDropdown(); return;
            } else { return; }
            items.forEach(function (el, i) { el.classList.toggle('active', i === activeIdx); });
        });

        // Close on outside click
        document.addEventListener('click', function (e) {
            if (!e.target.closest('.topbar-search')) closeDropdown();
        });

        // Re-open on focus if there's query
        input.addEventListener('focus', function () {
            if (input.value.trim().length >= 2 && dropdown.innerHTML) {
                dropdown.style.display = 'block';
            }
        });
    })();
    </script>

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
            <a href="{{ route('home') }}" class="navbar-brand" style="display: flex; align-items: center; gap: 0.75rem; text-decoration: none;">
                <img src="{{ asset('storage/logo-navbar.png') }}" alt="Logo" style="max-height: 42px; width: auto; background: rgba(37, 99, 235, 0.15); border: 1px solid rgba(37, 99, 235, 0.3); padding: 4px 6px; border-radius: 6px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                <span style="font-size: 1.25rem; font-weight: 900; color: #f59e0b; text-transform: uppercase; letter-spacing: 0.5px; text-shadow: 0 2px 4px rgba(0,0,0,0.3);">Pusat Plastik <span style="color: #fcd34d;">Wijaya</span></span>
            </a>
            {{-- Search bar (hidden on auth pages) --}}
            @unless(request()->routeIs('login') || request()->routeIs('register') || request()->routeIs('verify-email') || request()->routeIs('password.*'))
            <form action="{{ route('products.index') }}" method="GET" class="guest-navbar-search" autocomplete="off" id="guestSearchForm">
                <div class="guest-search-wrap">
                    <i class="fas fa-search"></i>
                    <input type="text" name="search" id="guestSearchInput" placeholder="Cari produk..." value="{{ request('search') }}" autocomplete="off">
                </div>
                <div id="guestSearchDropdown" class="search-suggest-dropdown" style="display:none;"></div>
            </form>
            @endunless
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
                        <li><i class="fab fa-whatsapp"></i> <a href="https://wa.me/6282313505557" target="_blank">+62 823-1350-5557</a></li>
                        <li><i class="fas fa-envelope"></i> <a href="mailto:pwp5758wijaya@gmail.com">pwp5758wijaya@gmail.com</a></li>
                        <li><i class="fab fa-instagram"></i> <a href="https://www.instagram.com/pusatplastikwijaya/" target="_blank">Instagram</a></li>
                        <li><i class="fab fa-tiktok"></i> <a href="https://www.tiktok.com/@plastikwijaya" target="_blank">TikTok</a></li>
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

    {{-- Guest Search Autocomplete (same system as logged-in customer) --}}
    <script>
    (function () {
        const input    = document.getElementById('guestSearchInput');
        const dropdown = document.getElementById('guestSearchDropdown');
        const form     = document.getElementById('guestSearchForm');
        if (!input || !dropdown) return;

        const SUGGEST_URL = '{{ route("products.suggest") }}';
        const SEARCH_URL  = '{{ route("products.index") }}';
        let timer = null;
        let activeIdx = -1;

        function escHtml(s) {
            return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
        }
        function highlight(text, query) {
            if (!query) return escHtml(text);
            const escaped = query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
            return escHtml(text).replace(new RegExp('(' + escaped + ')', 'gi'),
                '<mark style="background:#dbeafe;color:#1d4ed8;border-radius:2px;padding:0 1px;">$1</mark>');
        }

        function renderDropdown(items, query) {
            if (!items.length) {
                dropdown.innerHTML = '<div class="ssd-empty"><i class="fas fa-search" style="margin-right:0.4rem;"></i>Produk tidak ditemukan</div>';
                dropdown.style.display = 'block';
                return;
            }
            let html = '';
            items.forEach(function (p) {
                const imgHtml = p.image
                    ? '<img class="ssd-img" src="' + p.image + '" alt="" loading="lazy">'
                    : '<div class="ssd-img-placeholder"><i class="fas fa-box"></i></div>';
                html += '<a class="ssd-item" href="' + SEARCH_URL + '?search=' + encodeURIComponent(p.name) + '">'
                    + imgHtml
                    + '<div class="ssd-info">'
                    +   '<div class="ssd-name">' + highlight(p.name, query) + '</div>'
                    +   '<div class="ssd-meta">' + escHtml(p.category) + '</div>'
                    + '</div>'
                    + '<div class="ssd-price-wrap">'
                    +   (p.is_promo && p.price_original ? '<span class="ssd-price-original">' + escHtml(p.price_original) + '</span>' : '')
                    +   '<span class="ssd-price' + (p.is_promo ? ' promo' : '') + '">' + escHtml(p.price) + '</span>'
                    + '</div>'
                    + '</a>';
            });
            html += '<div class="ssd-footer" id="gSsdSeeAll"><i class="fas fa-search"></i> Lihat semua hasil untuk "<strong>' + escHtml(query) + '</strong>"</div>';
            dropdown.innerHTML = html;
            dropdown.style.display = 'block';
            activeIdx = -1;

            dropdown.querySelectorAll('.ssd-item').forEach(function (el) {
                el.addEventListener('mousedown', function (e) { e.preventDefault(); window.location.href = el.href; });
            });
            const seeAll = document.getElementById('gSsdSeeAll');
            if (seeAll) seeAll.addEventListener('mousedown', function (e) { e.preventDefault(); form.submit(); });
        }

        function closeDropdown() { dropdown.style.display = 'none'; activeIdx = -1; }

        function fetchSuggestions(q) {
            fetch(SUGGEST_URL + '?q=' + encodeURIComponent(q), {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (input.value.trim().length >= 2) renderDropdown(data, input.value.trim());
            })
            .catch(function () {});
        }

        input.addEventListener('input', function () {
            clearTimeout(timer);
            const q = input.value.trim();
            if (q.length < 2) { closeDropdown(); return; }
            timer = setTimeout(function () { fetchSuggestions(q); }, 280);
        });

        input.addEventListener('keydown', function (e) {
            const items = dropdown.querySelectorAll('.ssd-item, #gSsdSeeAll');
            if (!items.length) return;
            if (e.key === 'ArrowDown') { e.preventDefault(); activeIdx = Math.min(activeIdx + 1, items.length - 1); }
            else if (e.key === 'ArrowUp') { e.preventDefault(); activeIdx = Math.max(activeIdx - 1, -1); }
            else if (e.key === 'Enter' && activeIdx >= 0) {
                e.preventDefault();
                const el = items[activeIdx];
                if (el.id === 'gSsdSeeAll') { form.submit(); } else { window.location.href = el.href; }
                return;
            } else if (e.key === 'Escape') { closeDropdown(); return; } else { return; }
            items.forEach(function (el, i) { el.classList.toggle('active', i === activeIdx); });
        });

        document.addEventListener('click', function (e) {
            if (!e.target.closest('.guest-navbar-search')) closeDropdown();
        });

        input.addEventListener('focus', function () {
            if (input.value.trim().length >= 2 && dropdown.innerHTML) dropdown.style.display = 'block';
        });
    })();
    </script>
    @stack('scripts')
</body>
@endauth
</html>
