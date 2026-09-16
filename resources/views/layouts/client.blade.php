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

    <title>{{ $title ?? 'My Event' }} — Koordli</title>
    <meta name="robots" content="noindex, nofollow">

    <style>
        @font-face {
            font-family: 'Satoshi';
            src: url('/fonts/Satoshi-Variable.woff2') format('woff2');
            font-weight: 300 900;
            font-display: swap;
        }
        @font-face {
            font-family: 'Satoshi';
            src: url('/fonts/Satoshi-VariableItalic.woff2') format('woff2');
            font-weight: 300 900;
            font-display: swap;
            font-style: italic;
        }
    </style>

                @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    {{-- PWA --}}
    <link rel="manifest" href="{{ route('pwa.manifest') }}">
    <link rel="apple-touch-icon" href="{{ route('pwa.icon', ['size' => 180]) }}">

    <script>
        window.__currentUserId = {{ auth('client')->id() ?? 'null' }};
        window.__currentUserName = @js(auth('client')->user()->name ?? '');
        window.__tenantWhiteLabel = {{ (auth('client')->user()?->tenant && app(\App\Services\FeatureGateService::class)->canAccess(auth('client')->user()->tenant, 'white_label')) ? 'true' : 'false' }};
        window.__tenantSlug = @js(auth('client')->user()?->tenant?->slug ?? 'koordli');
        window.__vapidPublicKey = @js(config('services.vapid.public_key'));
    </script>
</head>
<body class="krd-body h-full">

    <div id="krd-toast-container"
         class="fixed top-4 right-4 z-50 flex flex-col gap-2 pointer-events-none">
    </div>

    <div id="krd-client-shell" class="krd-shell"
         x-data="{ sidebarOpen: window.innerWidth >= 768 }"
         x-init="window.addEventListener('resize', () => { sidebarOpen = window.innerWidth >= 768; })">

        <div class="krd-sidebar-overlay"
             x-bind:class="{ 'active': sidebarOpen && window.innerWidth < 768 }"
             x-on:click="sidebarOpen = false">
        </div>

        {{-- Sidebar --}}
        <aside class="krd-sidebar"
            id="krd-client-sidebar"
            x-bind:class="{
                'krd-sidebar--collapsed': !sidebarOpen && window.innerWidth >= 768,
                'open': sidebarOpen && window.innerWidth < 768
            }">
            @include('components.layout.client-sidebar')
        </aside>

        <div class="krd-main" x-bind:class="{ 'krd-main--expanded': !sidebarOpen }">

            <header class="krd-topbar">
                @include('components.layout.client-topbar')
            </header>

            <main class="krd-content">
                {{ $slot ?? '' }}
                @yield('content')
            </main>

        </div>

    </div>

    <livewire:client.conversations.floating-conversations-widget />
    @livewireScripts
</body>
</html>