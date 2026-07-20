<div>
    <div style="margin-bottom:28px;">
        <div class="krd-label" style="margin-bottom:4px;">Billing</div>
        <h2 class="krd-heading-3" style="color:#1C1917;">Billing & Subscription</h2>
    </div>

    {{-- Current subscription card --}}
    @if($subscription)
    <div class="krd-card" style="padding:24px;margin-bottom:20px;">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:20px;">
            <div>
                <div class="krd-label" style="margin-bottom:4px;">Current Plan</div>
                <div style="font-size:22px;font-weight:700;color:#1C1917;">{{ $subscription->plan?->name ?? 'No Plan' }}</div>
                <div style="font-size:12px;color:#A8A29E;margin-top:3px;">{{ ucfirst($subscription->billing_cycle ?? 'trial') }}</div>
            </div>
            <div style="display:flex;gap:8px;align-items:flex-start;">
                @if($subscription->isActive())
                <span class="krd-badge krd-badge-green">Active</span>
                @elseif($subscription->isInGracePeriod())
                <span class="krd-badge krd-badge-amber">Grace Period</span>
                @else
                <span class="krd-badge krd-badge-red">Expired</span>
                @endif
                <a href="{{ route('tenant.billing.upgrade') }}" wire:navigate
                    class="krd-btn krd-btn-primary krd-btn-sm">
                    {{ $subscription->isActive() ? 'Upgrade' : 'Renew' }}
                </a>
            </div>
        </div>

        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;">
            <div style="background:#F5F5F4;border-radius:6px;padding:14px;">
                <div style="font-size:11px;color:#A8A29E;margin-bottom:4px;text-transform:uppercase;letter-spacing:0.06em;">Status</div>
                <div style="font-size:14px;font-weight:600;color:#1C1917;">{{ ucfirst($subscription->status) }}</div>
            </div>
            <div style="background:#F5F5F4;border-radius:6px;padding:14px;">
                <div style="font-size:11px;color:#A8A29E;margin-bottom:4px;text-transform:uppercase;letter-spacing:0.06em;">
                    {{ $subscription->isTrialing() ? 'Trial Ends' : 'Expires' }}
                </div>
                <div style="font-size:14px;font-weight:600;color:#1C1917;">
                    {{ ($subscription->expires_at ?? $subscription->trial_ends_at)?->format('d M Y') ?? '—' }}
                </div>
            </div>
            <div style="background:#F5F5F4;border-radius:6px;padding:14px;">
                <div style="font-size:11px;color:#A8A29E;margin-bottom:4px;text-transform:uppercase;letter-spacing:0.06em;">Last Payment</div>
                <div style="font-size:14px;font-weight:600;color:#1C1917;">
                    {{ $subscription->invoices->first()?->paid_at?->format('d M Y') ?? '—' }}
                </div>
            </div>
        </div>
    </div>
    @else
    <div class="krd-card" style="padding:24px;margin-bottom:20px;text-align:center;">
        <div style="font-size:48px;margin-bottom:12px;">📦</div>
        <div style="font-size:16px;font-weight:600;color:#1C1917;margin-bottom:8px;">No active subscription</div>
        <p style="font-size:13px;color:#78716C;margin-bottom:16px;">Choose a plan to get full access to Koordli.</p>
        <a href="{{ route('tenant.billing.upgrade') }}" wire:navigate class="krd-btn krd-btn-primary">
            View Plans →
        </a>
    </div>
    @endif

    {{-- Invoice history --}}
    @if($invoices->isNotEmpty())
    <div class="krd-label" style="margin-bottom:12px;">Payment History</div>
    <div class="krd-card" style="padding:0;overflow:hidden;" id="invoices-desktop">
        <div class="krd-table-wrap">
            <table class="krd-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Plan</th>
                        <th>Amount</th>
                        <th>Currency</th>
                        <th>Gateway</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoices as $invoice)
                    <tr>
                        <td style="font-size:12px;color:#78716C;">{{ $invoice->created_at->format('d M Y') }}</td>
                        <td style="font-size:13px;color:#1C1917;">{{ $invoice->subscription?->plan?->name ?? '—' }}</td>
                        <td style="font-size:13px;font-weight:500;color:#1C1917;">
                            {{ \App\Helpers\CurrencyHelper::symbol($invoice->currency) }}{{ number_format($invoice->amount, 2) }}
                        </td>
                        <td style="font-size:12px;color:#78716C;">{{ $invoice->currency }}</td>
                        <td style="font-size:12px;color:#78716C;">{{ ucfirst($invoice->gateway) }}</td>
                        <td>
                            <span class="krd-badge {{ $invoice->status === 'paid' ? 'krd-badge-green' : 'krd-badge-amber' }}">
                                {{ ucfirst($invoice->status) }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div id="invoices-mobile" style="display:flex;flex-direction:column;gap:10px;">
        @foreach($invoices as $invoice)
        <div class="krd-card" style="padding:16px;">
            <div style="display:flex;justify-content:space-between;margin-bottom:6px;">
                <div style="font-size:14px;font-weight:600;color:#1C1917;">
                    {{ \App\Helpers\CurrencyHelper::symbol($invoice->currency) }}{{ number_format($invoice->amount, 2) }}
                </div>
                <span class="krd-badge {{ $invoice->status === 'paid' ? 'krd-badge-green' : 'krd-badge-amber' }}">
                    {{ ucfirst($invoice->status) }}
                </span>
            </div>
            <div style="font-size:12px;color:#78716C;">
                {{ $invoice->created_at->format('d M Y') }} · {{ ucfirst($invoice->gateway) }} · {{ $invoice->currency }}
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>

<style>
@media (min-width:768px) { #invoices-desktop { display:block !important; } #invoices-mobile { display:none !important; } }
@media (max-width:767px) { #invoices-desktop { display:none !important; } #invoices-mobile { display:flex !important; } }
</style>