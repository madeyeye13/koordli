@php
    $faviconPath = \App\Models\Central\PlatformSetting::get('site_favicon');
@endphp
@if($faviconPath)
<link rel="icon" type="image/png" href="{{ Storage::url($faviconPath) }}">
@else
<link rel="icon" type="image/png" href="{{ asset('images/logoonwhite.png') }}">
@endif