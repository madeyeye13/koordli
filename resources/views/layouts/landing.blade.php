<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    @include('partials.favicon')
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ $siteName ?? 'Koordli' }} — Event Operations Platform for Modern Event Businesses</title>
    <meta name="description" content="Koordli is the operating system for event companies. Manage clients, vendors, contracts, runsheets, RSVP, and payments — all in one place.">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ url('/') }}">

    {{-- Open Graph --}}
    <meta property="og:title" content="{{ $siteName ?? 'Koordli' }} — Event Operations Platform">
    <meta property="og:description" content="The operating system for event companies. Manage clients, vendors, contracts, runsheets, RSVP, and payments — all in one place.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url('/') }}">
    <meta property="og:image" content="{{ asset('images/logoonwhite.png') }}">

    {{-- Twitter --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $siteName ?? 'Koordli' }} — Event Operations Platform">
    <meta name="twitter:description" content="The operating system for event companies.">
    <meta name="twitter:image" content="{{ asset('images/logoonwhite.png') }}">

    <style>
        @font-face { font-family: 'Satoshi'; src: url('/fonts/Satoshi-Variable.woff2') format('woff2'); font-weight: 300 900; font-display: swap; }
        @font-face { font-family: 'Satoshi'; src: url('/fonts/Satoshi-VariableItalic.woff2') format('woff2'); font-weight: 300 900; font-display: swap; font-style: italic; }
        @font-face { font-family: 'Fraunces'; src: url('https://fonts.gstatic.com/s/fraunces/v31/6NUM8FyLNQOQZAnv9bYEvDiIdVQ.woff2') format('woff2'); font-weight: 400 700; }
    </style>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600;9..144,700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="krd-body h-full" style="background:#FAFAF9;">
    {{ $slot ?? '' }}
    @yield('content')
    @livewireScripts
</body>
</html>