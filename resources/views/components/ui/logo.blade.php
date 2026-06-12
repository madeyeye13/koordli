@props(['color' => 'light', 'tagline' => null])

@php
    $lightLogo = asset('images/logoonblack.png');
    $darkLogo  = asset('images/logoonwhite.png');
    $defaultTagline = $tagline ?? 'Event Operations Simplified';
@endphp

@if($color === 'light')
    {{-- Static — always white logo (auth left panel) --}}
    <div style="display:flex;flex-direction:column;">
        <img src="{{ $lightLogo }}" alt="Koordli" style="width:120px;height:auto;" />
        <span style="font-size:10px;font-weight:500;letter-spacing:0.1em;text-transform:uppercase;color:#78716C;margin-top:3px;">
            {{ $defaultTagline }}
        </span>
    </div>

@elseif($color === 'dark')
    {{-- Static — always dark logo (auth right panel / mobile) --}}
    <div style="display:flex;flex-direction:column;">
        <img src="{{ $darkLogo }}" alt="Koordli" style="width:120px;height:auto;" />
        <span style="font-size:10px;font-weight:500;letter-spacing:0.1em;text-transform:uppercase;color:#A8A29E;margin-top:3px;">
            {{ $defaultTagline }}
        </span>
    </div>

@elseif($color === 'auto')
    <div style="display:flex;flex-direction:column;">

        {{-- Light mode logo --}}
        <img
            src="{{ $darkLogo }}"
            alt="Koordli"
            class="krd-logo-for-light"
            style="width:120px;height:auto;"
        />

        {{-- Dark mode logo --}}
        <img
            src="{{ $lightLogo }}"
            alt="Koordli"
            class="krd-logo-for-dark"
            style="width:120px;height:auto;display:none;"
        />

        <span
            class="krd-logo-tagline"
            style="font-size:10px;font-weight:500;letter-spacing:0.1em;text-transform:uppercase;color:#A8A29E;margin-top:3px;"
        >{{ $defaultTagline }}</span>
    </div>
@endif