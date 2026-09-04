{{-- resources/views/components/ui/confirm-button.blade.php --}}
@props(['action', 'message', 'label' => '', 'class' => 'krd-btn krd-btn-danger krd-btn-sm', 'loadingLabel' => '...', 'icon' => null])
<div x-data="{ open: false }" style="display:inline-block;">
    <button type="button" x-on:click="open = true" {{ $attributes->merge(['class' => $class]) }}>
        @if($icon)
        {!! $icon !!}
        @else
        <span wire:loading.remove wire:target="{{ $action }}">{{ $label }}</span>
        <span wire:loading wire:target="{{ $action }}">{{ $loadingLabel }}</span>
        @endif
    </button>
    <template x-teleport="body">
    <div x-show="open" x-cloak style="position:fixed;inset:0;z-index:70;">
        <div style="position:absolute;inset:0;background:rgba(0,0,0,0.4);"></div>
        <div style="position:relative;height:100%;display:flex;align-items:center;justify-content:center;padding:16px;">
            <div style="background:#fff;border-radius:8px;padding:24px;max-width:380px;width:100%;">
                <p style="font-size:13px;color:#1C1917;margin-bottom:20px;line-height:1.6;">{{ $message }}</p>
                <div style="display:flex;gap:10px;">
                    <button wire:click="{{ $action }}" x-on:click="open = false" class="krd-btn krd-btn-danger" style="flex:1;">Confirm</button>
                    <button type="button" x-on:click="open = false" class="krd-btn krd-btn-secondary" style="flex:1;">Cancel</button>
                </div>
            </div>
        </div>
    </div>
    </template>
</div>