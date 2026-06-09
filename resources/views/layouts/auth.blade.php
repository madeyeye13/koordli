<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- SEO --}}
    <title>{{ $title ?? config('app.name') }}</title>
    <meta name="description" content="{{ $metaDescription ?? 'Koordli — Event Operations Platform' }}">
    <meta name="robots" content="noindex, nofollow">

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
<body class="krd-auth-body h-full">

    {{-- Toast Notifications --}}
    <div id="krd-toast-container"
         class="fixed top-4 right-4 z-50 flex flex-col gap-2 pointer-events-none">
    </div>

    {{-- Page Content --}}
    {{ $slot }}

    @livewireScripts
</body>
</html>