<div>
    <div style="margin-bottom:24px;">
        <h2 class="krd-heading-3" style="color:#1C1917;">Budget — {{ $event->name }}</h2>
    </div>

    @if(!$budget || (!$canSeeBalance && !$canSeeBreakdown && !$canSeeVendors && !$canSeeFee))
    <div class="krd-card" style="padding:40px;text-align:center;color:#A8A29E;">Nothing has been shared with you yet.</div>
    @else

    @if($canSeeBalance)
    @php
        $symbol = \App\Helpers\CurrencyHelper::symbol($budget->currency ?? 'NGN');
        $agreed = $budget->agreedBudget();
        $paid   = $budget->totalClientPaid();
        $outstanding = $budget->clientOutstanding();
    @endphp
    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(160px, 1fr));gap:12px;margin-bottom:20px;">
        <div class="krd-card" style="padding:16px;border-left:3px solid #7C3AED;">
            <div class="krd-label" style="margin-bottom:5px;">Agreed Budget</div>
            <div style="font-size:18px;font-weight:700;color:#7C3AED;">{{ $symbol }}{{ number_format($agreed, 2) }}</div>
        </div>
        <div class="krd-card" style="padding:16px;border-left:3px solid #10B981;">
            <div class="krd-label" style="margin-bottom:5px;">You've Paid</div>
            <div style="font-size:18px;font-weight:700;color:#10B981;">{{ $symbol }}{{ number_format($paid, 2) }}</div>
        </div>
        <div class="krd-card" style="padding:16px;border-left:3px solid {{ $outstanding > 0 ? '#EF4444' : '#10B981' }};">
            <div class="krd-label" style="margin-bottom:5px;">Outstanding</div>
            <div style="font-size:18px;font-weight:700;color:{{ $outstanding > 0 ? '#EF4444' : '#10B981' }};">{{ $symbol }}{{ number_format($outstanding, 2) }}</div>
        </div>
    </div>
    @endif

    @if($canSeeFee && $budget->fee_amount)
    <div class="krd-card" style="padding:16px;margin-bottom:20px;">
        <div class="krd-label" style="margin-bottom:8px;">Professional Fee</div>
        <div style="font-size:16px;font-weight:700;color:#7C3AED;">{{ $symbol ?? '' }}{{ number_format($budget->fee_amount, 2) }}</div>
        @if($budget->fee_note)<div style="font-size:11px;color:#78716C;margin-top:2px;">{{ $budget->fee_note }}</div>@endif
    </div>
    @endif

        @if($canSeeBreakdown)
    @php
        // Only items genuinely funded from the CLIENT'S OWN budget money
        // — never a planner-fronted personal cost, and never a vendor
        // the client already pays directly themselves (they know that
        // number already). This is specifically "here's what I did with
        // the money you gave me."
        $clientFundedItems = $budget->items->filter(
            fn($item) => $item->effectiveResponsibleParty() === 'client_budget'
        );
    @endphp
    <div style="margin-bottom:20px;">
        <div class="krd-label" style="margin-bottom:10px;">How Your Money Was Spent</div>
        <div class="krd-card" style="padding:0;overflow:hidden;">
            @forelse($clientFundedItems as $item)
            @php
                $itemOutstanding = max(0, (float) $item->actual - (float) $item->paid);
            @endphp
            <div style="display:flex;justify-content:space-between;align-items:center;padding:14px 16px;border-bottom:1px solid #F5F5F4;">
                <div style="font-size:13px;font-weight:500;color:#1C1917;">{{ $item->category }}</div>
                <div style="text-align:right;">
                    <div style="font-size:13px;font-weight:600;color:#10B981;">{{ $symbol ?? '' }}{{ number_format($item->paid, 2) }} <span style="font-weight:400;color:#A8A29E;">paid</span></div>
                    @if($itemOutstanding > 0)
                    <div style="font-size:11px;color:#EF4444;margin-top:1px;">{{ $symbol ?? '' }}{{ number_format($itemOutstanding, 2) }} outstanding</div>
                    @endif
                </div>
            </div>
            @empty
            <div style="padding:24px;text-align:center;color:#A8A29E;font-size:12px;">Nothing recorded yet.</div>
            @endforelse

            @if($clientFundedItems->isNotEmpty())
            <div style="padding:14px 16px;background:#F5F5F4;display:flex;justify-content:space-between;align-items:center;">
                <span style="font-size:12px;font-weight:600;color:#57534E;">Total</span>
                <div style="text-align:right;">
                    <span style="font-size:14px;font-weight:700;color:#10B981;">{{ $symbol ?? '' }}{{ number_format($clientFundedItems->sum('paid'), 2) }} paid</span>
                    @php $totalOutstanding = $clientFundedItems->sum(fn($i) => max(0, (float) $i->actual - (float) $i->paid)); @endphp
                    @if($totalOutstanding > 0)
                    <div style="font-size:11px;color:#EF4444;">{{ $symbol ?? '' }}{{ number_format($totalOutstanding, 2) }} outstanding</div>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>
    @endif

    @endif
</div>