<div x-data="{ showSuggestModal: false }">
    <div style="margin-bottom:24px;">
        <h2 class="krd-heading-3" style="color:#1C1917;">Vendors — {{ $event->name }}</h2>
        <p style="font-size:12px;color:#78716C;margin-top:4px;">Vendors selected or suggested for your event.</p>
    </div>

    @if($canSuggest)
    <div style="margin-bottom:20px;">
        <button x-on:click="showSuggestModal = true" class="krd-btn krd-btn-primary krd-btn-sm">Suggest a Vendor</button>
    </div>
    @endif

    {{-- Suggestions needing action --}}
    @if($suggestions->isNotEmpty())
    <div style="margin-bottom:28px;">
        <div class="krd-label" style="margin-bottom:12px;">Suggestions</div>
        <div style="display:flex;flex-direction:column;gap:12px;">
            @foreach($suggestions as $s)
            <div class="krd-card" style="padding:16px;">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;flex-wrap:wrap;">
                    <div>
                        <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                            <span style="font-size:14px;font-weight:600;color:#1C1917;">{{ $s->name }}</span>
                            @if($s->isSuggestedByClient())
                            <span class="krd-badge" style="font-size:9px;background:#7C3AED22;color:#7C3AED;">Your Suggestion</span>
                            @else
                            <span class="krd-badge" style="font-size:9px;background:#3B82F622;color:#3B82F6;">Suggested to You</span>
                            @endif
                        </div>
                        @if($s->category_label)<div style="font-size:12px;color:#78716C;margin-top:4px;">{{ $s->category_label }}</div>@endif
                        @if($s->portfolio_link)<a href="{{ $s->portfolio_link }}" target="_blank" style="font-size:12px;color:#7C3AED;">View Portfolio →</a>@endif
                        @if($s->notes)<div style="font-size:12px;color:#78716C;margin-top:6px;">{{ $s->notes }}</div>@endif
                    </div>

                    @if($s->isSuggestedByStaff() && $canApproveOrReject)
                    <div style="display:flex;gap:6px;flex-shrink:0;">
                        <button wire:click="approveStaffSuggestion({{ $s->id }})" class="krd-btn krd-btn-primary krd-btn-sm">Approve</button>
                        <button wire:click="rejectStaffSuggestion({{ $s->id }})" class="krd-btn krd-btn-danger krd-btn-sm">Decline</button>
                    </div>
                    @elseif($s->isSuggestedByClient())
                    <span style="font-size:11px;color:#F59E0B;flex-shrink:0;">Awaiting response</span>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Confirmed vendors --}}
    <div class="krd-label" style="margin-bottom:12px;">Confirmed Vendors</div>

    @if($confirmedAssignments->isEmpty())
        <div class="krd-card" style="padding:40px;text-align:center;color:#A8A29E;">
            <div style="font-size:13px;">No confirmed vendors yet.</div>
        </div>
    @else
    <div style="display:flex;flex-direction:column;gap:12px;">
        @foreach($confirmedAssignments as $a)
        <div class="krd-card" style="padding:16px;{{ $a->needsDisclaimerAcknowledgment() ? 'border-color:#F59E0B;background:#FFFBEB;' : '' }}">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;flex-wrap:wrap;">
                <div>
                    <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                        <span style="font-size:14px;font-weight:600;color:#1C1917;">{{ $a->vendor->name }}</span>
                        @if($a->selectionBadgeLabel())
                        <span class="krd-badge" style="font-size:9px;background:{{ $a->selectionBadgeColor() }}22;color:{{ $a->selectionBadgeColor() }};">{{ $a->selectionBadgeLabel() }}</span>
                        @endif
                    </div>
                    @if($a->vendor->category)<div style="font-size:12px;color:#78716C;margin-top:4px;">{{ $a->vendor->category->name }}</div>@endif
                    @if($a->vendor->instagram)<a href="{{ $a->vendor->instagram }}" target="_blank" style="font-size:12px;color:#7C3AED;">View Portfolio →</a>@endif

                    <div style="font-size:12px;color:#78716C;margin-top:8px;">{{ $a->paymentResponsibilityLabel() }}</div>

                    @if($a->client_can_view_pricing && $a->amount_agreed > 0)
                    <div style="font-size:13px;font-weight:600;color:#7C3AED;margin-top:4px;">
                        {{ \App\Helpers\CurrencyHelper::forTenant() }}{{ number_format($a->amount_agreed, 2) }}
                    </div>
                    @endif
                </div>

                @if($a->needsDisclaimerAcknowledgment())
                <button wire:click="openDisclaimer({{ $a->id }})" class="krd-btn krd-btn-secondary krd-btn-sm" style="flex-shrink:0;">
                    Review & Acknowledge
                </button>
                @endif
            </div>
        </div>
        @endforeach
    </div>
    @endif

    {{-- Suggest Vendor Modal --}}
    <template x-teleport="body">
    <div x-show="showSuggestModal" x-cloak style="position:fixed;inset:0;z-index:60;">
        <div style="position:absolute;inset:0;background:rgba(0,0,0,0.4);"></div>
        <div style="position:relative;height:100%;display:flex;align-items:center;justify-content:center;padding:16px;">
            <div style="background:#fff;border-radius:8px;padding:24px;max-width:440px;width:100%;">
                <h3 style="font-size:15px;font-weight:600;margin-bottom:12px;">Suggest a Vendor</h3>
                <input wire:model="name" type="text" class="krd-input" placeholder="Vendor name" style="margin-bottom:8px;">
                <input wire:model="category_label" type="text" class="krd-input" placeholder="Category (e.g. Photography)" style="margin-bottom:8px;">
                <input wire:model="portfolio_link" type="url" class="krd-input" placeholder="Portfolio link (https://...)" style="margin-bottom:8px;">
                <textarea wire:model="notes" class="krd-input" rows="3" placeholder="Anything else you'd like to share (optional)"></textarea>
                <div style="display:flex;gap:10px;margin-top:16px;">
                    <button wire:click="submitSuggestion" x-on:click="showSuggestModal = false" class="krd-btn krd-btn-primary" style="flex:1;">Send</button>
                    <button x-on:click="showSuggestModal = false" class="krd-btn krd-btn-ghost" style="flex:1;">Cancel</button>
                </div>
            </div>
        </div>
    </div>
    </template>

    {{-- Disclaimer Modal --}}
    <template x-teleport="body">
    <div x-show="$wire.showDisclaimerModal" x-cloak style="position:fixed;inset:0;z-index:60;">
        <div style="position:absolute;inset:0;background:rgba(0,0,0,0.4);"></div>
        <div style="position:relative;height:100%;display:flex;align-items:center;justify-content:center;padding:16px;">
            <div style="background:#fff;border-radius:8px;padding:24px;max-width:460px;width:100%;">
                <h3 style="font-size:15px;font-weight:600;margin-bottom:8px;">Please Review</h3>
                <p style="font-size:13px;color:#57534E;line-height:1.6;margin-bottom:20px;">
                    {{ \App\Models\Central\Tenant::find($event->tenant_id)->vendor_disclaimer_text ?? 'This vendor was sourced independently and is not vetted by us. We are not responsible for their quality, reliability, or service.' }}
                </p>
                <div style="display:flex;gap:10px;">
                    <button wire:click="acknowledgeDisclaimer" class="krd-btn krd-btn-primary" style="flex:1;">I Understand</button>
                    <button wire:click="$set('showDisclaimerModal', false)" class="krd-btn krd-btn-secondary" style="flex:1;">Later</button>
                </div>
            </div>
        </div>
    </div>
    </template>
</div>