<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      class="h-full"
      x-data="{ sidebarOpen: window.innerWidth >= 768 }"
      x-init="window.addEventListener('resize', () => { sidebarOpen = window.innerWidth >= 768; })">
<head>
    <script>
        (function() {
            if (localStorage.getItem('krd-dark') === 'true') {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Vendor Portal' }} — Koordli</title>
    <meta name="robots" content="noindex, nofollow">
    <style>
        @font-face { font-family: 'Satoshi'; src: url('/fonts/Satoshi-Variable.woff2') format('woff2'); font-weight: 300 900; font-display: swap; }
        @font-face { font-family: 'Satoshi'; src: url('/fonts/Satoshi-VariableItalic.woff2') format('woff2'); font-weight: 300 900; font-display: swap; font-style: italic; }
    </style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="krd-body h-full" x-cloak>

    <div id="krd-toast-container" class="fixed top-4 right-4 z-50 flex flex-col gap-2 pointer-events-none"></div>

    <div class="krd-sidebar-overlay"
         x-bind:class="{ 'active': sidebarOpen && window.innerWidth < 768 }"
         x-on:click="sidebarOpen = false"></div>

    <div class="krd-shell">
        <aside class="krd-sidebar" id="krd-sidebar"
            x-bind:class="{
                'krd-sidebar--collapsed': !sidebarOpen && window.innerWidth >= 768,
                'open': sidebarOpen && window.innerWidth < 768
            }">
            @include('components.layout.vendor-sidebar')
        </aside>

        <div class="krd-main" x-bind:class="{ 'krd-main--expanded': !sidebarOpen }">
            <header class="krd-topbar">
                @include('components.layout.vendor-topbar')
            </header>
            <main class="krd-content">
                {{ $slot ?? '' }}
                @yield('content')
            </main>
        </div>
    </div>

    @livewireScripts
</body>
</html>