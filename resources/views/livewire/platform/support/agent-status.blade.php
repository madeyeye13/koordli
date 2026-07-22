<div x-data="{ available: {{ $agent?->is_available ? 'true' : 'false' }} }"
    x-on:livewire:update="available = {{ $agent?->is_available ? 'true' : 'false' }}"
    style="display:flex;align-items:center;gap:10px;">

    <div style="display:flex;align-items:center;gap:6px;">
        <span :style="available ? 'width:8px;height:8px;border-radius:50%;background:#10B981;' : 'width:8px;height:8px;border-radius:50%;background:#A8A29E;'"></span>
        <span style="font-size:12px;color:#78716C;" x-text="available ? 'Available for Support' : 'Unavailable'"></span>
    </div>

    <button wire:click="toggleAvailability"
        x-on:click="available = !available"
        :style="`width:36px;height:20px;border-radius:10px;border:none;cursor:pointer;position:relative;transition:background 150ms;background:${available ? '#10B981' : '#D6D3D1'};`">
        <span :style="`position:absolute;top:2px;width:16px;height:16px;background:#fff;border-radius:50%;transition:left 150ms;left:${available ? '18px' : '2px'};`"></span>
    </button>
</div>