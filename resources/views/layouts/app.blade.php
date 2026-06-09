<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name') }}</title>

    <style>
        @font-face {
            font-family: 'Satoshi';
            src: url('/fonts/Satoshi-Variable.woff2') format('woff2');
            font-weight: 300 900;
            font-display: swap;
            font-style: normal;
        }
    </style>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="krd-body h-full">
    {{ $slot }}
    @livewireScripts
</body>
</html>