<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', __('Admin Panel') . ' — Allocore')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', sans-serif; background: #0a0e1a; color: #e2e8f0; min-height: 100vh; }
        :root { --admin: #dc2626; --sidebar-w: 240px; }

        .sidebar {
            position: fixed; top: 0; left: 0; height: 100vh; width: var(--sidebar-w);
            background: #0a0e1a;
            border-right: 1px solid rgba(220,38,38,0.2);
            display: flex; flex-direction: column; z-index: 50;
        }
        .sidebar-logo { padding: 22px 18px 18px; border-bottom: 1px solid rgba(220,38,38,0.12); }
        .sidebar-logo h2 {
            font-size: 16px; font-weight: 700;
            color: #f87171;
        }
        .sidebar-logo p { font-size: 10px; color: #64748b; margin-top: 2px; }
        .sidebar-nav { flex: 1; padding: 14px 10px; overflow-y: auto; }
        .nav-label { font-size: 9px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 1px; padding: 8px 8px 4px; margin-top: 6px; }
        .nav-item {
            display: flex; align-items: center; gap: 9px; padding: 9px 10px;
            border-radius: 7px; color: #94a3b8; font-size: 12.5px; font-weight: 500;
            text-decoration: none; transition: all .15s; margin-bottom: 2px;
        }
        .nav-item:hover { background: rgba(220,38,38,0.1); color: #fca5a5; }
        .nav-item.active { background: rgba(220,38,38,0.15); color: #f87171; }
        .sidebar-footer { padding: 14px; border-top: 1px solid rgba(220,38,38,0.1); }
        .main { margin-left: var(--sidebar-w); min-height: 100vh; }
        .topbar {
            height: 56px; background: rgba(10,14,26,0.95); backdrop-filter: blur(8px);
            border-bottom: 1px solid rgba(220,38,38,0.1);
            display: flex; align-items: center; padding: 0 22px; gap: 12px;
            position: sticky; top: 0; z-index: 40;
        }
        .topbar-title { font-size: 14px; font-weight: 600; color: #e2e8f0; flex: 1; }
        .menu-toggle {
            display: none;
            align-items: center;
            justify-content: center;
            width: 34px;
            height: 34px;
            border-radius: 8px;
            border: 1px solid rgba(220,38,38,0.3);
            background: rgba(220,38,38,0.12);
            color: #fca5a5;
            cursor: pointer;
            font-size: 15px;
            line-height: 1;
        }
        .mobile-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(2,6,23,0.6);
            z-index: 45;
        }
        .page { padding: 24px; }
        .card { background: rgba(26,10,10,0.4); border: 1px solid rgba(220,38,38,0.12); border-radius: 10px; padding: 18px; }
        .card-title { font-size: 13px; font-weight: 600; color: #fca5a5; margin-bottom: 14px; }
        .alert { padding: 11px 14px; border-radius: 7px; font-size: 12px; margin-bottom: 18px; }
        .alert-success { background: rgba(16,185,129,0.1); border: 1px solid rgba(16,185,129,0.3); color: #34d399; }
        .alert-error { background: rgba(220,38,38,0.1); border: 1px solid rgba(220,38,38,0.3); color: #f87171; }
        .btn { display: inline-flex; align-items: center; gap: 5px; padding: 7px 14px; border-radius: 7px; font-size: 12px; font-weight: 500; cursor: pointer; border: none; text-decoration: none; transition: all .15s; }
        .btn-danger { background: rgba(220,38,38,0.15); color: #f87171; border: 1px solid rgba(220,38,38,0.25); }
        .btn-danger:hover { background: rgba(220,38,38,0.25); }
        .btn-secondary { background: rgba(255,255,255,0.07); color: #94a3b8; border: 1px solid rgba(255,255,255,0.1); }
        .btn-secondary:hover { background: rgba(255,255,255,0.1); color: #e2e8f0; }
        .btn-primary { background: #dc2626; color: white; }
        .btn-primary:hover { background: #b91c1c; }
        .btn-sm { padding: 4px 10px; font-size: 11px; }
        .data-table { width: 100%; border-collapse: collapse; font-size: 12px; }
        .data-table th { text-align: left; padding: 8px 10px; color: #64748b; font-weight: 500; font-size: 10px; text-transform: uppercase; letter-spacing: .5px; border-bottom: 1px solid rgba(220,38,38,0.1); }
        .data-table td { padding: 10px 10px; border-bottom: 1px solid rgba(255,255,255,0.04); color: #cbd5e1; vertical-align: middle; }
        .data-table tr:hover td { background: rgba(220,38,38,0.04); }
        .form-control { width: 100%; padding: 7px 10px; border-radius: 7px; font-size: 12px; background: rgba(255,255,255,0.05); border: 1px solid rgba(220,38,38,0.2); color: #e2e8f0; font-family: 'Inter', sans-serif; }
        .form-control:focus { outline: none; border-color: #dc2626; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 10px; font-size: 10px; font-weight: 500; }
        .badge-admin { background: rgba(220,38,38,0.15); color: #f87171; border: 1px solid rgba(220,38,38,0.3); }
        .badge-analyst { background: rgba(99,102,241,0.15); color: #818cf8; border: 1px solid rgba(99,102,241,0.3); }
        .badge-viewer { background: rgba(100,116,139,0.15); color: #94a3b8; border: 1px solid rgba(100,116,139,0.3); }
        .lang-switcher { display: flex; gap: 6px; }
        .lang-switcher a { font-size: 11px; font-weight: 600; color: #94a3b8; text-decoration: none; padding: 4px 8px; border-radius: 6px; border: 1px solid rgba(255,255,255,0.1); }
        .lang-switcher a.active { background: rgba(220,38,38,0.15); color: #f87171; border-color: rgba(220,38,38,0.3); }
        .lang-switcher a:hover { color: #e2e8f0; }

        @media (max-width: 1024px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform .2s ease;
            }
            body.nav-open .sidebar {
                transform: translateX(0);
            }
            .mobile-overlay {
                display: block;
                opacity: 0;
                pointer-events: none;
                transition: opacity .2s ease;
            }
            body.nav-open .mobile-overlay {
                opacity: 1;
                pointer-events: auto;
            }
            .main { margin-left: 0; }
            .topbar { padding: 0 14px; }
            .menu-toggle { display: inline-flex; }
            .page { padding: 16px; }
        }

        @media (max-width: 640px) {
            .topbar {
                min-height: 56px;
                height: auto;
                padding: 10px 12px;
            }
            .topbar-title { font-size: 13px; }
            .card { padding: 14px; }
            .data-table { font-size: 11px; display: block; overflow-x: auto; white-space: nowrap; }
            .page { padding: 12px; }
        }
    </style>
    @stack('styles')
</head>
<body>
<div class="mobile-overlay" id="mobileOverlay"></div>
<aside class="sidebar">
    <div class="sidebar-logo">
        <h2>🛡 {{ __('Admin Panel') }}</h2>
        <p>Allocore Financial Platform</p>
    </div>
    <nav class="sidebar-nav">
        <div class="nav-label">{{ __('Administration') }}</div>
        <a href="{{ route('admin.index') }}" class="nav-item {{ request()->routeIs('admin.index') ? 'active' : '' }}">📊 {{ __('Dashboard') }}</a>
        <a href="{{ route('admin.users') }}" class="nav-item {{ request()->routeIs('admin.users') ? 'active' : '' }}">👥 {{ __('Users') }}</a>
        <a href="{{ route('admin.thresholds') }}" class="nav-item {{ request()->routeIs('admin.thresholds') ? 'active' : '' }}">⚙ {{ __('KPI Thresholds') }}</a>
        <a href="{{ route('admin.invoicemaker') }}" class="nav-item {{ request()->routeIs('admin.invoicemaker') ? 'active' : '' }}">🧾 {{ __('Invoice Maker') }}</a>
        <div class="nav-label" style="margin-top:12px;">{{ __('Navigation') }}</div>
        <a href="{{ route('dashboard') }}" class="nav-item">🏠 {{ __('Back to app') }}</a>
        <a href="{{ route('analyses.index') }}" class="nav-item">📋 {{ __('All Analyses') }}</a>
    </nav>
    <div class="sidebar-footer">
        <div style="font-size:11px; color:#64748b; margin-bottom:8px;">
            {{ __('Logged in as') }} <strong style="color:#f87171;">{{ Auth::user()->name }}</strong>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-secondary" style="width:100%; justify-content:center; font-size:11px;">🚪 {{ __('Sign out') }}</button>
        </form>
    </div>
</aside>
<div class="main">
    <div class="topbar">
        <button type="button" class="menu-toggle" id="menuToggle" aria-label="{{ __('Toggle navigation') }}">☰</button>
        <div class="topbar-title">@yield('page-title', __('Admin'))</div>
        <div class="lang-switcher" style="margin-right:10px;">
            <a href="{{ route('locale.switch', 'de') }}" class="{{ app()->getLocale() === 'de' ? 'active' : '' }}">DE</a>
            <a href="{{ route('locale.switch', 'en') }}" class="{{ app()->getLocale() === 'en' ? 'active' : '' }}">EN</a>
        </div>
        <div>@yield('topbar-actions')</div>
    </div>
    <div class="page">
        @if(session('success'))
            <div class="alert alert-success">✅ {{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-error">❌ {{ session('error') }}</div>
        @endif
        @yield('content')
    </div>
</div>
@stack('scripts')
<script>
    (() => {
        const body = document.body;
        const toggle = document.getElementById('menuToggle');
        const overlay = document.getElementById('mobileOverlay');
        if (!toggle || !overlay) return;

        const closeMenu = () => body.classList.remove('nav-open');
        toggle.addEventListener('click', () => body.classList.toggle('nav-open'));
        overlay.addEventListener('click', closeMenu);
        window.addEventListener('resize', () => {
            if (window.innerWidth > 1024) closeMenu();
        });
    })();
</script>
</body>
</html>
