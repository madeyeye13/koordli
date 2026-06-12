<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      class="h-full"
      x-data="{
          darkMode: localStorage.getItem('krd-dark') === 'true',
          sidebarOpen: true
      }"
      x-bind:class="{ 'dark': darkMode }"
      x-init="$watch('darkMode', val => localStorage.setItem('krd-dark', val))">
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
</head>
<body class="krd-body h-full" x-cloak>

    {{-- Toast Container --}}
    <div id="krd-toast-container"
         class="fixed top-4 right-4 z-50 flex flex-col gap-2 pointer-events-none">
    </div>

    {{-- Mobile Sidebar Overlay --}}
    <div id="krd-overlay"
        class="krd-sidebar-overlay"
        onclick="
            document.getElementById('krd-sidebar').classList.remove('open');
            document.getElementById('krd-overlay').classList.remove('active');
        ">
    </div>

    {{-- App Shell --}}
    <div class="krd-shell">

        {{-- Sidebar --}}
        <aside class="krd-sidebar"
               id="krd-sidebar"
               x-bind:class="{ 'krd-sidebar--collapsed': !sidebarOpen }">
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

    @livewireScripts
</body>
</html>