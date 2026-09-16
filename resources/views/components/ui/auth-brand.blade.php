@props(['color' => 'dark'])

@php
    $faviconPath = \App\Models\Central\PlatformSetting::get('site_favicon');
    $faviconVersion = $faviconPath ? \Illuminate\Support\Facades\Storage::disk('public')->lastModified($faviconPath) : null;
    $fallbackLogo = $color === 'light' ? 'images/logoonblack.png' : 'images/logoonwhite.png';
    $textColor = $color === 'light' ? '#FAFAF9' : '#1C1917';
@endphp

<div class="krd-auth-brand" style="display:flex;align-items:center;gap:9px;">
    @if($faviconPath)
    <img src="{{ \Illuminate\Support\Facades\Storage::url($faviconPath) }}?v={{ $faviconVersion }}" alt="Koordli" style="width:32px;height:32px;object-fit:contain;border-radius:6px;">
    @else
    <img src="{{ asset($fallbackLogo) }}" alt="Koordli" style="width:32px;height:32px;object-fit:contain;border-radius:6px;">
    @endif
    <span style="font-size:18px;font-weight:700;color:{{ $textColor }};letter-spacing:-0.02em;">Koordli</span>
</div>
