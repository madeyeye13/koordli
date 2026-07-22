@props(['tenant' => null])

@php
    $showBranding = true;

    if ($tenant) {
        $showBranding = !app(\App\Services\FeatureGateService::class)->canAccess($tenant, 'white_label');
    }
@endphp

@if($showBranding)
<div {{ $attributes }}>
    Powered by <a href="/">Koordli</a>
</div>
@endif