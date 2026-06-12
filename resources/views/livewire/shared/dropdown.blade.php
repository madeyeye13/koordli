<div
    class="krd-dropdown"
    x-data="{ open: @entangle('open') }"
    x-on:click.outside="open = false; $wire.open = false;"
>
    {{-- Trigger --}}
    <button
        type="button"
        class="krd-dropdown-trigger {{ $open ? 'open' : '' }}"
        wire:click="toggle"
    >
        {{-- Selected value or placeholder --}}
        <span style="{{ $this->selectedLabel ? 'color:#1C1917' : '' }}">
            @if($this->selectedLabel)
                {{ $this->selectedLabel }}
            @else
                <span class="krd-dropdown-trigger-placeholder">{{ $placeholder }}</span>
            @endif
        </span>

        <div style="display:flex;align-items:center;gap:6px;flex-shrink:0;">
            {{-- Clear button --}}
            @if($value && !empty($value))
            <span
                wire:click.stop="clear"
                style="color:#A8A29E;font-size:14px;line-height:1;cursor:pointer;padding:2px;"
            >×</span>
            @endif

            {{-- Chevron --}}
            <svg class="krd-dropdown-chevron" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <polyline points="6 9 12 15 18 9"/>
            </svg>
        </div>
    </button>

    {{-- Menu --}}
    @if($open)
    <div class="krd-dropdown-menu" x-show="open" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">

        {{-- Search --}}
        @if($searchable)
        <div style="padding:8px;border-bottom:1px solid #E7E5E4;">
            <input
                wire:model.live="search"
                type="text"
                placeholder="Search..."
                style="width:100%;border:1px solid #E7E5E4;border-radius:4px;padding:6px 10px;font-size:12px;font-family:inherit;outline:none;background:#FAFAF9;"
                x-ref="searchInput"
                x-init="$nextTick(() => $refs.searchInput?.focus())"
            />
        </div>
        @endif

        {{-- Options --}}
        @forelse($this->filteredOptions as $option)
        @php
            $isSelected = $multiple
                ? in_array($option[$optionValue], (array) $value)
                : $option[$optionValue] == $value;
        @endphp
        <div
            class="krd-dropdown-option {{ $isSelected ? 'selected' : '' }}"
            wire:click="select('{{ $option[$optionValue] }}')"
        >
            @if($multiple)
            <div style="width:14px;height:14px;border:1.5px solid {{ $isSelected ? '#7C3AED' : '#D6D3D1' }};border-radius:3px;background:{{ $isSelected ? '#7C3AED' : 'transparent' }};display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                @if($isSelected)
                <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" fill="none" viewBox="0 0 24 24" stroke="#fff" stroke-width="3"><path d="M20 6L9 17l-5-5"/></svg>
                @endif
            </div>
            @endif
            {{ $option[$optionLabel] }}
        </div>
        @empty
        <div class="krd-dropdown-empty">No options found</div>
        @endforelse

    </div>
    @endif

</div>