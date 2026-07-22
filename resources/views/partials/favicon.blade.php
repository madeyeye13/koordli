@php
    $faviconPath = \App\Models\Central\PlatformSetting::get('site_favicon');
    $faviconVersion = $faviconPath ? \Illuminate\Support\Facades\Storage::disk('public')->lastModified($faviconPath) : null;
@endphp
@if($faviconPath)
<link rel="icon" type="image/png" href="{{ Storage::url($faviconPath) }}?v={{ $faviconVersion }}">
@else
<link rel="icon" type="image/png" href="{{ asset('images/logoonwhite.png') }}">
@endif