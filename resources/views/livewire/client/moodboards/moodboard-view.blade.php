<div>
    <div style="margin-bottom:20px;">
        <a href="{{ route('client.moodboards.index', $moodboard->event->slug) }}" wire:navigate style="color:#A8A29E;text-decoration:none;font-size:13px;">← Back to Moodboards</a>
        <h2 class="krd-heading-3" style="color:#1C1917;margin-top:6px;">{{ $moodboard->title }}</h2>
        @if($moodboard->description)<p style="font-size:12px;color:#78716C;margin-top:4px;">{{ $moodboard->description }}</p>@endif
    </div>

    <div style="display:flex;gap:8px;align-items:center;margin-bottom:20px;">
        <span class="krd-badge" style="font-size:10px;background:{{ $moodboard->statusColor() }}22;color:{{ $moodboard->statusColor() }};">{{ $moodboard->statusLabel() }}</span>

        @if($moodboard->status !== 'approved')
        <button wire:click="approve" wire:loading.attr="disabled" wire:target="approve" class="krd-btn krd-btn-primary krd-btn-sm">
            <span wire:loading.remove wire:target="approve">✓ Approve</span>
            <span wire:loading wire:target="approve">Saving...</span>
        </button>
        <button wire:click="openRequestChanges" class="krd-btn krd-btn-secondary krd-btn-sm">Request Changes</button>
        @else
        <span style="font-size:12px;color:#10B981;">You approved this board.</span>
        @endif
    </div>

    {{-- DESKTOP: the actual free-form layout, frozen — same pos_x/pos_y/
         width/height coordinates as the tenant canvas, but with zero
         interactivity (no drag, no resize, no edit). This is the real
         creative arrangement the planner built, not a re-flowed grid. --}}
    <div class="mb-client-canvas" style="display:none;position:relative;width:100%;height:{{ $canvasHeight }}px;overflow-x:auto;">
        @foreach($moodboard->sections as $section)
        <div style="position:absolute;left:{{ $section->pos_x }}px;top:{{ $section->pos_y }}px;width:{{ $section->width }}px;height:{{ $section->height }}px;border:2px dashed #DDD6FE;border-radius:8px;background:#F5F3FF33;">
            <div style="padding:6px 10px;font-size:11px;font-weight:600;color:#7C3AED;">{{ $section->title }}</div>
        </div>
        @endforeach

        @foreach($moodboard->items->where('type', '!=', 'empty') as $item)
        <div style="position:absolute;left:{{ $item->pos_x }}px;top:{{ $item->pos_y }}px;width:{{ $item->width }}px;height:{{ $item->height }}px;background:#fff;border:1px solid #E7E5E4;border-radius:8px;box-shadow:0 2px 6px rgba(0,0,0,0.06);overflow:hidden;padding:10px;">
            @include('livewire.client.moodboards.partials.item-content-readonly', ['item' => $item])
        </div>
        @endforeach
    </div>

    {{-- MOBILE: simplified reorderable-order list — same rationale as the
         staff side: true free-form positioning doesn't work well on
         touch/small screens, so mobile clients get a clean stacked list
         in the planner's chosen sort_order instead. --}}
    <div class="mb-client-mobile-list" style="display:flex;flex-direction:column;gap:14px;">
        @foreach($moodboard->items->where('type', '!=', 'empty')->sortBy('sort_order') as $item)
        <div class="krd-card" style="padding:12px;">
            <div style="font-size:9px;color:#A8A29E;text-transform:uppercase;margin-bottom:8px;">{{ $item->type }}{{ $item->section ? ' · ' . $item->section->title : '' }}</div>
            @include('livewire.client.moodboards.partials.item-content-readonly', ['item' => $item])
        </div>
        @endforeach
    </div>

    <style>
        @media (min-width: 900px) {
            .mb-client-canvas { display: block !important; }
            .mb-client-mobile-list { display: none !important; }
        }
    </style>

    {{-- Request Changes Modal --}}
    <template x-teleport="body">
    <div x-show="$wire.showRequestChangesModal" x-cloak style="position:fixed;inset:0;z-index:60;">
        <div style="position:absolute;inset:0;background:rgba(0,0,0,0.4);"></div>
        <div style="position:relative;height:100%;display:flex;align-items:center;justify-content:center;padding:16px;">
            <div style="background:#fff;border-radius:8px;padding:24px;max-width:420px;width:100%;">
                <h3 style="font-size:15px;font-weight:600;margin-bottom:12px;">Request Changes</h3>
                <textarea wire:model="changesNote" class="krd-input" rows="4" placeholder="What would you like to change?"></textarea>
                @error('changesNote') <span class="krd-input-error-msg">{{ $message }}</span> @enderror
                <div style="display:flex;gap:10px;margin-top:16px;">
                    <button wire:click="submitRequestChanges" wire:loading.attr="disabled" wire:target="submitRequestChanges" class="krd-btn krd-btn-primary" style="flex:1;">
                        <span wire:loading.remove wire:target="submitRequestChanges">Send</span>
                        <span wire:loading wire:target="submitRequestChanges">Sending...</span>
                    </button>
                    <button wire:click="$set('showRequestChangesModal', false)" class="krd-btn krd-btn-secondary" style="flex:1;">Cancel</button>
                </div>
            </div>
        </div>
    </div>
    </template>
</div>