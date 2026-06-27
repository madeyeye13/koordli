<div>
    <div style="margin-bottom: 28px;">
        <h1 style="font-size: 22px; font-weight: 600; color: #1C1917; letter-spacing: -0.01em;">
            Welcome, {{ auth('client')->user()?->name }}
        </h1>
        <p style="font-size: 13px; color: #78716C; margin-top: 4px;">
            Here's an overview of your event.
        </p>
    </div>

    @if($events->isEmpty())
    <div class="krd-card">
        <div class="krd-empty-state">
            <div class="krd-empty-state-icon">📋</div>
            <div class="krd-empty-state-title">No events found</div>
            <div class="krd-empty-state-desc">Your event coordinator hasn't linked an event to your account yet.</div>
        </div>
    </div>
    @else
        @foreach($events as $event)
        @php
            $budget      = $event->budget;
            $sym         = $budget ? \App\Helpers\CurrencyHelper::symbol($budget->currency ?? 'NGN') : \App\Helpers\CurrencyHelper::symbol('NGN');
            $agreed      = $budget ? $budget->agreedBudget()       : (float)($event->agreed_budget ?? 0);
            $clientPaid  = $budget ? $budget->totalClientPaid()    : 0;
            $outstanding = $budget ? $budget->clientOutstanding()  : $agreed;
            $collected   = $budget ? $budget->collectedPercentage(): 0;
        @endphp

        <div class="krd-card" style="margin-bottom: 20px;">

            {{-- Event Header --}}
            <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:16px;margin-bottom:20px;flex-wrap:wrap;">
                <div>
                    @if($event->eventType)
                    <div style="font-size:11px;font-weight:600;letter-spacing:0.06em;text-transform:uppercase;color:#78716C;margin-bottom:4px;">
                        {{ $event->eventType->name }}
                    </div>
                    @endif
                    <h2 style="font-size:18px;font-weight:600;color:#1C1917;letter-spacing:-0.01em;">
                        {{ $event->name }}
                    </h2>
                </div>
                @if($event->status)
                <span class="krd-badge krd-badge-dynamic"
                    style="background:{{ $event->status->color }}1a;color:{{ $event->status->color }};">
                    {{ $event->status->name }}
                </span>
                @endif
            </div>

            {{-- Event Details --}}
            <div class="krd-grid-2" style="gap:10px;margin-bottom:20px;">
                <div class="krd-card-sm">
                    <div style="font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:0.05em;color:#A8A29E;margin-bottom:4px;">Date</div>
                    <div style="font-size:14px;font-weight:600;color:#1C1917;">
                        {{ $event->date?->format('D, d M Y') ?? '—' }}
                    </div>
                    @if($event->start_time)
                    <div style="font-size:12px;color:#78716C;margin-top:2px;">
                        {{ \Carbon\Carbon::parse($event->start_time)->format('g:i A') }}
                        @if($event->end_time)
                            — {{ \Carbon\Carbon::parse($event->end_time)->format('g:i A') }}
                        @endif
                    </div>
                    @endif
                </div>

                <div class="krd-card-sm">
                    <div style="font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:0.05em;color:#A8A29E;margin-bottom:4px;">Venue</div>
                    <div style="font-size:14px;font-weight:600;color:#1C1917;">
                        {{ $event->venue ?? '—' }}
                    </div>
                    @if($event->location)
                    <div style="font-size:12px;color:#78716C;margin-top:2px;">{{ $event->location }}</div>
                    @endif
                </div>

                @if($event->max_guests)
                <div class="krd-card-sm">
                    <div style="font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:0.05em;color:#A8A29E;margin-bottom:4px;">Expected Guests</div>
                    <div style="font-size:14px;font-weight:600;color:#1C1917;">
                        {{ number_format($event->max_guests) }} guests
                    </div>
                </div>
                @endif

                @if($event->agreed_budget)
                <div class="krd-card-sm">
                    <div style="font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:0.05em;color:#A8A29E;margin-bottom:4px;">Agreed Budget</div>
                    <div style="font-size:14px;font-weight:600;color:#7C3AED;">
                        {{ $sym }}{{ number_format($agreed, 2) }}
                    </div>
                </div>
                @endif
            </div>

            {{-- Payment Summary --}}
            @if($event->agreed_budget)
            <div style="border-top:1px solid #E7E5E4;padding-top:16px;margin-bottom:16px;">
                <div style="font-size:11px;font-weight:600;letter-spacing:0.06em;text-transform:uppercase;color:#A8A29E;margin-bottom:12px;">
                    Payment Summary
                </div>

                <div class="krd-grid-2" style="gap:10px;margin-bottom:14px;">
                    <div class="krd-card-sm" style="border-left:3px solid #7C3AED;">
                        <div style="font-size:10px;color:#A8A29E;margin-bottom:3px;">Agreed Budget</div>
                        <div style="font-size:16px;font-weight:700;color:#7C3AED;">{{ $sym }}{{ number_format($agreed, 2) }}</div>
                    </div>
                    <div class="krd-card-sm" style="border-left:3px solid #10B981;">
                        <div style="font-size:10px;color:#A8A29E;margin-bottom:3px;">Amount Paid</div>
                        <div style="font-size:16px;font-weight:700;color:#10B981;">{{ $sym }}{{ number_format($clientPaid, 2) }}</div>
                        @if($agreed > 0)
                        <div style="font-size:10px;color:#A8A29E;margin-top:2px;">{{ $collected }}% of total</div>
                        @endif
                    </div>
                    <div class="krd-card-sm" style="border-left:3px solid {{ $outstanding > 0 ? '#EF4444' : '#10B981' }};">
                        <div style="font-size:10px;color:#A8A29E;margin-bottom:3px;">Outstanding</div>
                        <div style="font-size:16px;font-weight:700;color:{{ $outstanding > 0 ? '#EF4444' : '#10B981' }};">
                            {{ $sym }}{{ number_format($outstanding, 2) }}
                        </div>
                        @if($outstanding <= 0)
                        <div style="font-size:10px;color:#10B981;margin-top:2px;">Fully paid ✓</div>
                        @endif
                    </div>
                </div>

                {{-- Progress bar --}}
                @if($agreed > 0)
                <div>
                    <div style="display:flex;justify-content:space-between;margin-bottom:5px;">
                        <span style="font-size:11px;color:#57534E;">Payment progress</span>
                        <span style="font-size:11px;font-weight:600;color:#10B981;">{{ $collected }}%</span>
                    </div>
                    <div style="height:6px;background:#E7E5E4;border-radius:4px;overflow:hidden;">
                        <div style="height:100%;width:{{ min(100, $collected) }}%;background:#10B981;border-radius:4px;transition:width 400ms ease;"></div>
                    </div>
                </div>
                @endif
            </div>

            {{-- Payment History --}}
            @if($budget && $budget->clientPayments->count() > 0)
            <div style="border-top:1px solid #E7E5E4;padding-top:16px;">
                <div style="font-size:11px;font-weight:600;letter-spacing:0.06em;text-transform:uppercase;color:#A8A29E;margin-bottom:12px;">
                    Payment History
                </div>
                <div style="display:flex;flex-direction:column;gap:0;">
                    @foreach($budget->clientPayments->sortByDesc('paid_on') as $payment)
                    <div style="display:flex;align-items:center;gap:12px;padding:10px 0;border-bottom:1px solid #E7E5E4;">
                        <div style="width:32px;height:32px;border-radius:6px;background:#D1FAE5;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="#059669" stroke-width="2.5"><path d="M20 6L9 17l-5-5"/></svg>
                        </div>
                        <div style="flex:1;min-width:0;">
                            <div style="font-size:13px;font-weight:600;color:#10B981;">
                                {{ $sym }}{{ number_format($payment->amount, 2) }}
                            </div>
                            <div style="font-size:11px;color:#78716C;margin-top:1px;">
                                {{ $payment->paid_on->format('M d, Y') }}
                                · {{ ucfirst($payment->payment_method) }}
                                @if($payment->description)
                                    · {{ $payment->description }}
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach

                    <div style="display:flex;justify-content:space-between;align-items:center;padding:12px 0;">
                        <span style="font-size:12px;font-weight:600;color:#166534;">Total Paid</span>
                        <span style="font-size:15px;font-weight:700;color:#10B981;">{{ $sym }}{{ number_format($clientPaid, 2) }}</span>
                    </div>
                </div>
            </div>
            @endif

            @endif

            {{-- Notes --}}
            @if($event->notes)
            <div style="border-top:1px solid #E7E5E4;padding-top:16px;">
                <div style="font-size:11px;font-weight:600;letter-spacing:0.06em;text-transform:uppercase;color:#A8A29E;margin-bottom:8px;">
                    Notes from your coordinator
                </div>
                <p style="font-size:13px;color:#57534E;line-height:1.7;">{{ $event->notes }}</p>
            </div>
            @endif

            {{-- RSVP --}}
            @if($event->rsvp_enabled && $event->rsvpForm)
            @php
                $rsvpForm      = $event->rsvpForm;
                $responses     = $rsvpForm->responses ?? collect();
                $rsvpTotal     = $responses->count();
                $rsvpConfirmed = $responses->where('status', 'confirmed')->count();
                $rsvpDeclined  = $responses->where('status', 'declined')->count();
                $rsvpPending   = $responses->where('status', 'pending')->count();
                $rsvpAttendees = $responses->where('status', 'confirmed')->sum(fn($r) => 1 + $r->plus_one_count);
            @endphp
            <div style="border-top:1px solid #E7E5E4;padding-top:16px;margin-top:4px;">
                <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;margin-bottom:14px;">
                    <div style="font-size:11px;font-weight:600;letter-spacing:0.06em;text-transform:uppercase;color:#A8A29E;">
                        RSVP
                    </div>
                    <span class="krd-badge {{ $rsvpForm->is_active ? 'krd-badge-green' : 'krd-badge-stone' }}">
                        {{ $rsvpForm->is_active ? 'Accepting responses' : 'Closed' }}
                    </span>
                </div>

                {{-- Stats --}}
                <div class="krd-grid-4" style="gap:8px;margin-bottom:14px;">
                    <div class="krd-card-sm" style="border-left:3px solid #7C3AED;">
                        <div style="font-size:10px;color:#A8A29E;margin-bottom:3px;">Total</div>
                        <div style="font-size:20px;font-weight:700;color:#7C3AED;">{{ $rsvpTotal }}</div>
                    </div>
                    <div class="krd-card-sm" style="border-left:3px solid #10B981;">
                        <div style="font-size:10px;color:#A8A29E;margin-bottom:3px;">Attending</div>
                        <div style="font-size:20px;font-weight:700;color:#10B981;">{{ $rsvpConfirmed }}</div>
                        @if($rsvpAttendees > $rsvpConfirmed)
                        <div style="font-size:10px;color:#A8A29E;margin-top:1px;">{{ $rsvpAttendees }} total</div>
                        @endif
                    </div>
                    <div class="krd-card-sm" style="border-left:3px solid #EF4444;">
                        <div style="font-size:10px;color:#A8A29E;margin-bottom:3px;">Declined</div>
                        <div style="font-size:20px;font-weight:700;color:#EF4444;">{{ $rsvpDeclined }}</div>
                    </div>
                    <div class="krd-card-sm" style="border-left:3px solid #F59E0B;">
                        <div style="font-size:10px;color:#A8A29E;margin-bottom:3px;">Pending</div>
                        <div style="font-size:20px;font-weight:700;color:#F59E0B;">{{ $rsvpPending }}</div>
                    </div>
                </div>

                {{-- RSVP Link --}}
                @if($rsvpForm->is_active)
                <div style="background:#F5F3FF;border:1px solid #DDD6FE;border-radius:6px;padding:12px 14px;">
                    <div style="font-size:11px;font-weight:600;color:#7C3AED;text-transform:uppercase;letter-spacing:0.06em;margin-bottom:6px;">
                        Public RSVP Link
                    </div>
                    <div style="font-size:12px;color:#1C1917;font-family:monospace;word-break:break-all;margin-bottom:8px;">
                        {{ $rsvpForm->publicUrl() }}
                    </div>
                    <button
                        x-data
                        x-on:click="navigator.clipboard.writeText('{{ $rsvpForm->publicUrl() }}').then(() => {
                            $el.textContent = '✓ Copied!';
                            setTimeout(() => $el.textContent = 'Copy Link', 2000);
                        })"
                        class="krd-btn krd-btn-secondary krd-btn-sm">
                        Copy Link
                    </button>
                </div>
                @endif

                {{-- Deadline --}}
                @if($rsvpForm->deadline)
                <div style="margin-top:10px;font-size:12px;color:#78716C;">
                    @if($rsvpForm->isDeadlinePassed())
                    <span style="color:#EF4444;">⏰ RSVP deadline passed — {{ $rsvpForm->deadline->format('M d, Y') }}</span>
                    @else
                    ⏰ RSVP closes {{ $rsvpForm->deadline->format('M d, Y') }}
                    @endif
                </div>
                @endif
            </div>
            @elseif($event->rsvp_enabled && !$event->rsvpForm)
            <div style="border-top:1px solid #E7E5E4;padding-top:16px;margin-top:4px;">
                <div style="font-size:11px;font-weight:600;letter-spacing:0.06em;text-transform:uppercase;color:#A8A29E;margin-bottom:8px;">RSVP</div>
                <div style="font-size:13px;color:#A8A29E;">RSVP is being set up by your coordinator.</div>
            </div>
            @endif

        </div>
        @endforeach
    @endif
</div>