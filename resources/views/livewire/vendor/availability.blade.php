<div>
    {{-- Header --}}
    <div style="margin-bottom:24px;display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:12px;">
        <div>
            <div class="krd-label" style="margin-bottom:4px;">Vendor Portal</div>
            <h2 class="krd-heading-3" style="color:#1C1917;">My Availability</h2>
            <p style="font-size:13px;color:#78716C;margin-top:4px;">
                Let event companies know when you're unavailable to avoid scheduling conflicts.
            </p>
        </div>
        <button wire:click="showAddForm" class="krd-btn krd-btn-primary krd-btn-sm">
            + Block Dates
        </button>
    </div>

    {{-- Add Form --}}
    @if($showForm)
    <div class="krd-card" style="padding:20px;margin-bottom:20px;border:2px solid #7C3AED;max-width:480px;">
        <div style="font-size:13px;font-weight:600;color:#1C1917;margin-bottom:16px;">Block Unavailable Dates</div>

        <div class="krd-grid-2" style="gap:12px;">
            <div class="krd-input-group">
                <label class="krd-label-text">From</label>
                <input wire:model="date_from" type="date" class="krd-input" />
                @error('date_from') <span class="krd-input-error-msg">{{ $message }}</span> @enderror
            </div>
            <div class="krd-input-group">
                <label class="krd-label-text">To</label>
                <input wire:model="date_to" type="date" class="krd-input" />
                @error('date_to') <span class="krd-input-error-msg">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="krd-input-group">
            <label class="krd-label-text">Reason</label>
            <x-ui.dropdown wire="reason" placeholder="Personal"
                selected="{{ match($reason) { 'vacation' => 'Vacation', 'holiday' => 'Holiday', 'other' => 'Other', default => 'Personal' } }}">
                <div class="krd-dropdown-option {{ $reason === 'personal' ? 'selected' : '' }}" x-on:click="select('Personal', 'personal')">Personal</div>
                <div class="krd-dropdown-option {{ $reason === 'vacation' ? 'selected' : '' }}" x-on:click="select('Vacation', 'vacation')">Vacation</div>
                <div class="krd-dropdown-option {{ $reason === 'holiday' ? 'selected' : '' }}" x-on:click="select('Holiday', 'holiday')">Holiday</div>
                <div class="krd-dropdown-option {{ $reason === 'other' ? 'selected' : '' }}" x-on:click="select('Other', 'other')">Other</div>
            </x-ui.dropdown>
        </div>

        <div class="krd-input-group" style="margin-bottom:0;">
            <label class="krd-label-text">Notes <span style="color:#A8A29E;font-weight:400;">(optional)</span></label>
            <input wire:model="notes" type="text" class="krd-input" placeholder="e.g. Traveling out of town" />
        </div>

        <div style="display:flex;gap:10px;margin-top:16px;">
            <button wire:click="save" wire:loading.attr="disabled" class="krd-btn krd-btn-primary">
                <span wire:loading.remove wire:target="save">Save</span>
                <span wire:loading wire:target="save">Saving...</span>
            </button>
            <button wire:click="$set('showForm', false)" class="krd-btn krd-btn-ghost">Cancel</button>
        </div>
    </div>
    @endif

    {{-- Upcoming --}}
    <div style="margin-bottom:28px;">
        <div class="krd-label" style="margin-bottom:12px;">Upcoming</div>
        @if($upcoming->isEmpty())
        <div class="krd-card">
            <div class="krd-empty-state">
                <div class="krd-empty-state-icon">📅</div>
                <div class="krd-empty-state-title">No upcoming unavailable dates</div>
                <div class="krd-empty-state-desc">Block dates when you're not available for bookings.</div>
            </div>
        </div>
        @else
        <div style="display:flex;flex-direction:column;gap:10px;">
            @foreach($upcoming as $date)
            <div class="krd-card" style="padding:16px;display:flex;align-items:flex-start;justify-content:space-between;gap:12px;flex-wrap:wrap;">
                <div style="flex:1;min-width:0;">
                    <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px;">
                        <span style="width:8px;height:8px;border-radius:50%;background:{{ $date->reasonColor() }};flex-shrink:0;"></span>
                        <span style="font-size:13px;font-weight:600;color:#1C1917;">{{ $date->reasonLabel() }}</span>
                    </div>
                    <div style="font-size:12px;color:#78716C;">
                        {{ $date->date_from->format('D, d M Y') }}
                        @if($date->date_from->ne($date->date_to))
                            — {{ $date->date_to->format('D, d M Y') }}
                        @endif
                    </div>
                    @if($date->notes)
                    <div style="font-size:12px;color:#A8A29E;margin-top:4px;">{{ $date->notes }}</div>
                    @endif
                </div>
                <button wire:click="confirmDelete({{ $date->id }})"
                    class="krd-btn krd-btn-sm"
                    style="background:#FEE2E2;color:#DC2626;border-color:#FECACA;flex-shrink:0;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/>
                    </svg>
                </button>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- Past --}}
    @if($past->isNotEmpty())
    <div>
        <div class="krd-label" style="margin-bottom:12px;">Past</div>
        <div style="display:flex;flex-direction:column;gap:10px;opacity:0.6;">
            @foreach($past as $date)
            <div class="krd-card" style="padding:16px;">
                <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px;">
                    <span style="width:8px;height:8px;border-radius:50%;background:{{ $date->reasonColor() }};flex-shrink:0;"></span>
                    <span style="font-size:13px;font-weight:600;color:#1C1917;">{{ $date->reasonLabel() }}</span>
                </div>
                <div style="font-size:12px;color:#78716C;">
                    {{ $date->date_from->format('D, d M Y') }}
                    @if($date->date_from->ne($date->date_to))
                        — {{ $date->date_to->format('D, d M Y') }}
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Delete Modal --}}
    @if($showDeleteModal)
    <div style="position:fixed;inset:0;background:rgba(0,0,0,0.4);z-index:60;display:flex;align-items:center;justify-content:center;padding:16px;">
        <div style="background:#fff;border-radius:8px;padding:24px;max-width:400px;width:100%;">
            <h3 style="font-size:16px;font-weight:600;color:#1C1917;margin-bottom:8px;">Remove Blocked Dates?</h3>
            <p style="font-size:13px;color:#78716C;margin-bottom:24px;line-height:1.6;">
                Event companies will see you as available again for this date range.
            </p>
            <div style="display:flex;gap:10px;">
                <button wire:click="delete" class="krd-btn krd-btn-danger" style="flex:1;">Yes, Remove</button>
                <button wire:click="$set('showDeleteModal', false)" class="krd-btn krd-btn-secondary" style="flex:1;">Cancel</button>
            </div>
        </div>
    </div>
    @endif
</div>