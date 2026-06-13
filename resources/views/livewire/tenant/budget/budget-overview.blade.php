<div>
    {{-- Header --}}
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px;">
        <div>
            <div class="krd-label" style="margin-bottom:4px;">Operations</div>
            <h2 class="krd-heading-3" style="color:#1C1917;">Budget Overview</h2>
        </div>
    </div>

    {{-- Global KPIs --}}
    <div class="krd-budget-summary-grid" style="margin-bottom:24px;">
        <div class="krd-card" style="padding:16px;border-left:3px solid #7C3AED;">
            <div class="krd-label" style="margin-bottom:6px;">Total Agreed</div>
            <div style="font-size:20px;font-weight:700;color:#7C3AED;">₦{{ number_format($totalAgreed, 2) }}</div>
            <div style="font-size:11px;color:#A8A29E;margin-top:2px;">Across all events</div>
        </div>
        <div class="krd-card" style="padding:16px;border-left:3px solid #10B981;">
            <div class="krd-label" style="margin-bottom:6px;">Total Collected</div>
            <div style="font-size:20px;font-weight:700;color:#10B981;">₦{{ number_format($totalCollected, 2) }}</div>
            <div style="font-size:11px;color:#A8A29E;margin-top:2px;">From clients</div>
        </div>
        <div class="krd-card" style="padding:16px;border-left:3px solid {{ $totalOutstanding > 0 ? '#EF4444' : '#10B981' }};">
            <div class="krd-label" style="margin-bottom:6px;">Total Outstanding</div>
            <div style="font-size:20px;font-weight:700;color:{{ $totalOutstanding > 0 ? '#EF4444' : '#10B981' }};">₦{{ number_format($totalOutstanding, 2) }}</div>
            <div style="font-size:11px;color:#A8A29E;margin-top:2px;">Still owed by clients</div>
        </div>
        <div class="krd-card" style="padding:16px;border-left:3px solid #F59E0B;">
            <div class="krd-label" style="margin-bottom:6px;">Total Actual Spent</div>
            <div style="font-size:20px;font-weight:700;color:#F59E0B;">₦{{ number_format($totalActual, 2) }}</div>
            <div style="font-size:11px;color:#A8A29E;margin-top:2px;">On vendors & costs</div>
        </div>
    </div>

    {{-- Search --}}
    <div style="margin-bottom:16px;">
        <input
            wire:model.live.debounce.300ms="search"
            type="text"
            class="krd-input"
            placeholder="Search events..."
            style="max-width:280px;"
        />
    </div>

    {{-- Events with budgets --}}
    @if($eventsWithBudget->count() > 0)
    <div style="margin-bottom:24px;">
        <div style="font-size:11px;font-weight:600;letter-spacing:0.06em;text-transform:uppercase;color:#A8A29E;margin-bottom:10px;">Events with Budget</div>

        {{-- Desktop Table --}}
        <div class="krd-card" style="padding:0;overflow:hidden;" id="budget-overview-desktop">
            <div class="krd-table-wrap">
                <table class="krd-table">
                    <thead>
                        <tr>
                            <th>Event</th>
                            <th style="text-align:right;">Agreed</th>
                            <th style="text-align:right;">Collected</th>
                            <th style="text-align:right;">Outstanding</th>
                            <th style="text-align:right;">Actual Spent</th>
                            <th style="text-align:right;">Profit</th>
                            <th>Payment Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($eventsWithBudget as $event)
                        @php
                            $b           = $event->budget;
                            $agreed      = $b->agreedBudget();
                            $collected   = $b->totalClientPaid();
                            $outstanding = $b->clientOutstanding();
                            $actual      = $b->totalActual();
                            $profit      = $b->grossProfit();
                            $symbol      = match($b->currency ?? 'NGN') {
                                'NGN' => '₦', 'GHS' => '₵', 'GBP' => '£',
                                'USD' => '$', 'EUR' => '€', 'KES' => 'KSh', 'ZAR' => 'R',
                                default => '₦'
                            };
                        @endphp
                        <tr>
                            <td>
                                <div style="font-size:13px;font-weight:500;color:#1C1917;">{{ $event->name }}</div>
                                <div style="font-size:11px;color:#A8A29E;">
                                    {{ $event->date?->format('M d, Y') ?? 'Date TBC' }}
                                    @if($event->status)
                                    · <span style="color:{{ $event->status->color }};">{{ $event->status->name }}</span>
                                    @endif
                                </div>
                            </td>
                            <td style="text-align:right;font-size:13px;color:#7C3AED;font-weight:500;">{{ $symbol }}{{ number_format($agreed, 2) }}</td>
                            <td style="text-align:right;font-size:13px;color:#10B981;font-weight:500;">{{ $symbol }}{{ number_format($collected, 2) }}</td>
                            <td style="text-align:right;font-size:13px;font-weight:500;color:{{ $outstanding > 0 ? '#EF4444' : '#10B981' }};">{{ $symbol }}{{ number_format($outstanding, 2) }}</td>
                            <td style="text-align:right;font-size:13px;color:#F59E0B;font-weight:500;">{{ $symbol }}{{ number_format($actual, 2) }}</td>
                            <td style="text-align:right;font-size:13px;font-weight:600;color:{{ $profit >= 0 ? '#10B981' : '#EF4444' }};">
                                {{ $profit < 0 ? '-' : '+' }}{{ $symbol }}{{ number_format(abs($profit), 2) }}
                            </td>
                            <td>
                                @if($outstanding <= 0)
                                    <span class="krd-badge krd-badge-green">Fully Paid</span>
                                @elseif($collected > 0)
                                    <span class="krd-badge krd-badge-amber">Partial</span>
                                @else
                                    <span class="krd-badge krd-badge-red">Unpaid</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('tenant.events.budget', $event->slug) }}" wire:navigate class="krd-btn krd-btn-secondary krd-btn-sm">
                                    View Budget
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Mobile Cards --}}
        <div id="budget-overview-mobile" style="display:flex;flex-direction:column;gap:10px;">
            @foreach($eventsWithBudget as $event)
            @php
                $b           = $event->budget;
                $agreed      = $b->agreedBudget();
                $collected   = $b->totalClientPaid();
                $outstanding = $b->clientOutstanding();
                $actual      = $b->totalActual();
                $profit      = $b->grossProfit();
                $symbol      = match($b->currency ?? 'NGN') {
                    'NGN' => '₦', 'GHS' => '₵', 'GBP' => '£',
                    'USD' => '$', 'EUR' => '€', 'KES' => 'KSh', 'ZAR' => 'R',
                    default => '₦'
                };
            @endphp
            <div class="krd-card" style="padding:16px;">
                <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:10px;margin-bottom:12px;">
                    <div>
                        <div style="font-size:13px;font-weight:600;color:#1C1917;">{{ $event->name }}</div>
                        <div style="font-size:11px;color:#A8A29E;margin-top:2px;">{{ $event->date?->format('M d, Y') ?? 'Date TBC' }}</div>
                    </div>
                    @if($outstanding <= 0)
                        <span class="krd-badge krd-badge-green">Fully Paid</span>
                    @elseif($collected > 0)
                        <span class="krd-badge krd-badge-amber">Partial</span>
                    @else
                        <span class="krd-badge krd-badge-red">Unpaid</span>
                    @endif
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:12px;">
                    <div>
                        <div style="font-size:10px;color:#A8A29E;margin-bottom:2px;">Agreed</div>
                        <div style="font-size:14px;font-weight:700;color:#7C3AED;">{{ $symbol }}{{ number_format($agreed, 2) }}</div>
                    </div>
                    <div>
                        <div style="font-size:10px;color:#A8A29E;margin-bottom:2px;">Collected</div>
                        <div style="font-size:14px;font-weight:700;color:#10B981;">{{ $symbol }}{{ number_format($collected, 2) }}</div>
                    </div>
                    <div>
                        <div style="font-size:10px;color:#A8A29E;margin-bottom:2px;">Outstanding</div>
                        <div style="font-size:14px;font-weight:700;color:{{ $outstanding > 0 ? '#EF4444' : '#10B981' }};">{{ $symbol }}{{ number_format($outstanding, 2) }}</div>
                    </div>
                    <div>
                        <div style="font-size:10px;color:#A8A29E;margin-bottom:2px;">Profit</div>
                        <div style="font-size:14px;font-weight:700;color:{{ $profit >= 0 ? '#10B981' : '#EF4444' }};">{{ $profit < 0 ? '-' : '+' }}{{ $symbol }}{{ number_format(abs($profit), 2) }}</div>
                    </div>
                </div>
                <a href="{{ route('tenant.events.budget', $event->slug) }}" wire:navigate class="krd-btn krd-btn-secondary krd-btn-sm">
                    View Budget →
                </a>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Events without budgets --}}
    @if($eventsWithoutBudget->count() > 0)
    <div>
        <div style="font-size:11px;font-weight:600;letter-spacing:0.06em;text-transform:uppercase;color:#A8A29E;margin-bottom:10px;">Events Without Budget</div>
        <div class="krd-card" style="padding:0;overflow:hidden;">
            @foreach($eventsWithoutBudget as $event)
            <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 16px;border-bottom:1px solid #E7E5E4;flex-wrap:wrap;gap:8px;">
                <div>
                    <div style="font-size:13px;font-weight:500;color:#1C1917;">{{ $event->name }}</div>
                    <div style="font-size:11px;color:#A8A29E;">
                        {{ $event->date?->format('M d, Y') ?? 'Date TBC' }}
                        @if($event->agreed_budget)
                        · Agreed: ₦{{ number_format($event->agreed_budget, 2) }}
                        @endif
                    </div>
                </div>
                <a href="{{ route('tenant.events.budget', $event->slug) }}" wire:navigate class="krd-btn krd-btn-secondary krd-btn-sm">
                    Set Up Budget
                </a>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    @if($eventsWithBudget->count() === 0 && $eventsWithoutBudget->count() === 0)
    <div class="krd-card">
        <div class="krd-empty-state">
            <div class="krd-empty-state-icon">💰</div>
            <div class="krd-empty-state-title">No events found</div>
            <div class="krd-empty-state-desc">Create an event first, then set up its budget.</div>
        </div>
    </div>
    @endif

</div>

<style>
.krd-budget-summary-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 10px;
}

@media (min-width: 768px) {
    .krd-budget-summary-grid { grid-template-columns: repeat(4, 1fr); }
    #budget-overview-desktop { display: block !important; }
    #budget-overview-mobile  { display: none !important; }
}

@media (max-width: 767px) {
    #budget-overview-desktop { display: none !important; }
    #budget-overview-mobile  { display: flex !important; }
}
</style>