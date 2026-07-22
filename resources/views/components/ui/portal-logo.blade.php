@props(['tenant' => null, 'color' => 'auto'])

@php
    // If no explicit tenant passed, try the domain-resolved one (for login pages)
    $resolvedTenant = $tenant ?? (app()->bound('resolvedTenant') ? app('resolvedTenant') : null);

    $whiteLabel = false;
    if ($resolvedTenant) {
        $whiteLabel = app(\App\Services\FeatureGateService::class)->canAccess($resolvedTenant, 'white_label');
    }

    $logoPath = $resolvedTenant?->branding['logo'] ?? null;
@endphp

@if($whiteLabel && $resolvedTenant)
    @if($logoPath)
        <img src="{{ \Illuminate\Support\Facades\Storage::url($logoPath) }}"
            alt="{{ $resolvedTenant->name }}"
            style="height:32px;max-width:180px;object-fit:contain;" />
    @else
        <div style="font-size:18px;font-weight:800;color:{{ $color === 'light' ? '#FAFAF9' : '#1C1917' }};letter-spacing:-0.02em;">
            {{ $resolvedTenant->name }}
        </div>
    @endif
@else
    <x-ui.logo :color="$color" />
@endif