<div x-data="{ showSuggestModal: false }">
    <div style="margin-bottom:24px;">
        <a href="{{ route('tenant.events.show', $event->slug) }}" wire:navigate style="color:#A8A29E;text-decoration:none;font-size:13px;">← Back to {{ $event->name }}</a>
        <h2 class="krd-heading-3" style="color:#1C1917;margin-top:8px;">Vendor Suggestions</h2>
    </div>

    @if($canManage)
    <div style="margin-bottom:16px;">
        <button x-on:click="showSuggestModal = true" class="krd-btn krd-btn-primary krd-btn-sm">Suggest a Vendor to Client</button>
    </div>
    @endif

    @if($suggestions->isEmpty())
        <div class="krd-card" style="padding:40px;text-align:center;color:#A8A29E;">No vendor suggestions yet.</div>
    @else
    <div style="display:flex;flex-direction:column;gap:12px;">
        @foreach($suggestions as $s)
        <div class="krd-card" style="padding:16px;">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;">
                <div>
                    <div style="display:flex;align-items:center;gap:8px;">
                        <span style="font-size:14px;font-weight:600;color:#1C1917;">{{ $s->name }}</span>
                        @if($s->isSuggestedByClient())
                        <span class="krd-badge" style="font-size:9px;background:#7C3AED22;color:#7C3AED;">Suggested by Client</span>
                        @else
                        <span class="krd-badge" style="font-size:9px;background:#3B82F622;color:#3B82F6;">Suggested by You</span>
                        @endif
                        <span class="krd-badge" style="font-size:9px;background:{{ $s->statusColor() }}22;color:{{ $s->statusColor() }};">{{ ucfirst($s->status) }}</span>
                    </div>
                    @if($s->category_label)<div style="font-size:12px;color:#78716C;margin-top:4px;">{{ $s->category_label }}</div>@endif
                    @if($s->portfolio_link)<a href="{{ $s->portfolio_link }}" target="_blank" style="font-size:12px;color:#7C3AED;">View Portfolio →</a>@endif
                    @if($s->notes)<div style="font-size:12px;color:#78716C;margin-top:6px;">{{ $s->notes }}</div>@endif
                    @if($s->decision_note)<div style="font-size:12px;color:#DC2626;margin-top:6px;">Reason: {{ $s->decision_note }}</div>@endif
                </div>

                <div style="display:flex;gap:6px;flex-shrink:0;">
                    @if($s->isSuggestedByClient() && $s->isPending())
                        <button wire:click="approveClientSuggestion({{ $s->id }})" class="krd-btn krd-btn-primary krd-btn-sm">Approve</button>
                        <button wire:click="openReject({{ $s->id }})" class="krd-btn krd-btn-danger krd-btn-sm">Reject</button>
                    @endif
                    @if($s->isSuggestedByStaff() && $s->isPending())
                        <button wire:click="withdrawSuggestion({{ $s->id }})" class="krd-btn krd-btn-ghost krd-btn-sm">Withdraw</button>
                    @endif
                    @if($s->status === 'approved' && !$s->resulting_assignment_id)
                        <button wire:click="showFinalize({{ $s->id }})" class="krd-btn krd-btn-primary krd-btn-sm">Finalize</button>
                    @endif
                    @if($s->resulting_assignment_id)
                        <span style="font-size:11px;color:#10B981;">✓ Added to event</span>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    {{-- Create Suggestion Modal --}}
    <template x-teleport="body">
    <div x-show="showSuggestModal" x-cloak style="position:fixed;inset:0;z-index:60;">
        <div style="position:absolute;inset:0;background:rgba(0,0,0,0.4);"></div>
        <div style="position:relative;height:100%;display:flex;align-items:center;justify-content:center;padding:16px;">
            <div style="background:#fff;border-radius:8px;padding:24px;max-width:440px;width:100%;">
                <h3 style="font-size:15px;font-weight:600;margin-bottom:12px;">Suggest a Vendor</h3>
                <input wire:model="name" type="text" class="krd-input" placeholder="Vendor name" style="margin-bottom:8px;">
                <input wire:model="category_label" type="text" class="krd-input" placeholder="Category (e.g. Photography)" style="margin-bottom:8px;">
                <input wire:model="portfolio_link" type="url" class="krd-input" placeholder="Portfolio link (https://...)" style="margin-bottom:8px;">
                <textarea wire:model="notes" class="krd-input" rows="3" placeholder="Note to client (optional)"></textarea>
                <div style="display:flex;gap:10px;margin-top:16px;">
                    <button wire:click="createSuggestion" x-on:click="showSuggestModal = false" class="krd-btn krd-btn-primary" style="flex:1;">Send to Client</button>
                    <button x-on:click="showSuggestModal = false" class="krd-btn krd-btn-ghost" style="flex:1;">Cancel</button>
                </div>
            </div>
        </div>
    </div>
    </template>

    {{-- Reject Modal --}}
    <template x-teleport="body">
    <div x-show="$wire.showRejectModal" x-cloak style="position:fixed;inset:0;z-index:60;">
        <div style="position:absolute;inset:0;background:rgba(0,0,0,0.4);"></div>
        <div style="position:relative;height:100%;display:flex;align-items:center;justify-content:center;padding:16px;">
            <div style="background:#fff;border-radius:8px;padding:24px;max-width:400px;width:100%;">
                <h3 style="font-size:15px;font-weight:600;margin-bottom:12px;">Reject Suggestion</h3>
                <textarea wire:model="rejectReason" class="krd-input" rows="3" placeholder="Reason (shown to client, optional)"></textarea>
                <div style="display:flex;gap:10px;margin-top:16px;">
                    <button wire:click="rejectClientSuggestion" class="krd-btn krd-btn-danger" style="flex:1;">Reject</button>
                    <button wire:click="$set('showRejectModal', false)" class="krd-btn krd-btn-secondary" style="flex:1;">Cancel</button>
                </div>
            </div>
        </div>
    </div>
    </template>

    {{-- Finalize Modal --}}
    <template x-teleport="body">
    <div x-show="$wire.showFinalizeForm" x-cloak style="position:fixed;inset:0;z-index:60;">
        <div style="position:absolute;inset:0;background:rgba(0,0,0,0.4);"></div>
        <div style="position:relative;height:100%;display:flex;align-items:center;justify-content:center;padding:16px;">
            <div style="background:#fff;border-radius:8px;padding:24px;max-width:460px;width:100%;">
                <h3 style="font-size:15px;font-weight:600;margin-bottom:4px;">Finalize Vendor</h3>
                <p style="font-size:12px;color:#78716C;margin-bottom:16px;">Link to an existing vendor if this matches one you already work with, or leave blank to add as a new vendor.</p>

                <div class="krd-input-group">
                    <label class="krd-label-text">Match to existing vendor <span style="color:#A8A29E;font-weight:400;">(optional)</span></label>
                    <x-ui.dropdown wire="finalize_existing_vendor_id" placeholder="— Create as new vendor —"
                        selected="{{ $finalize_existing_vendor_id ? $vendors->firstWhere('id', $finalize_existing_vendor_id)?->name : '— Create as new vendor —' }}">
                        @foreach($vendors as $v)
                        <div class="krd-dropdown-option {{ $finalize_existing_vendor_id == $v->id ? 'selected' : '' }}" x-on:click="select(@js($v->name), {{ $v->id }})">
                            {{ $v->name }}
                        </div>
                        @endforeach
                    </x-ui.dropdown>
                </div>

                <div class="krd-input-group">
                    <label class="krd-label-text">Amount Agreed</label>
                    <input wire:model="finalize_amount" type="number" step="0.01" class="krd-input" placeholder="0.00">
                </div>

                <div class="krd-input-group">
                    <label class="krd-label-text">Who pays this vendor?</label>
                    <x-ui.dropdown wire="finalize_payment_responsibility" placeholder="Paid from event budget"
                        selected="{{ match($finalize_payment_responsibility) {
                            'client_pays_planner' => 'Client pays you, you pay vendor',
                            'client_pays_vendor_direct' => 'Client pays vendor directly',
                            default => 'Paid from event budget',
                        } }}">
                        <div class="krd-dropdown-option {{ $finalize_payment_responsibility === 'planner_pays_from_budget' ? 'selected' : '' }}" x-on:click="select('Paid from event budget', 'planner_pays_from_budget')">
                            Paid from event budget
                        </div>
                        <div class="krd-dropdown-option {{ $finalize_payment_responsibility === 'client_pays_planner' ? 'selected' : '' }}" x-on:click="select('Client pays you, you pay vendor', 'client_pays_planner')">
                            Client pays you, you pay vendor
                        </div>
                        <div class="krd-dropdown-option {{ $finalize_payment_responsibility === 'client_pays_vendor_direct' ? 'selected' : '' }}" x-on:click="select('Client pays vendor directly', 'client_pays_vendor_direct')">
                            Client pays vendor directly
                        </div>
                    </x-ui.dropdown>
                </div>

                <label style="display:flex;align-items:center;gap:8px;font-size:13px;margin-bottom:10px;">
                    <input type="checkbox" wire:model="finalize_is_client_visible" style="accent-color:#7C3AED;">
                    Show this vendor to the client
                </label>
                <label style="display:flex;align-items:center;gap:8px;font-size:13px;margin-bottom:10px;">
                    <input type="checkbox" wire:model="finalize_client_can_view_pricing" style="accent-color:#7C3AED;">
                    Client can see pricing
                </label>

                <div style="display:flex;gap:10px;margin-top:8px;">
                    <button wire:click="finalizeSuggestion" class="krd-btn krd-btn-primary" style="flex:1;">Add to Event</button>
                    <button wire:click="$set('showFinalizeForm', false)" class="krd-btn krd-btn-ghost" style="flex:1;">Cancel</button>
                </div>
            </div>
        </div>
    </div>
    </template>
</div>