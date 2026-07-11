<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', __('Allocore Financial Platform'))</title>
    <meta name="description" content="@yield('meta_description', __('Professional financial analysis for GmbH, financial statements and real estate.'))">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- ApexCharts -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --sidebar-w: 256px;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', sans-serif; background: #0f172a; color: #e2e8f0; min-height: 100vh; }

        /* Sidebar */
        .sidebar {
            position: fixed; top: 0; left: 0; height: 100vh; width: var(--sidebar-w);
            background: #0f172a;
            border-right: 1px solid rgba(99,102,241,0.2);
            display: flex; flex-direction: column; z-index: 50;
        }
        .sidebar-logo { padding: 24px 20px 20px; border-bottom: 1px solid rgba(99,102,241,0.15); }
        .sidebar-logo h2 {
            font-size: 18px; font-weight: 700;
            color: #818cf8;
        }
        .sidebar-logo p { font-size: 11px; color: #64748b; margin-top: 2px; }
        .sidebar-nav { flex: 1; padding: 16px 12px; overflow-y: auto; }
        .nav-label { font-size: 10px; font-weight: 600; color: #475569; text-transform: uppercase;
            letter-spacing: 1px; padding: 8px 8px 4px; margin-top: 8px; }
        .nav-item {
            display: flex; align-items: center; gap: 10px; padding: 10px 12px;
            border-radius: 8px; color: #94a3b8; font-size: 13px; font-weight: 500;
            text-decoration: none; transition: all .15s; margin-bottom: 2px;
        }
        .nav-item:hover { background: rgba(99,102,241,0.12); color: #c7d2fe; }
        .nav-item.active { background: rgba(99,102,241,0.2); color: #818cf8; }
        .nav-item .icon { width: 18px; text-align: center; flex-shrink: 0; }
        .sidebar-footer { padding: 16px; border-top: 1px solid rgba(99,102,241,0.15); }
        .user-card { display: flex; align-items: center; gap: 10px; padding: 10px;
            background: rgba(255,255,255,0.05); border-radius: 8px; }
        .user-avatar { width: 32px; height: 32px; border-radius: 50%;
            background: #6366f1;
            display: flex; align-items: center; justify-content: center;
            font-size: 13px; font-weight: 600; color: white; flex-shrink: 0; }
        .user-name { font-size: 13px; font-weight: 500; color: #e2e8f0; }
        .user-role { font-size: 11px; color: #64748b; }

        /* Main content */
        .main { margin-left: var(--sidebar-w); min-height: 100vh; }
        .topbar {
            height: 60px; background: rgba(15,23,42,0.95); backdrop-filter: blur(8px);
            border-bottom: 1px solid rgba(99,102,241,0.1);
            display: flex; align-items: center; padding: 0 24px; gap: 12px;
            position: sticky; top: 0; z-index: 40;
        }
        .topbar-title { font-size: 16px; font-weight: 600; color: #e2e8f0; flex: 1; }
        .topbar-actions { display: flex; align-items: center; gap: 8px; }
        .menu-toggle {
            display: none;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            border-radius: 8px;
            border: 1px solid rgba(99,102,241,0.3);
            background: rgba(99,102,241,0.12);
            color: #c7d2fe;
            cursor: pointer;
            font-size: 16px;
            line-height: 1;
        }
        .mobile-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(2,6,23,0.6);
            z-index: 45;
        }

        /* Buttons */
        .btn { display: inline-flex; align-items: center; gap: 6px;
            padding: 8px 16px; border-radius: 8px; font-size: 13px; font-weight: 500;
            cursor: pointer; border: none; text-decoration: none; transition: all .15s; }
        .btn-primary { background: var(--primary); color: white; }
        .btn-primary:hover { background: var(--primary-dark); transform: translateY(-1px); }
        .btn-secondary { background: rgba(255,255,255,0.08); color: #cbd5e1; border: 1px solid rgba(255,255,255,0.1); }
        .btn-secondary:hover { background: rgba(255,255,255,0.12); }
        .btn-danger { background: rgba(239,68,68,0.15); color: #f87171; border: 1px solid rgba(239,68,68,0.2); }
        .btn-danger:hover { background: rgba(239,68,68,0.25); }
        .btn-success { background: rgba(16,185,129,0.15); color: #34d399; border: 1px solid rgba(16,185,129,0.2); }
        .btn-success:hover { background: rgba(16,185,129,0.25); }
        .btn-sm { padding: 5px 12px; font-size: 12px; }

        /* Page content */
        .page { padding: 28px; }

        /* Cards */
        .card { background: rgba(30,27,75,0.4); border: 1px solid rgba(99,102,241,0.15); border-radius: 12px; padding: 20px; }
        .card-title { font-size: 14px; font-weight: 600; color: #c7d2fe; margin-bottom: 16px; }

        /* Alerts */
        .alert { padding: 12px 16px; border-radius: 8px; font-size: 13px; margin-bottom: 20px; display: flex; align-items: center; gap: 8px; }
        .alert-success { background: rgba(16,185,129,0.12); border: 1px solid rgba(16,185,129,0.3); color: #34d399; }
        .alert-error { background: rgba(239,68,68,0.12); border: 1px solid rgba(239,68,68,0.3); color: #f87171; }

        /* Traffic lights */
        .tl-green { color: #10b981; } .tl-yellow { color: #f59e0b; } .tl-red { color: #ef4444; }
        .badge { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 500; }
        .badge-green  { background: rgba(16,185,129,0.15); color: #34d399; border: 1px solid rgba(16,185,129,0.3); }
        .badge-yellow { background: rgba(245,158,11,0.15); color: #fbbf24; border: 1px solid rgba(245,158,11,0.3); }
        .badge-red    { background: rgba(239,68,68,0.15); color: #f87171; border: 1px solid rgba(239,68,68,0.3); }
        .badge-gray   { background: rgba(100,116,139,0.15); color: #94a3b8; border: 1px solid rgba(100,116,139,0.3); }

        /* Tables */
        .data-table { width: 100%; border-collapse: collapse; font-size: 13px; }
        .data-table th { text-align: left; padding: 10px 12px; color: #64748b; font-weight: 500;
            font-size: 11px; text-transform: uppercase; letter-spacing: .5px;
            border-bottom: 1px solid rgba(99,102,241,0.1); }
        .data-table td { padding: 12px 12px; border-bottom: 1px solid rgba(255,255,255,0.04); color: #cbd5e1; vertical-align: middle; }
        .data-table tr:last-child td { border-bottom: none; }
        .data-table tr:hover td { background: rgba(99,102,241,0.05); }

        /* Forms */
        .form-group { margin-bottom: 18px; }
        .form-label { display: block; font-size: 12px; font-weight: 500; color: #94a3b8; margin-bottom: 6px; }
        .form-control { width: 100%; padding: 10px 14px; border-radius: 8px; font-size: 13px;
            background: rgba(255,255,255,0.06); border: 1px solid rgba(99,102,241,0.2);
            color: #e2e8f0; transition: border-color .15s; font-family: 'Inter', sans-serif; }
        .form-control:focus { outline: none; border-color: #6366f1; background: rgba(255,255,255,0.08); }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        .form-grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; }
        .col-span-2 { grid-column: span 2; }

        /* Score */
        .score-green { color: #10b981; } .score-yellow { color: #f59e0b; }
        .score-red { color: #ef4444; } .score-gray { color: #64748b; }
        .score-lg { font-size: 56px; font-weight: 700; line-height: 1; }

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
            .topbar-title { font-size: 14px; }
            .topbar-actions { gap: 6px; flex-wrap: wrap; justify-content: flex-end; }
            .menu-toggle { display: inline-flex; }
            .page { padding: 16px; }
            .form-grid, .form-grid-3 { grid-template-columns: 1fr; }
            .col-span-2 { grid-column: auto; }
        }

        @media (max-width: 640px) {
            .topbar {
                min-height: 60px;
                height: auto;
                padding: 10px 12px;
                align-items: flex-start;
            }
            .topbar-title { font-size: 13px; }
            .btn { padding: 7px 12px; font-size: 12px; }
            .card { padding: 14px; }
            .data-table { font-size: 12px; display: block; overflow-x: auto; white-space: nowrap; }
            .page { padding: 12px; }
        }
    </style>

    @stack('styles')
</head>
<body>

<div class="mobile-overlay" id="mobileOverlay"></div>

<aside class="sidebar">
    <div class="sidebar-logo">
        <h2>⬡ Allocore</h2>
        <p>Financial Platform</p>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-label">{{ __('Overview') }}</div>
        <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <span class="icon">🏠</span> {{ __('Dashboard') }}
        </a>
        <a href="{{ route('companies.index') }}" class="nav-item {{ request()->routeIs('companies.*') ? 'active' : '' }}">
            <span class="icon">🏢</span> {{ __('Companies') }}
        </a>
        <a href="{{ route('analyses.index') }}" class="nav-item {{ request()->routeIs('analyses.*') ? 'active' : '' }}">
            <span class="icon">📋</span> {{ __('All Analyses') }}
        </a>

        <div class="nav-label" style="margin-top:16px">{{ __('Analysis Tools') }}</div>
        <a href="{{ route('gmbh.index') }}" class="nav-item {{ request()->routeIs('gmbh.*') ? 'active' : '' }}">
            <span class="icon">📊</span> {{ __('GmbH Analysis') }}
        </a>
        <a href="{{ route('jahresabschluss.index') }}" class="nav-item {{ request()->routeIs('jahresabschluss.*') ? 'active' : '' }}">
            <span class="icon">📈</span> {{ __('Financial Statement') }}
        </a>
        <a href="{{ route('immobilien.index') }}" class="nav-item {{ request()->routeIs('immobilien.*') ? 'active' : '' }}">
            <span class="icon">🏘</span> {{ __('Real Estate Analysis') }}
        </a>

        <div class="nav-label" style="margin-top:16px;">{{ __('Leads & Payments') }}</div>
        <a href="{{ route('leads.index') }}" class="nav-item {{ request()->routeIs('leads.*') ? 'active' : '' }}">
            <span class="icon">👤</span> {{ __('Leads') }}
        </a>
        <a href="{{ route('paypal.index') }}" class="nav-item {{ request()->routeIs('paypal.*') ? 'active' : '' }}">
            <span class="icon">💳</span> {{ __('Payments') }}
        </a>

        <div class="nav-label" style="margin-top:16px;">{{ __('Import & Tools') }}</div>
        <a href="{{ route('import.index') }}" class="nav-item {{ request()->routeIs('import.*') ? 'active' : '' }}">
            <span class="icon">📥</span> {{ __('Excel Import') }}
        </a>

        @if(Auth::user()->hasRole('Admin'))
        <div class="nav-label" style="margin-top:16px;">{{ __('Administration') }}</div>
        <a href="{{ route('admin.index') }}" class="nav-item {{ request()->routeIs('admin.*') ? 'active' : '' }}"
            style="color:#f87171; border-left: 2px solid rgba(220,38,38,0.3); border-radius:0 8px 8px 0;">
            <span class="icon">🛡</span> {{ __('Admin Panel') }}
        </a>
        @endif
    </nav>

    <div class="sidebar-footer">
        <div class="user-card">
            <div class="user-avatar">{{ substr(Auth::user()->name, 0, 1) }}</div>
            <div>
                <div class="user-name">{{ Auth::user()->name }}</div>
                <div class="user-role">{{ Auth::user()->getRoleNames()->first() ?? 'User' }}</div>
            </div>
        </div>
        <form method="POST" action="{{ route('logout') }}" style="margin-top:8px">
            @csrf
            <button type="submit" class="btn btn-secondary" style="width:100%; justify-content:center; font-size:12px;">
                🚪 {{ __('Log out') }}
            </button>
        </form>
    </div>
</aside>

<div class="main">
    <div class="topbar">
        <button type="button" class="menu-toggle" id="menuToggle" aria-label="{{ __('Navigation umschalten') }}">☰</button>
        <div class="topbar-title">@yield('page-title', __('Dashboard'))</div>
        <div class="topbar-actions">
            <div class="lang-switcher" style="display:flex; gap:6px; align-items:center;">
                <a href="{{ route('locale.switch', 'de') }}" class="btn btn-sm {{ app()->getLocale() === 'de' ? 'btn-primary' : 'btn-secondary' }}">DE</a>
                <a href="{{ route('locale.switch', 'en') }}" class="btn btn-sm {{ app()->getLocale() === 'en' ? 'btn-primary' : 'btn-secondary' }}">EN</a>
            </div>
            @yield('topbar-actions')
        </div>
    </div>

    <div class="page">
        @if(session('success'))
            <div class="alert alert-success">✅ {{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-error">❌ {{ session('error') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-error">❌ {{ $errors->first() }}</div>
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
