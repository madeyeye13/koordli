<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      class="h-full"
      x-data="{ sidebarOpen: window.innerWidth >= 768 }"
      x-init="window.addEventListener('resize', () => { sidebarOpen = window.innerWidth >= 768; })">
<head>

    {{-- Prevent dark mode flash — must be first in head --}}
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
    @include('partials.favicon')

    {{-- SEO --}}
    <title>{{ $title ?? 'Dashboard' }} — {{ config('app.name') }}</title>
    <meta name="robots" content="noindex, nofollow">

    {{-- Tenant Branding --}}
    @if(app()->bound('tenant'))
        @php $tenant = app('tenant'); @endphp
        @if($tenant->branding)
        <style>
            :root {
                --tenant-primary: {{ $tenant->branding['primary_color'] ?? 'var(--krd-violet)' }};
                --tenant-accent: {{ $tenant->branding['accent_color'] ?? 'var(--krd-amber)' }};
            }
        </style>
        @endif
    @endif

    {{-- Fonts --}}
    <style>
        @font-face {
            font-family: 'Satoshi';
            src: url('/fonts/Satoshi-Variable.woff2') format('woff2');
            font-weight: 300 900;
            font-display: swap;
            font-style: normal;
        }
        @font-face {
            font-family: 'Satoshi';
            src: url('/fonts/Satoshi-VariableItalic.woff2') format('woff2');
            font-weight: 300 900;
            font-display: swap;
            font-style: italic;
        }
    </style>

    {{-- Styles --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    {{-- PWA --}}
    <link rel="manifest" href="{{ route('pwa.manifest') }}">
    <link rel="apple-touch-icon" href="{{ route('pwa.icon', ['size' => 180]) }}">

    <script>
        window.__currentUserId = {{ auth()->id() ?? 'null' }};
        window.__currentUserName = @js(auth()->user()->name ?? '');
        window.__tenantWhiteLabel = {{ (auth()->user()?->tenant && app(\App\Services\FeatureGateService::class)->canAccess(auth()->user()->tenant, 'white_label')) ? 'true' : 'false' }};
        window.__tenantSlug = @js(auth()->user()?->tenant?->slug ?? 'koordli');
        window.__vapidPublicKey = @js(config('services.vapid.public_key'));
    </script>
</head>
<body class="krd-body h-full" x-cloak>

    {{-- Toast Container --}}
    <div id="krd-toast-container"
         class="fixed top-4 right-4 z-50 flex flex-col gap-2 pointer-events-none">
    </div>

    {{-- Mobile Sidebar Overlay --}}
    <div class="krd-sidebar-overlay"
        x-bind:class="{ 'active': sidebarOpen && window.innerWidth < 768 }"
        x-on:click="sidebarOpen = false">
    </div>

    {{-- App Shell --}}
    <div class="krd-shell">

        {{-- Sidebar --}}
        <aside class="krd-sidebar"
            id="krd-sidebar"
            x-bind:class="{
                'krd-sidebar--collapsed': !sidebarOpen && window.innerWidth >= 768,
                'open': sidebarOpen && window.innerWidth < 768
            }">
            @include('components.layout.tenant-sidebar')
        </aside>

        {{-- Main --}}
        <div class="krd-main"
             x-bind:class="{ 'krd-main--expanded': !sidebarOpen }">

            {{-- Topbar --}}
            <header class="krd-topbar">
                @include('components.layout.tenant-topbar')
            </header>

            {{-- Trial Banner --}}
            <x-ui.trial-banner />

            {{-- Page Content --}}
            <main class="krd-content">
                {{ $slot ?? '' }}
                @yield('content')
            </main>

        </div>

    </div>

        <livewire:tenant.conversations.floating-conversations-widget />
    @livewireScripts

    <script>
        document.addEventListener('subscription-locked', function() {
            if (window.showToast) {
                window.showToast("This action requires an active plan. Redirecting to billing...", 'error');
            }
            setTimeout(function() {
                window.location.href = '{{ route('tenant.billing.upgrade') }}';
            }, 1500);
        });
    </script>
</body>
</html>