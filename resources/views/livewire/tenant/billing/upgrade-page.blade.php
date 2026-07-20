<div>
    {{-- Header --}}
    <div style="margin-bottom:28px;">
        <div class="krd-label" style="margin-bottom:4px;">Billing</div>
        <h2 class="krd-heading-3" style="color:#1C1917;">Choose Your Plan</h2>
        <p style="font-size:13px;color:#78716C;margin-top:4px;">
            Upgrade to keep full access to all Koordli features.
        </p>
    </div>

    {{-- Current subscription status --}}
    @if($subscription)
    <div style="background:#FEF3C7;border:1px solid #FDE68A;border-radius:6px;padding:12px 16px;margin-bottom:24px;display:flex;align-items:center;gap:10px;">
        <span style="font-size:16px;">⚠️</span>
        <div style="font-size:13px;color:#92400E;">
            @if($subscription->isLocked())
            Your account is locked. Renew to restore full access.
            @elseif($subscription->isInGracePeriod())
            Your plan has expired. You're in your grace period — {{ $subscription->grace_until?->diffForHumans() }} remaining.
            @elseif($subscription->isTrialing())
            Your trial ends {{ $subscription->trial_ends_at?->diffForHumans() }}.
            @else
            Your plan expires {{ ($subscription->expires_at ?? $subscription->trial_ends_at)?->diffForHumans() }}.
            @endif
        </div>
    </div>
    @endif

    {{-- Billing cycle toggle — Alpine owned, instant --}}
    <div style="display:flex;align-items:center;justify-content:center;gap:0;margin-bottom:28px;"
        x-data="{
            cycle: '{{ $selectedCycle }}',
            setCycle(val) {
                this.cycle = val;
                $wire.set('selectedCycle', val);
            }
        }">
        <div style="display:flex;background:#F5F5F4;border-radius:8px;padding:4px;gap:4px;">
            <button type="button"
                x-on:click="setCycle('monthly')"
                :style="cycle === 'monthly'
                    ? 'padding:8px 20px;border-radius:6px;border:none;font-size:13px;font-weight:600;cursor:pointer;font-family:inherit;background:#7C3AED;color:#fff;'
                    : 'padding:8px 20px;border-radius:6px;border:none;font-size:13px;font-weight:500;cursor:pointer;font-family:inherit;background:transparent;color:#78716C;'">
                Monthly
            </button>
            <button type="button"
                x-on:click="setCycle('annual')"
                :style="cycle === 'annual'
                    ? 'padding:8px 20px;border-radius:6px;border:none;font-size:13px;font-weight:600;cursor:pointer;font-family:inherit;background:#7C3AED;color:#fff;'
                    : 'padding:8px 20px;border-radius:6px;border:none;font-size:13px;font-weight:500;cursor:pointer;font-family:inherit;background:transparent;color:#78716C;'">
                Annual
                <span x-show="cycle === 'annual'"
                    style="font-size:10px;background:rgba(255,255,255,0.2);color:#fff;padding:2px 6px;border-radius:10px;margin-left:4px;">
                    Save more
                </span>
            </button>
        </div>
    </div>

    {{-- Gateway selector --}}
    @if(count($enabled) > 1)
    <div style="display:flex;align-items:center;justify-content:center;gap:8px;margin-bottom:24px;"
        x-data="{
            gateway: '{{ $selectedGateway }}',
            setGateway(val) {
                this.gateway = val;
                $wire.set('selectedGateway', val);
            }
        }">
        <span style="font-size:12px;color:#A8A29E;">Pay with:</span>
        @foreach($enabled as $gw)
        <button type="button"
            x-on:click="setGateway('{{ $gw }}')"
            :style="gateway === '{{ $gw }}'
                ? 'padding:6px 14px;border-radius:6px;border:1.5px solid #7C3AED;background:#7C3AED;color:#fff;font-size:12px;font-weight:600;cursor:pointer;font-family:inherit;'
                : 'padding:6px 14px;border-radius:6px;border:1.5px solid #E7E5E4;background:transparent;color:#78716C;font-size:12px;font-weight:500;cursor:pointer;font-family:inherit;'">
            {{ ucfirst($gw) }}
        </button>
        @endforeach
    </div>
    @endif

    {{-- Plans grid --}}
    <div class="krd-grid-3" style="gap:16px;">
        @foreach($plans as $plan)
        @php
            $pricing = $pricingData[$plan->id][$selectedCycle] ?? null;
            $canSelect = $selectedCycle === 'monthly' ? $plan->allowsMonthly() : $plan->allowsAnnual();
        @endphp
        @if(!$canSelect) @continue @endif

        <div class="krd-card" style="padding:24px;position:relative;{{ $plan->is_featured ? 'border:2px solid #7C3AED;' : '' }}">
            @if($plan->is_featured)
            <div style="position:absolute;top:-1px;left:20px;background:#7C3AED;color:#fff;font-size:10px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;padding:3px 10px;border-radius:0 0 6px 6px;">
                ⭐ Recommended
            </div>
            @endif

            <div style="margin-top:{{ $plan->is_featured ? '16px' : '0' }};">
                <div style="font-size:18px;font-weight:700;color:#1C1917;margin-bottom:4px;">{{ $plan->name }}</div>

                @if($pricing)
                <div style="margin-bottom:16px;">
                    <div style="font-size:28px;font-weight:700;color:#7C3AED;line-height:1;">
                        {{ \App\Helpers\CurrencyHelper::symbol($pricing['currency']) }}{{ number_format($pricing['amount_with_charges'], 0) }}
                    </div>
                    <div style="font-size:11px;color:#A8A29E;margin-top:3px;">
                        per {{ $selectedCycle === 'annual' ? 'year' : 'month' }} · {{ $pricing['currency'] }}
                        @if($pricing['fee_absorbed'] > 0)
                        · <span style="color:#10B981;">gateway fees included</span>
                        @endif
                    </div>
                    @if($selectedCycle === 'annual' && $plan->annual_discount_percent > 0)
                    <div style="font-size:11px;color:#10B981;margin-top:3px;font-weight:500;">
                        Save {{ $plan->annual_discount_percent }}% vs monthly
                    </div>
                    @endif
                    @if($pricing['currency'] !== 'NGN')
                    <div style="font-size:10px;color:#A8A29E;margin-top:2px;">
                        ≈ {{ \App\Helpers\CurrencyHelper::symbol('NGN') }}{{ number_format($pricing['base_amount'], 0) }} NGN
                        (rate: 1 NGN = {{ number_format($pricing['exchange_rate'], 4) }} {{ $pricing['currency'] }})
                    </div>
                    @endif
                </div>
                @endif

                <div class="krd-divider" style="margin:14px 0;"></div>

                @if(!empty($plan->limits))
                <div style="margin-bottom:14px;">
                    @foreach($plan->limits as $key => $val)
                    <div style="display:flex;justify-content:space-between;padding:5px 0;font-size:12px;border-bottom:1px solid #F5F5F4;">
                        <span style="color:#78716C;">{{ str_replace('_', ' ', ucwords($key, '_')) }}</span>
                        <span style="font-weight:600;color:#1C1917;">{{ $val == -1 ? '∞' : $val }}</span>
                    </div>
                    @endforeach
                </div>
                @endif

                @if($pricing)
                <button wire:click="checkout({{ $plan->id }})" wire:loading.attr="disabled"
                    class="krd-btn krd-btn-primary"
                    style="width:100%;padding:12px;font-size:14px;font-weight:600;">
                    <span wire:loading.remove wire:target="checkout({{ $plan->id }})">
                        Get {{ $plan->name }} →
                    </span>
                    <span wire:loading wire:target="checkout({{ $plan->id }})">Processing...</span>
                </button>
                @else
                <div style="font-size:12px;color:#A8A29E;text-align:center;padding:10px 0;">
                    Not available for {{ $selectedCycle }} billing
                </div>
                @endif
            </div>
        </div>
        @endforeach
    </div>

    {{-- Note --}}
    <div style="text-align:center;margin-top:20px;font-size:12px;color:#A8A29E;line-height:1.7;">
        All prices include gateway fees. No hidden charges.<br>
        Subscriptions are renewed manually — you'll receive a reminder before expiry.
    </div>
</div>