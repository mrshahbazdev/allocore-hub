<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('title', config('app.name', 'Allocore'))</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            body {
                background: #0b1021;
                color: #e2e8f0;
                font-family: 'Inter', ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, sans-serif;
            }
            .guest-card {
                background: rgba(15, 23, 42, 0.78);
                border: 1px solid rgba(99, 102, 241, 0.18);
                backdrop-filter: blur(10px);
                border-radius: 16px;
                box-shadow: 0 24px 80px rgba(0,0,0,0.45);
            }
            .guest-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 16px 24px;
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                z-index: 50;
            }
            .guest-logo {
                font-size: 20px;
                font-weight: 700;
                color: #e2e8f0;
                text-decoration: none;
                display: flex;
                align-items: center;
                gap: 8px;
            }
            .guest-logo span {
                color: #818cf8;
            }
            .lang-switcher a {
                padding: 5px 10px;
                border-radius: 6px;
                font-size: 12px;
                font-weight: 600;
                text-decoration: none;
                color: #94a3b8;
                border: 1px solid rgba(255,255,255,0.08);
                transition: all .2s;
            }
            .lang-switcher a:hover, .lang-switcher a.active {
                background: rgba(99,102,241,0.15);
                color: #e2e8f0;
                border-color: rgba(99,102,241,0.35);
            }
        </style>
    </head>
    <body class="font-sans text-slate-100 antialiased">
        <div class="guest-header">
            <a href="/" class="guest-logo">
                ⬡ Allocore <span>Financial</span>
            </a>

            <div class="lang-switcher">
                <a href="{{ route('locale.switch', 'de') }}" class="{{ app()->getLocale() === 'de' ? 'active' : '' }}">DE</a>
                <a href="{{ route('locale.switch', 'en') }}" class="{{ app()->getLocale() === 'en' ? 'active' : '' }}">EN</a>
            </div>
        </div>

        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-24 pb-12 px-4">
            <div class="w-full max-w-md guest-card p-8 sm:p-10">
                {{ $slot }}
            </div>

            <p class="mt-6 text-xs text-slate-500">
                © {{ date('Y') }} Allocore. {{ __('welcome.footer.rights') }}
            </p>
        </div>
    </body>
</html>
