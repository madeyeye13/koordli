<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    @include('partials.favicon')
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ $siteName ?? 'Koordli' }} | Event Operations Platform for Modern Event Businesses</title>
    <meta name="description" content="Koordli is the operating system for event companies. Manage clients, vendors, contracts, runsheets, RSVP, and payments, all in one place.">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ url('/') }}">

    {{-- Open Graph --}}
    <meta property="og:title" content="{{ $siteName ?? 'Koordli' }} | Event Operations Platform">
    <meta property="og:description" content="The operating system for event companies. Manage clients, vendors, contracts, runsheets, RSVP, and payments, all in one place.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url('/') }}">
    <meta property="og:image" content="{{ asset('images/logoonwhite.png') }}">

    {{-- Twitter --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $siteName ?? 'Koordli' }} | Event Operations Platform">
    <meta name="twitter:description" content="The operating system for event companies.">
    <meta name="twitter:image" content="{{ asset('images/logoonwhite.png') }}">

        <style>
        @font-face { font-family: 'Satoshi'; src: url('/fonts/Satoshi-Variable.woff2') format('woff2'); font-weight: 300 900; font-display: swap; }
        @font-face { font-family: 'Satoshi'; src: url('/fonts/Satoshi-VariableItalic.woff2') format('woff2'); font-weight: 300 900; font-display: swap; font-style: italic; }

        /* Shared design tokens + nav styles: required by BOTH the real
           landing page (which also defines its own copy internally) AND
           every page using this layout directly (blog index/show), since
           those pages have no other source for these variables/classes. */
        :root {
            --lp-bg: #FAFAF9; --lp-bg-alt: #F5F5F4; --lp-text: #1C1917;
            --lp-text-muted: #57534E; --lp-text-faint: #78716C; --lp-border: #E7E5E4;
            --lp-card-bg: #ffffff; --lp-accent: #7C3AED; --lp-accent-bg: #F5F3FF; --lp-accent-border: #DDD6FE;
        }
        .dark {
            --lp-bg: #0C0A09; --lp-bg-alt: #1C1917; --lp-text: #FAFAF9;
            --lp-text-muted: #D6D3D1; --lp-text-faint: #A8A29E; --lp-border: #292524;
            --lp-card-bg: #1C1917; --lp-accent: #A78BFA; --lp-accent-bg: #2E1065; --lp-accent-border: #4C1D95;
        }
        body { font-family: 'Satoshi', sans-serif; background: var(--lp-bg); color: var(--lp-text); transition: background 200ms, color 200ms; }

        .lp-nav { position: fixed; top: 0; left: 0; right: 0; z-index: 50; background: color-mix(in srgb, var(--lp-bg) 88%, transparent); backdrop-filter: blur(14px); border-bottom: 1px solid var(--lp-border); }
        .lp-nav-inner { display: flex; align-items: center; justify-content: space-between; padding: 14px 20px; max-width: 1180px; margin: 0 auto; gap: 12px; }
        .lp-logo { display: inline-flex; align-items: center; gap: 9px; color: var(--lp-text); text-decoration: none; font-family: 'Fraunces', serif; font-size: 20px; font-weight: 600; letter-spacing: -0.02em; }
        .lp-logo-mark { width: 28px; height: 28px; object-fit: contain; border-radius: 7px; flex-shrink: 0; }
        .lp-logo-fallback { display: block; background: var(--lp-accent); transform: rotate(24deg); }
        .lp-nav-links { display: flex; gap: 28px; align-items: center; }
        .lp-nav-link { font-size: 14px; color: var(--lp-text-muted); text-decoration: none; font-weight: 500; }
        .lp-nav-link:hover { color: var(--lp-text); }
        .lp-nav-cta { display: flex; gap: 10px; align-items: center; flex-shrink: 0; }
        .lp-theme-toggle { background: none; border: 1.5px solid var(--lp-border); border-radius: 8px; width: 38px; height: 38px; display: flex; align-items: center; justify-content: center; cursor: pointer; color: var(--lp-text-muted); flex-shrink: 0; }
        .lp-menu-toggle { display: none; background: none; border: 1.5px solid var(--lp-border); border-radius: 8px; width: 40px; height: 40px; align-items: center; justify-content: center; cursor: pointer; color: var(--lp-text); }
        .lp-mobile-menu { display: none; }

        .lp-btn { display: inline-flex; align-items: center; justify-content: center; gap: 6px; padding: 12px 22px; border-radius: 8px; font-size: 14px; font-weight: 600; text-decoration: none; cursor: pointer; border: none; transition: all 150ms; white-space: nowrap; }
        .lp-btn-primary { background: var(--lp-text); color: var(--lp-bg); }
        .lp-btn-primary:hover { opacity: 0.85; }
        .lp-btn-secondary { background: transparent; color: var(--lp-text); border: 1.5px solid var(--lp-border); }
        .lp-btn-secondary:hover { border-color: var(--lp-text); }
        .lp-btn-sm-mobile { padding: 9px 14px; font-size: 12px; }
        .lp-nav-cta .lp-btn-primary { background: #7C3AED; color: #fff; border-color: #7C3AED; }
        .lp-nav-cta .lp-btn-primary:hover { background: #6D28D9; border-color: #6D28D9; opacity: 1; }
        [x-cloak] { display: none !important; }

        @media (max-width: 767px) {
            .lp-nav-links { display: none; }
            .lp-nav-cta { display: none; }
            .lp-menu-toggle { display: flex; }
            .lp-nav.menu-open .lp-mobile-menu { display: flex; }
            .lp-mobile-menu { flex-direction: column; gap: 4px; padding: 10px 16px 16px; border-top: 1px solid var(--lp-border); background: var(--lp-bg); }
            .lp-mobile-menu-link { display: block; padding: 11px 4px; color: var(--lp-text-muted); text-decoration: none; font-size: 14px; font-weight: 500; }
            .lp-mobile-menu-link:hover { color: var(--lp-text); }
            .lp-mobile-menu-actions { display: flex; align-items: center; gap: 10px; margin-top: 6px; padding-top: 12px; border-top: 1px solid var(--lp-border); }
            .lp-mobile-menu-actions .lp-theme-toggle { display: flex; }
            .lp-mobile-menu-actions .lp-btn { flex: 1; }
        }
    </style>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@40..144,300..400&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @stack('head')
</head>
<body class="krd-body h-full" style="background:#FAFAF9;">
    {{ $slot ?? '' }}
    @yield('content')
    @livewireScripts
</body>
</html>