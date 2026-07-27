<div>
    <div style="margin-bottom:24px;">
        <div style="margin-bottom:8px;">
            <a href="{{ route('tenant.assets') }}" wire:navigate style="color:#A8A29E;text-decoration:none;font-size:13px;">← Back to {{ term_title('asset_plural', 'Assets') }}</a>
        </div>
        <div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:12px;">
            <div>
                <h2 class="krd-heading-3" style="color:#1C1917;">{{ $asset->name }}</h2>
                <div style="font-size:12px;color:#78716C;margin-top:2px;">{{ $asset->category?->name ?? '—' }}</div>
            </div>
            <span class="krd-badge" style="background:{{ $asset->statusColor() }}1a;color:{{ $asset->statusColor() }};">{{ $asset->statusLabel() }}</span>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 300px;gap:20px;align-items:start;" id="asset-detail-grid">
        <div>
            <div class="krd-card" style="padding:20px;margin-bottom:16px;">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;">
                    <div class="krd-label" style="margin-bottom:0;">Assigned {{ term_title('event_plural', 'Events') }}</div>
                    <button wire:click="showAssign" class="krd-btn krd-btn-primary krd-btn-sm">+ Assign to {{ term('event', 'Event') }}</button>
                </div>

                @if($showAssignForm)
                <div style="background:#F5F3FF;border:1px solid #DDD6FE;border-radius:6px;padding:16px;margin-bottom:14px;">
                    <div class="krd-input-group">
                        <label class="krd-label-text">{{ term_title('event', 'Event') }}</label>
                        <x-ui.dropdown wire="assign_event_id" placeholder="Select {{ term('event', 'event') }}"
                            selected="{{ $assign_event_id ? ($availableEvents->firstWhere('id', $assign_event_id)?->name ?? 'Select') : 'Select ' . term('event', 'event') }}">
                            @foreach($availableEvents as $event)
                            <div class="krd-dropdown-option {{ $assign_event_id == $event->id ? 'selected' : '' }}" x-on:click="select(@js($event->name), {{ $event->id }})">
                                {{ $event->name }} @if($event->date)<span style="color:#A8A29E;"> · {{ $event->date->format('M d, Y') }}</span>@endif
                            </div>
                            @endforeach
                        </x-ui.dropdown>
                        @error('assign_event_id') <span class="krd-input-error-msg">{{ $message }}</span> @enderror
                    </div>
                    <div class="krd-grid-2" style="gap:12px;">
                        <div class="krd-input-group">
                            <label class="krd-label-text">From <span style="color:#A8A29E;font-weight:400;">(optional)</span></label>
                            <input wire:model="assign_date_from" type="date" class="krd-input" />
                        </div>
                        <div class="krd-input-group">
                            <label class="krd-label-text">To <span style="color:#A8A29E;font-weight:400;">(optional)</span></label>
                            <input wire:model="assign_date_to" type="date" class="krd-input" />
                        </div>
                    </div>
                    <div class="krd-input-group" style="margin-bottom:0;">
                        <label class="krd-label-text">Notes</label>
                        <input wire:model="assign_notes" type="text" class="krd-input" />
                    </div>
                    <div style="display:flex;gap:10px;margin-top:14px;">
                        <button wire:click="assignToEvent" class="krd-btn krd-btn-primary krd-btn-sm">Assign</button>
                        <button wire:click="$set('showAssignForm', false)" class="krd-btn krd-btn-ghost krd-btn-sm">Cancel</button>
                    </div>
                </div>
                @endif

                @forelse($asset->eventAssignments as $assignment)
                <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 0;border-bottom:1px solid #F5F5F4;gap:8px;flex-wrap:wrap;">
                    <div>
                        <div style="font-size:13px;font-weight:500;color:#1C1917;">{{ $assignment->event->name }}</div>
                        @if($assignment->date_from)
                        <div style="font-size:11px;color:#A8A29E;">{{ $assignment->date_from->format('M d') }}@if($assignment->date_to && $assignment->date_to->ne($assignment->date_from)) – {{ $assignment->date_to->format('M d, Y') }}@endif</div>
                        @endif
                    </div>
                    <button wire:click="confirmDeleteAssign({{ $assignment->id }})" class="krd-btn krd-btn-sm" style="background:#FEE2E2;color:#DC2626;">Remove</button>
                </div>
                @empty
                <div style="font-size:13px;color:#A8A29E;text-align:center;padding:16px 0;">Not currently assigned to any {{ term('event_plural', 'events') }}.</div>
                @endforelse
            </div>

            <div class="krd-card" style="padding:20px;">
                <div class="krd-label" style="margin-bottom:10px;">Notes</div>
                <p style="font-size:13px;color:#57534E;">{{ $asset->notes ?: 'No notes added.' }}</p>
            </div>
        </div>

        <div style="display:flex;flex-direction:column;gap:12px;" id="asset-detail-actions">
            <div class="krd-card" style="padding:20px;">
                <div class="krd-label" style="margin-bottom:12px;">Status</div>
                <div style="display:flex;flex-direction:column;gap:6px;">
                    @foreach(['available' => 'Available', 'reserved' => 'Reserved', 'maintenance' => 'Maintenance'] as $val => $label)
                    <button wire:click="updateStatus('{{ $val }}')" class="krd-btn krd-btn-sm"
                        style="{{ $asset->status === $val ? 'background:#7C3AED;color:#fff;' : 'background:#F5F5F4;color:#57534E;' }}">
                        {{ $label }}
                    </button>
                    @endforeach
                </div>
            </div>

            <a href="{{ route('tenant.assets.edit', $asset->id) }}" wire:navigate class="krd-btn krd-btn-secondary" style="width:100%;">Edit {{ term('asset', 'Asset') }}</a>
        </div>
    </div>

    @if($showDeleteAssignModal)
    <div style="position:fixed;inset:0;background:rgba(0,0,0,0.4);z-index:60;display:flex;align-items:center;justify-content:center;padding:16px;">
        <div style="background:#fff;border-radius:8px;padding:24px;max-width:400px;width:100%;">
            <h3 style="font-size:16px;font-weight:600;color:#1C1917;margin-bottom:8px;">Remove Assignment?</h3>
            <div style="display:flex;gap:10px;">
                <button wire:click="deleteAssign" class="krd-btn krd-btn-danger" style="flex:1;">Yes, Remove</button>
                <button wire:click="$set('showDeleteAssignModal', false)" class="krd-btn krd-btn-secondary" style="flex:1;">Cancel</button>
            </div>
        </div>
    </div>
    @endif
</div>

<style>
@media (max-width:768px) { #asset-detail-grid { grid-template-columns:1fr !important; } }
</style>