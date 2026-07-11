<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Allocore Financial Platform') }} — {{ __('welcome.hero.badge') }}</title>
    <meta name="description" content="{{ __('Professional financial analysis for GmbH, financial statements and real estate.') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --indigo: #6366f1;
            --indigo-dark: #4f46e5;
            --violet: #8b5cf6;
            --emerald: #10b981;
            --amber: #f59e0b;
            --rose: #ef4444;
            --bg: #080c18;
            --surface: #0f172a;
            --surface2: #1e1b4b;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg);
            color: #e2e8f0;
            overflow-x: hidden;
        }

        /* ─── Noise texture overlay ──────────────────────── */
        body::before {
            content: '';
            position: fixed; inset: 0; z-index: 0;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noise'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noise)' opacity='0.04'/%3E%3C/svg%3E");
            pointer-events: none;
        }

        /* ─── Ambient background glows ───────────────────── */
        .bg-glow {
            position: fixed; border-radius: 50%;
            filter: blur(120px); pointer-events: none; z-index: 0;
        }
        .glow-1 { width: 600px; height: 600px; top: -200px; left: -150px; background: rgba(99,102,241,0.15); }
        .glow-2 { width: 500px; height: 500px; top: 30%; right: -150px; background: rgba(139,92,246,0.1); }
        .glow-3 { width: 400px; height: 400px; bottom: 10%; left: 30%; background: rgba(16,185,129,0.07); }

        /* ─── Shared wrapper ─────────────────────────────── */
        .wrap { position: relative; z-index: 1; max-width: 1200px; margin: 0 auto; padding: 0 32px; }

        /* ─── NAV ────────────────────────────────────────── */
        nav {
            position: fixed; top: 0; left: 0; right: 0; z-index: 100;
            border-bottom: 1px solid rgba(255,255,255,0.05);
            background: rgba(8,12,24,0.85); backdrop-filter: blur(16px);
        }
        .nav-inner {
            max-width: 1200px; margin: 0 auto; padding: 0 32px;
            height: 64px; display: flex; align-items: center; gap: 16px;
        }
        .nav-logo {
            font-size: 20px; font-weight: 800; text-decoration: none;
            background: linear-gradient(135deg, #818cf8, #c084fc);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
            letter-spacing: -0.5px;
        }
        .nav-logo span { font-weight: 300; opacity: .6; }
        .nav-spacer { flex: 1; }
        .nav-links { display: flex; gap: 8px; align-items: center; }
        .nav-link {
            padding: 8px 16px; border-radius: 8px; font-size: 13px; font-weight: 500;
            text-decoration: none; color: #94a3b8; transition: all .15s;
        }
        .nav-link:hover { color: #e2e8f0; background: rgba(255,255,255,0.06); }
        .nav-cta {
            padding: 9px 20px; border-radius: 8px; font-size: 13px; font-weight: 600;
            text-decoration: none; background: var(--indigo); color: white;
            transition: all .15s; box-shadow: 0 0 20px rgba(99,102,241,0.3);
        }
        .nav-cta:hover { background: var(--indigo-dark); box-shadow: 0 0 30px rgba(99,102,241,0.5); transform: translateY(-1px); }

        /* ─── HERO ───────────────────────────────────────── */
        .hero {
            min-height: 100vh; display: flex; align-items: center;
            padding: 120px 0 80px;
        }
        .hero-badge {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 6px 14px; border-radius: 20px; font-size: 12px; font-weight: 500;
            background: rgba(99,102,241,0.12); border: 1px solid rgba(99,102,241,0.3);
            color: #818cf8; margin-bottom: 28px;
        }
        .hero-badge::before {
            content: ''; width: 6px; height: 6px; border-radius: 50%;
            background: var(--indigo); animation: pulse 2s infinite;
        }
        @keyframes pulse { 0%,100% { opacity:1; transform:scale(1); } 50% { opacity:.5; transform:scale(1.3); } }

        .hero h1 {
            font-size: clamp(40px, 6vw, 72px); font-weight: 800; line-height: 1.08;
            letter-spacing: -2px; color: #f1f5f9; margin-bottom: 24px;
        }
        .hero h1 .grad {
            background: linear-gradient(135deg, #818cf8 0%, #c084fc 50%, #f0abfc 100%);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
        }
        .hero-sub {
            font-size: 18px; color: #64748b; line-height: 1.7; max-width: 560px;
            margin-bottom: 40px; font-weight: 400;
        }
        .hero-actions { display: flex; gap: 12px; flex-wrap: wrap; margin-bottom: 64px; }
        .btn-hero-primary {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 14px 28px; border-radius: 10px; font-size: 15px; font-weight: 600;
            text-decoration: none; background: var(--indigo); color: white;
            box-shadow: 0 4px 24px rgba(99,102,241,0.35); transition: all .2s;
        }
        .btn-hero-primary:hover { background: var(--indigo-dark); transform: translateY(-2px); box-shadow: 0 8px 32px rgba(99,102,241,0.45); }
        .btn-hero-secondary {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 14px 28px; border-radius: 10px; font-size: 15px; font-weight: 500;
            text-decoration: none; color: #94a3b8; border: 1px solid rgba(255,255,255,0.1);
            background: rgba(255,255,255,0.04); transition: all .2s;
        }
        .btn-hero-secondary:hover { background: rgba(255,255,255,0.08); color: #e2e8f0; }

        /* Stats bar */
        .hero-stats {
            display: flex; gap: 40px; padding-top: 32px;
            border-top: 1px solid rgba(255,255,255,0.07);
        }
        .stat-item { display: flex; flex-direction: column; gap: 4px; }
        .stat-num { font-size: 28px; font-weight: 700; color: #f1f5f9; }
        .stat-label { font-size: 12px; color: #475569; font-weight: 500; }

        /* Hero preview card */
        .hero-preview {
            flex-shrink: 0; width: 480px;
            background: rgba(15,23,42,0.8); border: 1px solid rgba(99,102,241,0.2);
            border-radius: 16px; padding: 24px;
            box-shadow: 0 24px 80px rgba(0,0,0,0.5), 0 0 0 1px rgba(255,255,255,0.05);
            backdrop-filter: blur(12px);
        }
        .preview-header {
            display: flex; align-items: center; gap: 8px;
            padding-bottom: 16px; border-bottom: 1px solid rgba(255,255,255,0.06);
            margin-bottom: 20px;
        }
        .preview-dot { width: 10px; height: 10px; border-radius: 50%; }
        .preview-score {
            text-align: center; padding: 20px 0;
            border: 1px solid rgba(99,102,241,0.12);
            border-radius: 12px; margin-bottom: 16px;
            background: rgba(99,102,241,0.05);
        }
        .preview-score-num { font-size: 52px; font-weight: 800; color: #10b981; line-height: 1; }
        .preview-score-label { font-size: 12px; color: #64748b; margin-top: 4px; }
        .preview-kpi { display: flex; align-items: center; gap: 10px; padding: 10px 0; border-bottom: 1px solid rgba(255,255,255,0.05); font-size: 13px; }
        .preview-kpi:last-child { border-bottom: none; }
        .kpi-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
        .kpi-name { flex: 1; color: #94a3b8; }
        .kpi-val { font-weight: 600; color: #e2e8f0; }
        .kpi-badge { font-size: 10px; padding: 2px 7px; border-radius: 10px; font-weight: 500; }
        .green { background: rgba(16,185,129,0.15); color: #34d399; }
        .yellow { background: rgba(245,158,11,0.15); color: #fbbf24; }
        .red-b { background: rgba(239,68,68,0.15); color: #f87171; }

        /* Layout row */
        .hero-content { display: flex; gap: 64px; align-items: center; }
        .hero-left { flex: 1; }

        /* ─── TOOLS SECTION ──────────────────────────────── */
        .section { padding: 100px 0; }
        .section-label {
            font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 2px;
            color: var(--indigo); margin-bottom: 12px;
        }
        .section-title { font-size: clamp(28px, 4vw, 44px); font-weight: 800; color: #f1f5f9; line-height: 1.1; letter-spacing: -1px; margin-bottom: 16px; }
        .section-sub { font-size: 16px; color: #64748b; max-width: 560px; line-height: 1.7; }
        .section-head { margin-bottom: 56px; }

        .tools-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; }

        .tool-card {
            background: rgba(15,23,42,0.6); border: 1px solid rgba(255,255,255,0.07);
            border-radius: 16px; padding: 28px; transition: all .25s; cursor: default;
            position: relative; overflow: hidden;
        }
        .tool-card::before {
            content: ''; position: absolute; inset: 0; border-radius: 16px;
            opacity: 0; transition: opacity .3s;
        }
        .tool-card.t1::before { background: radial-gradient(circle at top left, rgba(99,102,241,0.1), transparent 60%); }
        .tool-card.t2::before { background: radial-gradient(circle at top left, rgba(245,158,11,0.08), transparent 60%); }
        .tool-card.t3::before { background: radial-gradient(circle at top left, rgba(139,92,246,0.1), transparent 60%); }
        .tool-card:hover::before { opacity: 1; }
        .tool-card:hover { transform: translateY(-4px); border-color: rgba(99,102,241,0.3); box-shadow: 0 20px 60px rgba(0,0,0,.4); }

        .tool-icon {
            width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center;
            justify-content: center; font-size: 22px; margin-bottom: 20px;
        }
        .t1 .tool-icon { background: rgba(99,102,241,0.15); }
        .t2 .tool-icon { background: rgba(245,158,11,0.12); }
        .t3 .tool-icon { background: rgba(139,92,246,0.15); }

        .tool-title { font-size: 18px; font-weight: 700; color: #f1f5f9; margin-bottom: 10px; }
        .tool-desc { font-size: 13px; color: #64748b; line-height: 1.7; margin-bottom: 20px; }
        .tool-kpis { display: flex; flex-wrap: wrap; gap: 6px; }
        .kpi-chip {
            font-size: 11px; padding: 3px 9px; border-radius: 8px; font-weight: 500;
            background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.08);
            color: #94a3b8;
        }
        .tool-footer { margin-top: 20px; padding-top: 16px; border-top: 1px solid rgba(255,255,255,0.06); font-size: 12px; color: #475569; }

        /* ─── FEATURE GRID ───────────────────────────────── */
        .features-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; }
        .feature-card {
            padding: 24px; border-radius: 12px;
            background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.06);
            transition: all .2s;
        }
        .feature-card:hover { background: rgba(255,255,255,0.05); border-color: rgba(99,102,241,0.2); }
        .feature-icon { font-size: 24px; margin-bottom: 12px; }
        .feature-title { font-size: 14px; font-weight: 600; color: #e2e8f0; margin-bottom: 6px; }
        .feature-desc { font-size: 13px; color: #475569; line-height: 1.6; }

        /* ─── HOW IT WORKS ───────────────────────────────── */
        .steps { display: flex; flex-direction: column; gap: 0; position: relative; }
        .steps::before {
            content: '';
            position: absolute; left: 23px; top: 0; bottom: 0; width: 1px;
            background: linear-gradient(to bottom, transparent, rgba(99,102,241,0.3), transparent);
        }
        .step { display: flex; gap: 24px; align-items: flex-start; padding: 28px 0; }
        .step-num {
            width: 46px; height: 46px; border-radius: 50%; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center;
            border: 1px solid rgba(99,102,241,0.4); background: rgba(99,102,241,0.1);
            font-size: 16px; font-weight: 700; color: #818cf8;
            position: relative; z-index: 1;
        }
        .step-content { padding-top: 8px; }
        .step-title { font-size: 16px; font-weight: 600; color: #f1f5f9; margin-bottom: 6px; }
        .step-desc { font-size: 13px; color: #64748b; line-height: 1.7; }

        /* ─── CTA SECTION ────────────────────────────────── */
        .cta-section {
            margin: 0 0 100px;
            padding: 64px; border-radius: 24px;
            background: linear-gradient(135deg, rgba(99,102,241,0.12) 0%, rgba(139,92,246,0.1) 100%);
            border: 1px solid rgba(99,102,241,0.2);
            text-align: center; position: relative; overflow: hidden;
        }
        .cta-section::before {
            content: ''; position: absolute; top: -80px; left: 50%; transform: translateX(-50%);
            width: 400px; height: 300px; border-radius: 50%;
            background: rgba(99,102,241,0.12); filter: blur(60px); pointer-events: none;
        }
        .cta-section h2 { font-size: 40px; font-weight: 800; color: #f1f5f9; margin-bottom: 12px; letter-spacing: -1px; }
        .cta-section p { font-size: 16px; color: #64748b; margin-bottom: 36px; }
        .cta-actions { display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; }

        /* ─── FOOTER ─────────────────────────────────────── */
        footer {
            border-top: 1px solid rgba(255,255,255,0.06);
            padding: 40px 0;
        }
        .footer-inner {
            max-width: 1200px; margin: 0 auto; padding: 0 32px;
            display: flex; align-items: center; justify-content: space-between;
            flex-wrap: wrap; gap: 16px;
        }
        .footer-logo { font-size: 16px; font-weight: 700; color: #475569; }
        .footer-copy { font-size: 12px; color: #334155; }
        .footer-links { display: flex; gap: 20px; }
        .footer-links a { font-size: 12px; color: #475569; text-decoration: none; }
        .footer-links a:hover { color: #94a3b8; }

        /* ─── Divider ─────────────────────────────────────── */
        .divider {
            height: 1px; background: linear-gradient(to right, transparent, rgba(99,102,241,0.2), transparent);
            margin: 0;
        }

        /* ─── Responsive ─────────────────────────────────── */
        @media (max-width: 1024px) {
            .hero-content { flex-direction: column; gap: 40px; }
            .hero-preview { width: 100%; max-width: 500px; }
            .tools-grid { grid-template-columns: 1fr; }
            .features-grid { grid-template-columns: 1fr 1fr; }
        }
        @media (max-width: 640px) {
            .wrap { padding: 0 20px; }
            .features-grid { grid-template-columns: 1fr; }
            .hero-stats { flex-wrap: wrap; gap: 20px; }
            .cta-section { padding: 40px 24px; }
        }

        /* ─── Scroll animations ───────────────────────────── */
        .fade-up {
            opacity: 0; transform: translateY(30px);
            transition: opacity .6s ease, transform .6s ease;
        }
        .fade-up.visible { opacity: 1; transform: translateY(0); }
    </style>
</head>
<body>

<!-- Background glows -->
<div class="bg-glow glow-1"></div>
<div class="bg-glow glow-2"></div>
<div class="bg-glow glow-3"></div>

<!-- ──────────────── NAV ──────────────── -->
<nav>
    <div class="nav-inner">
        <a href="/" class="nav-logo">⬡ Allocore <span>Financial</span></a>
        <div class="nav-spacer"></div>
        <div class="nav-links">
            <a href="#tools" class="nav-link">{{ __('welcome.nav.tools') }}</a>
            <a href="#features" class="nav-link">{{ __('welcome.nav.features') }}</a>
            <a href="#how" class="nav-link">{{ __('welcome.nav.how') }}</a>
            @if(Route::has('login'))
                @auth
                    <a href="{{ url('/dashboard') }}" class="nav-link">{{ __('Dashboard') }} →</a>
                @else
                    <a href="{{ route('login') }}" class="nav-link">{{ __('Log in') }}</a>
                    <a href="{{ route('register') }}" class="nav-cta">{{ __('welcome.hero.cta_primary') }}</a>
                @endauth
            @endif
            <div class="lang-switcher" style="display:flex; gap:6px; align-items:center; margin-left:8px;">
                <a href="{{ route('locale.switch', 'de') }}" class="nav-link {{ app()->getLocale() === 'de' ? 'active' : '' }}" style="padding:6px 10px; font-weight:600; color: {{ app()->getLocale() === 'de' ? '#e2e8f0' : '#94a3b8' }}">DE</a>
                <a href="{{ route('locale.switch', 'en') }}" class="nav-link {{ app()->getLocale() === 'en' ? 'active' : '' }}" style="padding:6px 10px; font-weight:600; color: {{ app()->getLocale() === 'en' ? '#e2e8f0' : '#94a3b8' }}">EN</a>
            </div>
        </div>
    </div>
</nav>

<!-- ──────────────── HERO ──────────────── -->
<section class="hero">
    <div class="wrap">
        <div class="hero-content">

            <div class="hero-left">
                <div class="hero-badge">{{ __('welcome.hero.badge') }}</div>

                <h1>
                    {!! __('welcome.hero.title') !!}
                </h1>

                <p class="hero-sub">
                    {{ __('welcome.hero.subtitle') }}
                </p>

                <div class="hero-actions">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="btn-hero-primary">
                            → {{ __('Dashboard') }}
                        </a>
                    @else
                        <a href="{{ route('register') }}" class="btn-hero-primary">
                            🚀 {{ __('welcome.hero.cta_primary') }}
                        </a>
                        <a href="{{ route('login') }}" class="btn-hero-secondary">
                            {{ __('welcome.hero.cta_secondary') }}
                        </a>
                    @endauth
                </div>

                <div class="hero-stats">
                    <div class="stat-item">
                        <span class="stat-num">3</span>
                        <span class="stat-label">{{ __('welcome.hero.stat_tools') }}</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-num">30+</span>
                        <span class="stat-label">{{ __('welcome.hero.stat_kpis') }}</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-num">0–100</span>
                        <span class="stat-label">{{ __('welcome.hero.stat_score') }}</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-num">PDF</span>
                        <span class="stat-label">{{ __('welcome.hero.stat_export') }}</span>
                    </div>
                </div>
            </div>

            <!-- Preview Card -->
            <div class="hero-preview">
                <div class="preview-header">
                    <div class="preview-dot" style="background:#ef4444;"></div>
                    <div class="preview-dot" style="background:#f59e0b;"></div>
                    <div class="preview-dot" style="background:#10b981;"></div>
                    <span style="font-size:12px; color:#475569; margin-left:8px;">GmbH Analyse · Muster GmbH 2024</span>
                </div>

                <div class="preview-score">
                    <div class="preview-score-num">78.5</div>
                    <div class="preview-score-label">/100 Punkte · ✅ Finanzierung empfohlen</div>
                    <div style="margin: 12px 24px 0; height: 6px; background: rgba(255,255,255,0.06); border-radius: 3px; overflow:hidden;">
                        <div style="height:100%; width:78.5%; background: linear-gradient(90deg, #10b981, #34d399); border-radius:3px;"></div>
                    </div>
                </div>

                <div class="preview-kpi">
                    <div class="kpi-dot" style="background:#10b981;"></div>
                    <span class="kpi-name">EBITDA-Marge</span>
                    <span class="kpi-val">18.4%</span>
                    <span class="kpi-badge green">🟢 Gut</span>
                </div>
                <div class="preview-kpi">
                    <div class="kpi-dot" style="background:#10b981;"></div>
                    <span class="kpi-name">Umsatzwachstum</span>
                    <span class="kpi-val">+22.3%</span>
                    <span class="kpi-badge green">🟢 Gut</span>
                </div>
                <div class="preview-kpi">
                    <div class="kpi-dot" style="background:#f59e0b;"></div>
                    <span class="kpi-name">Current Ratio</span>
                    <span class="kpi-val">1.18x</span>
                    <span class="kpi-badge yellow">🟡 Mittel</span>
                </div>
                <div class="preview-kpi">
                    <div class="kpi-dot" style="background:#10b981;"></div>
                    <span class="kpi-name">LTV / CAC Ratio</span>
                    <span class="kpi-val">4.2x</span>
                    <span class="kpi-badge green">🟢 Gut</span>
                </div>
                <div class="preview-kpi">
                    <div class="kpi-dot" style="background:#ef4444;"></div>
                    <span class="kpi-name">Runway (Monate)</span>
                    <span class="kpi-val">4.5 Mo</span>
                    <span class="kpi-badge red-b">🔴 Kritisch</span>
                </div>

                <div style="margin-top:16px; padding:12px; background:rgba(99,102,241,0.08); border-radius:8px; border:1px solid rgba(99,102,241,0.15);">
                    <div style="font-size:11px; font-weight:600; color:#818cf8; margin-bottom:4px;">🎯 Empfehlung</div>
                    <div style="font-size:12px; color:#94a3b8;">Sehr gut — Finanzierung empfohlen. Cash-Runway sollte gestärkt werden.</div>
                </div>
            </div>

        </div>
    </div>
</section>

<div class="divider"></div>

<!-- ──────────────── TOOLS ──────────────── -->
<section class="section" id="tools">
    <div class="wrap">
        <div class="section-head fade-up">
            <div class="section-label">{{ __('welcome.tools.label') }}</div>
            <h2 class="section-title">{{ __('welcome.tools.title') }}</h2>
            <p class="section-sub">{{ __('welcome.tools.subtitle') }}</p>
        </div>

        <div class="tools-grid">
            <!-- GmbH -->
            <div class="tool-card t1 fade-up">
                <div class="tool-icon">📊</div>
                <div class="tool-title">{{ __('welcome.tools.gmbh.title') }}</div>
                <div class="tool-desc">
                    {{ __('welcome.tools.gmbh.desc') }}
                </div>
                <div class="tool-kpis">
                    <span class="kpi-chip">EBITDA-Marge</span>
                    <span class="kpi-chip">Umsatzwachstum</span>
                    <span class="kpi-chip">Debt/Equity</span>
                    <span class="kpi-chip">Current Ratio</span>
                    <span class="kpi-chip">Runway</span>
                    <span class="kpi-chip">LTV/CAC</span>
                </div>
                <div class="tool-footer">📅 {{ __('welcome.tools.gmbh.footer') }}</div>
            </div>

            <!-- Jahresabschluss -->
            <div class="tool-card t2 fade-up">
                <div class="tool-icon">📈</div>
                <div class="tool-title">{{ __('welcome.tools.ja.title') }}</div>
                <div class="tool-desc">
                    {{ __('welcome.tools.ja.desc') }}
                </div>
                <div class="tool-kpis">
                    <span class="kpi-chip">EK-Quote</span>
                    <span class="kpi-chip">Quick Ratio</span>
                    <span class="kpi-chip">ROE / ROA</span>
                    <span class="kpi-chip">EBIT-Marge</span>
                    <span class="kpi-chip">DSO / DPO</span>
                    <span class="kpi-chip">Zinsdeckung</span>
                </div>
                <div class="tool-footer">📅 {{ __('welcome.tools.ja.footer') }}</div>
            </div>

            <!-- Immobilien -->
            <div class="tool-card t3 fade-up">
                <div class="tool-icon">🏘</div>
                <div class="tool-title">{{ __('welcome.tools.imm.title') }}</div>
                <div class="tool-desc">
                    {{ __('welcome.tools.imm.desc') }}
                </div>
                <div class="tool-kpis">
                    <span class="kpi-chip">NOI</span>
                    <span class="kpi-chip">DSCR</span>
                    <span class="kpi-chip">LTV</span>
                    <span class="kpi-chip">Cashflow</span>
                    <span class="kpi-chip">Nettorendite</span>
                    <span class="kpi-chip">Mietmultiplikator</span>
                </div>
                <div class="tool-footer">📅 {{ __('welcome.tools.imm.footer') }}</div>
            </div>
        </div>
    </div>
</section>

<div class="divider"></div>

<!-- ──────────────── FEATURES ──────────────── -->
<section class="section" id="features">
    <div class="wrap">
        <div class="section-head fade-up">
            <div class="section-label">{{ __('welcome.features.label') }}</div>
            <h2 class="section-title">{{ __('welcome.features.title') }}</h2>
        </div>

        <div class="features-grid">
            <div class="feature-card fade-up">
                <div class="feature-icon">🟢🟡🔴</div>
                <div class="feature-title">{{ __('welcome.features.traffic.title') }}</div>
                <div class="feature-desc">{{ __('welcome.features.traffic.desc') }}</div>
            </div>
            <div class="feature-card fade-up">
                <div class="feature-icon">📄</div>
                <div class="feature-title">{{ __('welcome.features.pdf.title') }}</div>
                <div class="feature-desc">{{ __('welcome.features.pdf.desc') }}</div>
            </div>
            <div class="feature-card fade-up">
                <div class="feature-icon">🏢</div>
                <div class="feature-title">{{ __('welcome.features.multi.title') }}</div>
                <div class="feature-desc">{{ __('welcome.features.multi.desc') }}</div>
            </div>
            <div class="feature-card fade-up">
                <div class="feature-icon">📊</div>
                <div class="feature-title">{{ __('welcome.features.access.title') }}</div>
                <div class="feature-desc">{{ __('welcome.features.access.desc') }}</div>
            </div>
            <div class="feature-card fade-up">
                <div class="feature-icon">🔢</div>
                <div class="feature-title">{{ __('welcome.features.score.title') }}</div>
                <div class="feature-desc">{{ __('welcome.features.score.desc') }}</div>
            </div>
            <div class="feature-card fade-up">
                <div class="feature-icon">📋</div>
                <div class="feature-title">{{ __('welcome.features.history.title') }}</div>
                <div class="feature-desc">{{ __('welcome.features.history.desc') }}</div>
            </div>
        </div>
    </div>
</section>

<div class="divider"></div>

<!-- ──────────────── HOW IT WORKS ──────────────── -->
<section class="section" id="how">
    <div class="wrap" style="display:grid; grid-template-columns:1fr 1fr; gap:80px; align-items:center;">
        <div class="fade-up">
            <div class="section-label">{{ __('welcome.how.label') }}</div>
            <h2 class="section-title" style="margin-bottom:40px;">{!! __('welcome.how.title') !!}</h2>
            <div class="steps">
                <div class="step">
                    <div class="step-num">1</div>
                    <div class="step-content">
                        <div class="step-title">{{ __('welcome.how.step1.title') }}</div>
                        <div class="step-desc">{{ __('welcome.how.step1.desc') }}</div>
                    </div>
                </div>
                <div class="step">
                    <div class="step-num">2</div>
                    <div class="step-content">
                        <div class="step-title">{{ __('welcome.how.step2.title') }}</div>
                        <div class="step-desc">{{ __('welcome.how.step2.desc') }}</div>
                    </div>
                </div>
                <div class="step">
                    <div class="step-num">3</div>
                    <div class="step-content">
                        <div class="step-title">{{ __('welcome.how.step3.title') }}</div>
                        <div class="step-desc">{{ __('welcome.how.step3.desc') }}</div>
                    </div>
                </div>
                <div class="step">
                    <div class="step-num">4</div>
                    <div class="step-content">
                        <div class="step-title">{{ __('welcome.how.step4.title') }}</div>
                        <div class="step-desc">{{ __('welcome.how.step4.desc') }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="fade-up" style="background:rgba(15,23,42,0.7); border:1px solid rgba(99,102,241,0.15); border-radius:16px; padding:28px;">
            <div style="font-size:13px; font-weight:600; color:#818cf8; margin-bottom:20px;">📊 Beispiel: Immobilienanalyse</div>
            @foreach([
                ['Kaufpreis','500.000 €','#64748b'],
                ['Nebenkosten','40.000 €','#64748b'],
                ['Eigenkapital','150.000 €','#64748b'],
                ['Darlehen','390.000 €','#818cf8'],
                ['Jahres-NOI','26.400 €','#818cf8'],
                ['Schuldendienst','21.450 €','#f59e0b'],
                ['Cashflow p.a.','+ 4.950 €','#10b981'],
                ['DSCR','1.23x','#f59e0b'],
                ['Nettorendite','4.8%','#10b981'],
                ['Gesamt-Score','62 / 100','#10b981'],
            ] as [$k,$v,$c])
            <div style="display:flex; justify-content:space-between; padding:9px 0; border-bottom:1px solid rgba(255,255,255,0.05); font-size:13px;">
                <span style="color:#64748b;">{{ $k }}</span>
                <span style="font-weight:600; color:{{ $c }};">{{ $v }}</span>
            </div>
            @endforeach
            <div style="margin-top:16px; padding:12px; background:rgba(16,185,129,0.08); border:1px solid rgba(16,185,129,0.2); border-radius:8px; font-size:12px; color:#34d399;">
                ✅ Solides Investment — mit Auflagen empfehlenswert
            </div>
        </div>
    </div>
</section>

<div class="divider"></div>

<!-- ──────────────── CTA ──────────────── -->
<section style="padding: 100px 0 0;">
    <div class="wrap">
        <div class="cta-section fade-up">
            <h2>{{ __('welcome.cta.title') }}</h2>
            <p>{{ __('welcome.cta.subtitle') }}</p>
            <div class="cta-actions">
                @auth
                    <a href="{{ url('/dashboard') }}" class="btn-hero-primary" style="font-size:14px;">→ {{ __('Dashboard') }}</a>
                @else
                    <a href="{{ route('register') }}" class="btn-hero-primary" style="font-size:14px;">🚀 {{ __('welcome.cta.primary') }}</a>
                    <a href="{{ route('login') }}" class="btn-hero-secondary" style="font-size:14px;">{{ __('Log in') }}</a>
                @endauth
            </div>
        </div>
    </div>
</section>

<!-- ──────────────── FOOTER ──────────────── -->
<footer>
    <div class="footer-inner">
        <div class="footer-logo">⬡ Allocore Financial Platform</div>
        <div class="footer-copy">© {{ date('Y') }} Allocore. {{ __('welcome.footer.rights') }}</div>
        <div class="footer-links">
            @if(Route::has('login'))
                <a href="{{ route('login') }}">{{ __('Log in') }}</a>
                <a href="{{ route('register') }}">{{ __('Register') }}</a>
            @endif
        </div>
    </div>
</footer>

<script>
// Scroll fade-in animations
const observer = new IntersectionObserver((entries) => {
    entries.forEach((e, i) => {
        if (e.isIntersecting) {
            setTimeout(() => e.target.classList.add('visible'), i * 80);
        }
    });
}, { threshold: 0.1 });
document.querySelectorAll('.fade-up').forEach(el => observer.observe(el));

// Smooth anchor scroll
document.querySelectorAll('a[href^="#"]').forEach(a => {
    a.addEventListener('click', e => {
        e.preventDefault();
        const t = document.querySelector(a.getAttribute('href'));
        if (t) t.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
});
</script>
</body>
</html>
