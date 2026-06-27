<div>
    {{-- Header --}}
    <div style="margin-bottom:28px;">
        <div class="krd-label" style="margin-bottom:4px;">Vendor Portal</div>
        <h2 class="krd-heading-3" style="color:#1C1917;">My Runsheet</h2>
        <p style="font-size:13px;color:#78716C;margin-top:4px;">
            Your assigned items across all events. Tap a status to update in real time.
        </p>
    </div>

    @if($items->isEmpty())
    <div class="krd-card">
        <div class="krd-empty-state">
            <div class="krd-empty-state-icon">📋</div>
            <div class="krd-empty-state-title">No runsheet items yet</div>
            <div class="krd-empty-state-desc">You'll see your assigned items here once the event planner adds them to the runsheet.</div>
        </div>
    </div>
    @else

    {{-- Auto-refresh notice --}}
    <div style="display:flex;align-items:center;gap:8px;margin-bottom:20px;padding:10px 14px;background:#F5F3FF;border:1px solid #DDD6FE;border-radius:6px;"
        wire:poll.60s>
        <div style="width:8px;height:8px;border-radius:50%;background:#7C3AED;animation:pulse 2s infinite;flex-shrink:0;"></div>
        <span style="font-size:12px;color:#7C3AED;font-weight:500;">Live — updates every 60 seconds</span>
    </div>

    @foreach($items as $eventName => $eventItems)
    <div style="margin-bottom:32px;">

        {{-- Event header --}}
        <div style="margin-bottom:14px;padding-bottom:10px;border-bottom:2px solid #E7E5E4;">
            <h3 style="font-size:16px;font-weight:600;color:#1C1917;">{{ $eventName }}</h3>
            @php
                $firstItem = $eventItems->first();
                $eventDate = $firstItem?->runsheet?->date;
            @endphp
            @if($eventDate)
            <div style="font-size:12px;color:#A8A29E;margin-top:2px;">
                {{ $eventDate->format('l, d M Y') }}
            </div>
            @endif
            {{-- Progress --}}
            @php
                $total    = $eventItems->count();
                $done     = $eventItems->where('status', \App\Enums\RunsheetItemStatus::Done)->count();
                $progress = $total > 0 ? round(($done / $total) * 100) : 0;
            @endphp
            <div style="display:flex;align-items:center;gap:10px;margin-top:10px;">
                <div style="flex:1;height:6px;background:#E7E5E4;border-radius:3px;overflow:hidden;">
                    <div style="height:100%;width:{{ $progress }}%;background:#10B981;border-radius:3px;transition:width 400ms;"></div>
                </div>
                <span style="font-size:11px;font-weight:600;color:#10B981;flex-shrink:0;">{{ $done }}/{{ $total }} done</span>
            </div>
        </div>

        {{-- Items --}}
        <div style="display:flex;flex-direction:column;gap:12px;">
            @foreach($eventItems as $item)
            @php
                $statusColor = match($item->status) {
                    \App\Enums\RunsheetItemStatus::Done       => '#10B981',
                    \App\Enums\RunsheetItemStatus::InProgress => '#F59E0B',
                    \App\Enums\RunsheetItemStatus::Delayed    => '#EF4444',
                    default                                    => '#A8A29E',
                };
                $cardBorder = match($item->status) {
                    \App\Enums\RunsheetItemStatus::InProgress => 'border-left:3px solid #F59E0B;',
                    \App\Enums\RunsheetItemStatus::Delayed    => 'border-left:3px solid #EF4444;',
                    \App\Enums\RunsheetItemStatus::Done       => 'border-left:3px solid #10B981;opacity:0.75;',
                    default                                   => 'border-left:3px solid #E7E5E4;',
                };
            @endphp
            <div class="krd-card" style="padding:0;overflow:hidden;{{ $cardBorder }}">
                <div style="padding:16px 20px;">

                    {{-- Item header --}}
                    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:10px;flex-wrap:wrap;">
                        <div style="flex:1;min-width:0;">
                            <div style="font-size:15px;font-weight:600;color:#1C1917;margin-bottom:4px;">
                                {{ $item->title }}
                            </div>
                            @if($item->description)
                            <div style="font-size:13px;color:#78716C;margin-bottom:6px;line-height:1.5;">
                                {{ $item->description }}
                            </div>
                            @endif
                            <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
                                @if($item->start_time)
                                <span style="font-size:12px;color:#57534E;font-weight:500;">
                                    🕐 {{ $item->start_time->format('g:i A') }}
                                    @if($item->end_time) — {{ $item->end_time->format('g:i A') }} @endif
                                </span>
                                @endif
                                <span class="krd-badge" style="background:{{ $statusColor }}1a;color:{{ $statusColor }};">
                                    {{ $item->status->label() }}
                                </span>
                            </div>
                            @if($item->notes)
                            <div style="margin-top:8px;padding:8px 10px;background:#FEF3C7;border-radius:4px;font-size:12px;color:#92400E;line-height:1.5;">
                                📝 {{ $item->notes }}
                            </div>
                            @endif
                        </div>
                    </div>

                    {{-- Status action buttons --}}
                    @if($item->status !== \App\Enums\RunsheetItemStatus::Done)
                    <div style="border-top:1px solid #F5F5F4;padding-top:12px;">
                        <div style="font-size:11px;font-weight:600;letter-spacing:0.06em;text-transform:uppercase;color:#A8A29E;margin-bottom:10px;">
                            Update Status
                        </div>
                        <div style="display:flex;gap:8px;flex-wrap:wrap;">

                            @if($item->status !== \App\Enums\RunsheetItemStatus::InProgress)
                            <button wire:click="updateStatus({{ $item->id }}, 'in_progress')"
                                wire:loading.attr="disabled"
                                wire:target="updateStatus({{ $item->id }}, 'in_progress')"
                                style="flex:1;min-width:120px;padding:12px 16px;border-radius:6px;border:2px solid #F59E0B;background:#FFFBEB;color:#D97706;font-size:13px;font-weight:600;cursor:pointer;transition:all 150ms;font-family:inherit;">
                                ▶ In Progress
                            </button>
                            @endif

                            <button wire:click="updateStatus({{ $item->id }}, 'done')"
                                wire:loading.attr="disabled"
                                wire:target="updateStatus({{ $item->id }}, 'done')"
                                style="flex:1;min-width:120px;padding:12px 16px;border-radius:6px;border:2px solid #10B981;background:#F0FDF4;color:#059669;font-size:13px;font-weight:600;cursor:pointer;transition:all 150ms;font-family:inherit;">
                                ✓ Mark Done
                            </button>

                            @if($item->status !== \App\Enums\RunsheetItemStatus::Delayed)
                            <button wire:click="updateStatus({{ $item->id }}, 'delayed')"
                                wire:loading.attr="disabled"
                                wire:target="updateStatus({{ $item->id }}, 'delayed')"
                                style="flex:1;min-width:120px;padding:12px 16px;border-radius:6px;border:2px solid #EF4444;background:#FEF2F2;color:#DC2626;font-size:13px;font-weight:600;cursor:pointer;transition:all 150ms;font-family:inherit;">
                                ⚠ Delayed
                            </button>
                            @endif

                        </div>
                    </div>
                    @else
                    {{-- Done state --}}
                    <div style="border-top:1px solid #F5F5F4;padding-top:12px;display:flex;align-items:center;gap:10px;">
                        <div style="width:28px;height:28px;border-radius:50%;background:#D1FAE5;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="#059669" stroke-width="2.5"><path d="M20 6L9 17l-5-5"/></svg>
                        </div>
                        <span style="font-size:13px;color:#059669;font-weight:500;">Completed</span>
                        <button wire:click="updateStatus({{ $item->id }}, 'pending')"
                            style="margin-left:auto;font-size:11px;color:#A8A29E;background:none;border:none;cursor:pointer;text-decoration:underline;font-family:inherit;">
                            Undo
                        </button>
                    </div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endforeach
    @endif

    {{-- Delay Note Modal --}}
    @if($showDelayModal)
    <div style="position:fixed;inset:0;background:rgba(0,0,0,0.4);z-index:60;display:flex;align-items:center;justify-content:center;padding:16px;">
        <div style="background:#fff;border-radius:8px;padding:24px;max-width:420px;width:100%;">
            <h3 style="font-size:16px;font-weight:600;color:#1C1917;margin-bottom:6px;">Mark as Delayed</h3>
            <p style="font-size:13px;color:#78716C;margin-bottom:16px;line-height:1.6;">
                Add a note explaining the delay so the planner knows what's happening.
            </p>
            <div class="krd-input-group" style="margin-bottom:20px;">
                <label class="krd-label-text">Delay reason <span style="color:#A8A29E;font-weight:400;">(optional)</span></label>
                <textarea wire:model="delayNote" class="krd-input" rows="3"
                    placeholder="e.g. Delivery truck stuck in traffic, ETA 30 mins..."
                    style="resize:none;"></textarea>
            </div>
            <div style="display:flex;gap:10px;">
                <button wire:click="confirmDelay" class="krd-btn krd-btn-danger" style="flex:1;">
                    Confirm Delay
                </button>
                <button wire:click="cancelDelay" class="krd-btn krd-btn-secondary" style="flex:1;">
                    Cancel
                </button>
            </div>
        </div>
    </div>
    @endif

</div>

<style>
@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.4; }
}
</style>