<div>
    <div style="margin-bottom:28px;">
        <div class="krd-label" style="margin-bottom:4px;">Platform Management</div>
        <h2 class="krd-heading-3" style="color:#1C1917;">Billing Configuration</h2>
    </div>

    {{-- Tabs --}}
    <div style="display:flex;gap:4px;border-bottom:1px solid #E7E5E4;margin-bottom:24px;">
        @foreach(['settings' => 'Settings', 'gateways' => 'Gateway Charges', 'keys' => 'API Keys'] as $tab => $label)
        <button type="button" wire:click="setTab('{{ $tab }}')"
            style="padding:10px 16px;font-size:13px;font-weight:500;border:none;background:none;cursor:pointer;margin-bottom:-1px;
                {{ $activeTab === $tab ? 'border-bottom:2px solid #7C3AED;color:#7C3AED;' : 'border-bottom:2px solid transparent;color:#78716C;' }}">
            {{ $label }}
        </button>
        @endforeach
    </div>

    {{-- Settings Tab --}}
    @if($activeTab === 'settings')
    <div style="display:grid;grid-template-columns:1fr 340px;gap:24px;align-items:start;" id="billing-settings-grid">

        {{-- Left column --}}
        <div>

            {{-- Billing Overview -- TOP --}}
            <div class="krd-card" style="padding:24px;margin-bottom:16px;">
                <div class="krd-label" style="margin-bottom:16px;">Billing Overview</div>
                @php
                    $totalActive  = \App\Models\Central\Subscription::where('status', 'active')->count();
                    $totalTrial   = \App\Models\Central\Subscription::where('status', 'trial')->count();
                    $totalExpired = \App\Models\Central\Subscription::where('status', 'expired')->count();
                    $totalGrace   = \App\Models\Central\Subscription::whereNotNull('grace_until')
                        ->where('grace_until', '>', now())
                        ->where('status', 'expired')->count();
                    $revenueThisMonth = \App\Models\Central\SubscriptionInvoice::where('status', 'paid')
                        ->whereMonth('paid_at', now()->month)
                        ->whereYear('paid_at', now()->year)
                        ->sum('amount_ngn');
                    $revenueLastMonth = \App\Models\Central\SubscriptionInvoice::where('status', 'paid')
                        ->whereMonth('paid_at', now()->subMonth()->month)
                        ->whereYear('paid_at', now()->subMonth()->year)
                        ->sum('amount_ngn');
                    $recentInvoices = \App\Models\Central\SubscriptionInvoice::where('status', 'paid')
                        ->with(['tenant', 'subscription.plan'])
                        ->orderByDesc('paid_at')
                        ->limit(5)
                        ->get();
                @endphp

                {{-- Stats --}}
                <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:10px;margin-bottom:20px;" id="billing-stats-grid">
                    <div style="background:#F0FDF4;border:1px solid #86EFAC;border-radius:6px;padding:14px;text-align:center;">
                        <div style="font-size:10px;color:#166534;font-weight:600;letter-spacing:0.06em;text-transform:uppercase;margin-bottom:4px;">Active</div>
                        <div style="font-size:24px;font-weight:700;color:#10B981;">{{ $totalActive }}</div>
                    </div>
                    <div style="background:#F5F3FF;border:1px solid #DDD6FE;border-radius:6px;padding:14px;text-align:center;">
                        <div style="font-size:10px;color:#5B21B6;font-weight:600;letter-spacing:0.06em;text-transform:uppercase;margin-bottom:4px;">On Trial</div>
                        <div style="font-size:24px;font-weight:700;color:#7C3AED;">{{ $totalTrial }}</div>
                    </div>
                    <div style="background:#FEF3C7;border:1px solid #FDE68A;border-radius:6px;padding:14px;text-align:center;">
                        <div style="font-size:10px;color:#92400E;font-weight:600;letter-spacing:0.06em;text-transform:uppercase;margin-bottom:4px;">Grace</div>
                        <div style="font-size:24px;font-weight:700;color:#F59E0B;">{{ $totalGrace }}</div>
                    </div>
                    <div style="background:#FEF2F2;border:1px solid #FECACA;border-radius:6px;padding:14px;text-align:center;">
                        <div style="font-size:10px;color:#991B1B;font-weight:600;letter-spacing:0.06em;text-transform:uppercase;margin-bottom:4px;">Expired</div>
                        <div style="font-size:24px;font-weight:700;color:#EF4444;">{{ $totalExpired }}</div>
                    </div>
                </div>

                {{-- Revenue --}}
                <div style="background:#F5F5F4;border-radius:8px;padding:16px;margin-bottom:20px;">
                    <div style="font-size:11px;font-weight:600;letter-spacing:0.06em;text-transform:uppercase;color:#A8A29E;margin-bottom:12px;">Revenue (NGN)</div>
                    <div style="display:flex;justify-content:space-between;align-items:flex-end;flex-wrap:wrap;gap:12px;">
                        <div>
                            <div style="font-size:11px;color:#A8A29E;margin-bottom:2px;">This Month</div>
                            <div style="font-size:22px;font-weight:700;color:#10B981;">₦{{ number_format($revenueThisMonth, 0) }}</div>
                        </div>
                        <div style="text-align:right;">
                            <div style="font-size:11px;color:#A8A29E;margin-bottom:2px;">Last Month</div>
                            <div style="font-size:16px;font-weight:600;color:#78716C;">₦{{ number_format($revenueLastMonth, 0) }}</div>
                        </div>
                    </div>
                </div>

                {{-- Recent payments --}}
                @if($recentInvoices->isNotEmpty())
                <div>
                    <div style="font-size:11px;font-weight:600;letter-spacing:0.06em;text-transform:uppercase;color:#A8A29E;margin-bottom:10px;">Recent Payments</div>
                    @foreach($recentInvoices as $invoice)
                    <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 0;border-bottom:1px solid #F5F5F4;gap:8px;flex-wrap:wrap;">
                        <div style="flex:1;min-width:0;">
                            <div style="font-size:13px;font-weight:500;color:#1C1917;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                {{ $invoice->tenant?->name ?? 'Unknown' }}
                            </div>
                            <div style="font-size:11px;color:#A8A29E;">
                                {{ $invoice->subscription?->plan?->name ?? '—' }} · {{ $invoice->paid_at?->format('d M Y') }}
                            </div>
                        </div>
                        <div style="font-size:13px;font-weight:600;color:#10B981;flex-shrink:0;">
                            ₦{{ number_format($invoice->amount_ngn ?? $invoice->amount, 0) }}
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div style="text-align:center;padding:12px 0;font-size:13px;color:#A8A29E;">
                    No payments yet.
                </div>
                @endif
            </div>

            {{-- Subscription Settings -- BELOW OVERVIEW --}}
            <div class="krd-card" style="padding:24px;">
                <div class="krd-label" style="margin-bottom:16px;">Subscription Settings</div>

                <div class="krd-grid-2" style="gap:12px;">
                    <div class="krd-input-group">
                        <label class="krd-label-text">Grace Period (days)</label>
                        <input wire:model="grace_period_days" type="number" min="0" max="30" class="krd-input" />
                        <span class="krd-input-hint">Days after expiry before account is locked.</span>
                    </div>
                    <div class="krd-input-group">
                        <label class="krd-label-text">First Reminder (days before expiry)</label>
                        <input wire:model="reminder_days" type="number" min="1" max="30" class="krd-input" />
                    </div>
                    <div class="krd-input-group">
                        <label class="krd-label-text">Urgent Reminder (days before expiry)</label>
                        <input wire:model="reminder_days_urgent" type="number" min="1" max="14" class="krd-input" />
                    </div>
                    <div class="krd-input-group">
                        <label class="krd-label-text">Exchange Rate Cache (hours)</label>
                        <input wire:model="frankfurter_cache_hours" type="number" min="1" max="168" class="krd-input" />
                        <span class="krd-input-hint">Frankfurter API cache duration.</span>
                    </div>
                </div>

                {{-- Gateway toggles --}}
                <div class="krd-input-group">
                    <label class="krd-label-text">Enabled Gateways</label>
                    <div style="display:flex;gap:16px;flex-wrap:wrap;padding-top:4px;"
                        x-data="{
                            gateways: {{ json_encode($enabled_gateways) }},
                            toggle(gw) {
                                if (this.gateways.includes(gw)) {
                                    this.gateways = this.gateways.filter(g => g !== gw);
                                } else {
                                    this.gateways = [...this.gateways, gw];
                                }
                                $wire.toggleGateway(gw);
                            },
                            isOn(gw) {
                                return this.gateways.includes(gw);
                            }
                        }">
                        @foreach(['paystack' => 'Paystack', 'flutterwave' => 'Flutterwave'] as $gw => $gwLabel)
                        <div style="display:flex;align-items:center;gap:8px;">
                            <div x-on:click="toggle('{{ $gw }}')"
                                :style="isOn('{{ $gw }}')
                                    ? 'width:44px;height:24px;border-radius:12px;background:#7C3AED;cursor:pointer;position:relative;flex-shrink:0;'
                                    : 'width:44px;height:24px;border-radius:12px;background:#D6D3D1;cursor:pointer;position:relative;flex-shrink:0;'">
                                <div :style="isOn('{{ $gw }}')
                                    ? 'position:absolute;top:3px;right:3px;width:18px;height:18px;border-radius:50%;background:#fff;transition:all 200ms;'
                                    : 'position:absolute;top:3px;left:3px;width:18px;height:18px;border-radius:50%;background:#fff;transition:all 200ms;'">
                                </div>
                            </div>
                            <span style="font-size:13px;color:#1C1917;">{{ $gwLabel }}</span>
                            <span x-text="isOn('{{ $gw }}') ? 'Enabled' : 'Disabled'"
                                :style="isOn('{{ $gw }}') ? 'font-size:11px;color:#10B981;' : 'font-size:11px;color:#A8A29E;'">
                            </span>
                        </div>
                        @endforeach
                    </div>
                </div>

                <button wire:click="saveSettings" wire:loading.attr="disabled" class="krd-btn krd-btn-primary">
                    <span wire:loading.remove wire:target="saveSettings">Save Settings</span>
                    <span wire:loading wire:target="saveSettings">Saving...</span>
                </button>
            </div>
        </div>

        {{-- Right column — sticky: Calculator + Gateway Rates --}}
        <div style="display:flex;flex-direction:column;gap:12px;position:sticky;top:80px;" id="billing-settings-right">

            {{-- Fee Absorption Calculator --}}
            <div class="krd-card" style="padding:20px;">
                <div style="font-size:13px;font-weight:600;color:#1C1917;margin-bottom:6px;">⚡ Fee Absorption Calculator</div>
                <p style="font-size:12px;color:#78716C;line-height:1.6;margin-bottom:14px;">
                    Enter the amount you want to receive after gateway fees to see what to charge your tenant.
                </p>

                <div x-data="{
                    desiredAmount: 5000,
                    gateway: 'paystack',
                    region: 'local',
                    get result() {
                        const configs = {
                            paystack_local:            { pct: 0.015, fixed: 100, cap: 2000 },
                            paystack_international:    { pct: 0.038, fixed: 100, cap: null },
                            flutterwave_local:         { pct: 0.014, fixed: 0,   cap: 2800 },
                            flutterwave_international: { pct: 0.038, fixed: 0,   cap: null },
                        };
                        const key = this.gateway + '_' + this.region;
                        const cfg = configs[key];
                        if (!cfg) return null;
                        let absorbed = (this.desiredAmount + cfg.fixed) / (1 - cfg.pct);
                        let fee = absorbed - this.desiredAmount;
                        if (cfg.cap && fee > cfg.cap) {
                            absorbed = this.desiredAmount + cfg.cap;
                            fee = cfg.cap;
                        }
                        return {
                            charge: Math.ceil(absorbed),
                            fee: Math.ceil(fee),
                            receive: Math.ceil(absorbed - fee),
                        };
                    }
                }">
                    <div class="krd-input-group">
                        <label class="krd-label-text">Amount you want to receive (₦)</label>
                        <input x-model.number="desiredAmount" type="number" min="0" class="krd-input" placeholder="5000" />
                    </div>

                    <div class="krd-grid-2" style="gap:8px;margin-bottom:14px;">
                        <div>
                            <label class="krd-label-text">Gateway</label>
                            <div style="display:flex;gap:4px;margin-top:4px;">
                                @foreach(['paystack' => 'Paystack', 'flutterwave' => 'Flutterwave'] as $gw => $gwLabel)
                                <button type="button"
                                    x-on:click="gateway = '{{ $gw }}'"
                                    :style="gateway === '{{ $gw }}'
                                        ? 'flex:1;padding:5px 4px;border-radius:5px;border:1.5px solid #7C3AED;background:#7C3AED;color:#fff;font-size:10px;font-weight:600;cursor:pointer;font-family:inherit;'
                                        : 'flex:1;padding:5px 4px;border-radius:5px;border:1.5px solid #E7E5E4;background:transparent;color:#78716C;font-size:10px;cursor:pointer;font-family:inherit;'">
                                    {{ $gwLabel }}
                                </button>
                                @endforeach
                            </div>
                        </div>
                        <div>
                            <label class="krd-label-text">Region</label>
                            <div style="display:flex;gap:4px;margin-top:4px;">
                                <button type="button"
                                    x-on:click="region = 'local'"
                                    :style="region === 'local'
                                        ? 'flex:1;padding:5px 4px;border-radius:5px;border:1.5px solid #7C3AED;background:#7C3AED;color:#fff;font-size:10px;font-weight:600;cursor:pointer;font-family:inherit;'
                                        : 'flex:1;padding:5px 4px;border-radius:5px;border:1.5px solid #E7E5E4;background:transparent;color:#78716C;font-size:10px;cursor:pointer;font-family:inherit;'">
                                    Local
                                </button>
                                <button type="button"
                                    x-on:click="region = 'international'"
                                    :style="region === 'international'
                                        ? 'flex:1;padding:5px 4px;border-radius:5px;border:1.5px solid #7C3AED;background:#7C3AED;color:#fff;font-size:10px;font-weight:600;cursor:pointer;font-family:inherit;'
                                        : 'flex:1;padding:5px 4px;border-radius:5px;border:1.5px solid #E7E5E4;background:transparent;color:#78716C;font-size:10px;cursor:pointer;font-family:inherit;'">
                                    Intl
                                </button>
                            </div>
                        </div>
                    </div>

                    <template x-if="result">
                        <div style="background:#F5F3FF;border:1px solid #DDD6FE;border-radius:8px;padding:14px;">
                            <div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid #EDE9FE;font-size:13px;">
                                <span style="color:#78716C;">Charge tenant</span>
                                <span style="font-weight:700;color:#7C3AED;">₦<span x-text="result.charge.toLocaleString()"></span></span>
                            </div>
                            <div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid #EDE9FE;font-size:13px;">
                                <span style="color:#78716C;">Fee absorbed</span>
                                <span style="font-weight:500;color:#EF4444;">- ₦<span x-text="result.fee.toLocaleString()"></span></span>
                            </div>
                            <div style="display:flex;justify-content:space-between;padding:6px 0;font-size:13px;">
                                <span style="color:#78716C;">You receive</span>
                                <span style="font-weight:700;color:#10B981;">₦<span x-text="result.receive.toLocaleString()"></span></span>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            {{-- Current Gateway Rates --}}
            <div class="krd-card" style="padding:20px;">
                <div style="font-size:13px;font-weight:600;color:#1C1917;margin-bottom:12px;">📋 Current Gateway Rates</div>
                <div style="display:flex;flex-direction:column;gap:8px;">
                    @foreach([
                        ['gateway' => 'Paystack', 'region' => 'Nigeria',       'rate' => '1.5% + ₦100 (cap ₦2,000)'],
                        ['gateway' => 'Paystack', 'region' => 'International', 'rate' => '3.8% + ₦100'],
                        ['gateway' => 'Flutterwave', 'region' => 'Nigeria',    'rate' => '1.4% (cap ₦2,800)'],
                        ['gateway' => 'Flutterwave', 'region' => 'International', 'rate' => '3.8%'],
                    ] as $rate)
                    <div style="padding:8px;background:#F5F5F4;border-radius:6px;">
                        <div style="font-size:11px;font-weight:600;color:#1C1917;margin-bottom:2px;">{{ $rate['gateway'] }} · {{ $rate['region'] }}</div>
                        <div style="font-size:11px;color:#78716C;">{{ $rate['rate'] }}</div>
                    </div>
                    @endforeach
                    <p style="font-size:11px;color:#A8A29E;line-height:1.6;margin-top:2px;">
                        Update in Gateway Charges tab if rates change.
                    </p>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Gateway Charges Tab --}}
    @if($activeTab === 'gateways')
    <div style="display:grid;grid-template-columns:1fr 300px;gap:24px;align-items:start;" id="billing-gateways-grid">
        <div>
            <div style="background:#FEF3C7;border:1px solid #FDE68A;border-radius:6px;padding:12px 16px;margin-bottom:20px;font-size:13px;color:#92400E;">
                💡 <strong>Absorption formula:</strong> Charge to tenant = (desired amount + fixed fee) ÷ (1 − percentage). This ensures you receive the exact plan price after gateway fees.
            </div>

            @foreach($charges as $key => $charge)
            <div class="krd-card" style="padding:20px;margin-bottom:12px;">
                <div style="display:flex;align-items:center;gap:10px;margin-bottom:14px;flex-wrap:wrap;">
                    <div style="font-size:14px;font-weight:600;color:#1C1917;">{{ ucfirst($charge['gateway']) }}</div>
                    <span class="krd-badge krd-badge-stone" style="font-size:10px;">{{ ucfirst($charge['region']) }}</span>
                    <div style="margin-left:auto;display:flex;align-items:center;gap:8px;"
                        x-data="{
                            on: {{ $charge['absorb'] ? 'true' : 'false' }},
                            toggle() {
                                this.on = !this.on;
                                $wire.set('charges.{{ $key }}.absorb', this.on, false);
                            }
                        }">
                        <div x-on:click="toggle()"
                            :style="on ? 'width:36px;height:20px;border-radius:10px;background:#7C3AED;cursor:pointer;position:relative;' : 'width:36px;height:20px;border-radius:10px;background:#D6D3D1;cursor:pointer;position:relative;'">
                            <div :style="on ? 'position:absolute;top:2px;right:2px;width:16px;height:16px;border-radius:50%;background:#fff;transition:all 200ms;' : 'position:absolute;top:2px;left:2px;width:16px;height:16px;border-radius:50%;background:#fff;transition:all 200ms;'"></div>
                        </div>
                        <span style="font-size:12px;color:#57534E;">Absorb charges</span>
                    </div>
                </div>

                <div class="krd-grid-3" style="gap:12px;">
                    <div class="krd-input-group" style="margin-bottom:0;">
                        <label class="krd-label-text">Percentage (%)</label>
                        <input wire:model="charges.{{ $key }}.percentage" type="number" step="0.01" min="0" max="100"
                            class="krd-input" placeholder="1.5" />
                    </div>
                    <div class="krd-input-group" style="margin-bottom:0;">
                        <label class="krd-label-text">Fixed Fee (₦)</label>
                        <input wire:model="charges.{{ $key }}.fixed_fee" type="number" step="0.01" min="0"
                            class="krd-input" placeholder="100" />
                    </div>
                    <div class="krd-input-group" style="margin-bottom:0;">
                        <label class="krd-label-text">Cap (₦, blank = no cap)</label>
                        <input wire:model="charges.{{ $key }}.cap" type="number" step="0.01" min="0"
                            class="krd-input" placeholder="2000" />
                    </div>
                </div>
                <div class="krd-input-group" style="margin-top:12px;margin-bottom:0;">
                    <label class="krd-label-text">Description</label>
                    <input wire:model="charges.{{ $key }}.description" type="text" class="krd-input"
                        placeholder="e.g. Paystack Nigeria: 1.5% + ₦100" />
                </div>
            </div>
            @endforeach

            <button wire:click="saveGatewayCharges" wire:loading.attr="disabled" class="krd-btn krd-btn-primary">
                <span wire:loading.remove wire:target="saveGatewayCharges">Save Gateway Charges</span>
                <span wire:loading wire:target="saveGatewayCharges">Saving...</span>
            </button>
        </div>

        {{-- Right side tips --}}
        <div style="position:sticky;top:80px;">
            <div class="krd-card" style="padding:20px;">
                <div style="font-size:13px;font-weight:600;color:#1C1917;margin-bottom:12px;">💡 How absorption works</div>
                <div style="display:flex;flex-direction:column;gap:10px;">
                    @foreach([
                        'You set a plan price (e.g. ₦5,000) — that\'s what you want to receive.',
                        'The system calculates what to charge the tenant so that after the gateway deducts its fee, you get exactly ₦5,000.',
                        'Formula: Charge = (Desired + Fixed Fee) ÷ (1 − Percentage)',
                        'If a cap exists, the fee is limited to that cap amount.',
                        'Update rates here whenever Paystack or Flutterwave change their pricing.',
                    ] as $tip)
                    <div style="display:flex;gap:8px;align-items:flex-start;">
                        <div style="width:5px;height:5px;border-radius:50%;background:#7C3AED;flex-shrink:0;margin-top:6px;"></div>
                        <p style="font-size:12px;color:#78716C;line-height:1.6;">{{ $tip }}</p>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="krd-card" style="padding:20px;margin-top:12px;background:#F0FDF4;border-color:#86EFAC;">
                <div style="font-size:13px;font-weight:600;color:#166534;margin-bottom:8px;">✅ Example</div>
                <div style="font-size:12px;color:#166534;line-height:1.8;">
                    Desired: ₦5,000<br>
                    Paystack Nigeria: 1.5% + ₦100<br>
                    Charge tenant: <strong>₦5,178</strong><br>
                    Fee deducted: ₦178<br>
                    You receive: <strong>₦5,000</strong>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- API Keys Tab --}}
    @if($activeTab === 'keys')
    <div style="display:grid;grid-template-columns:1fr 300px;gap:24px;align-items:start;" id="billing-keys-grid">
        <div>
            <div class="krd-card" style="padding:24px;margin-bottom:16px;">
                <div class="krd-label" style="margin-bottom:16px;">Paystack</div>
                <div class="krd-input-group">
                    <label class="krd-label-text">Secret Key</label>
                    <input wire:model="paystack_secret_key" type="password" class="krd-input" placeholder="sk_live_..." />
                </div>
                <div class="krd-input-group" style="margin-bottom:0;">
                    <label class="krd-label-text">Public Key</label>
                    <input wire:model="paystack_public_key" type="text" class="krd-input" placeholder="pk_live_..." />
                </div>
            </div>

            <div class="krd-card" style="padding:24px;margin-bottom:16px;">
                <div class="krd-label" style="margin-bottom:16px;">Flutterwave</div>
                <div class="krd-input-group">
                    <label class="krd-label-text">Secret Key</label>
                    <input wire:model="flutterwave_secret_key" type="password" class="krd-input" placeholder="FLWSECK_TEST-..." />
                </div>
                <div class="krd-input-group" style="margin-bottom:0;">
                    <label class="krd-label-text">Public Key</label>
                    <input wire:model="flutterwave_public_key" type="text" class="krd-input" placeholder="FLWPUBK_TEST-..." />
                </div>
            </div>

            <div style="background:#FEF3C7;border:1px solid #FDE68A;border-radius:6px;padding:12px 16px;margin-bottom:16px;font-size:12px;color:#92400E;">
                ⚠️ API keys are stored in the database. Use test keys during development, live keys in production only.
            </div>

            <button wire:click="saveApiKeys" wire:loading.attr="disabled" class="krd-btn krd-btn-primary">
                <span wire:loading.remove wire:target="saveApiKeys">Save API Keys</span>
                <span wire:loading wire:target="saveApiKeys">Saving...</span>
            </button>
        </div>

        {{-- Right side --}}
        <div style="position:sticky;top:80px;">
            <div class="krd-card" style="padding:20px;">
                <div style="font-size:13px;font-weight:600;color:#1C1917;margin-bottom:12px;">🔑 Where to find your keys</div>
                <div style="display:flex;flex-direction:column;gap:12px;">
                    <div style="padding:12px;background:#F5F5F4;border-radius:6px;">
                        <div style="font-size:12px;font-weight:600;color:#1C1917;margin-bottom:4px;">Paystack</div>
                        <div style="font-size:11px;color:#78716C;line-height:1.6;">
                            Dashboard → Settings → API Keys & Webhooks<br>
                            <a href="https://dashboard.paystack.com/#/settings/developer" target="_blank"
                                style="color:#7C3AED;text-decoration:none;">dashboard.paystack.com →</a>
                        </div>
                    </div>
                    <div style="padding:12px;background:#F5F5F4;border-radius:6px;">
                        <div style="font-size:12px;font-weight:600;color:#1C1917;margin-bottom:4px;">Flutterwave</div>
                        <div style="font-size:11px;color:#78716C;line-height:1.6;">
                            Dashboard → Settings → API<br>
                            <a href="https://app.flutterwave.com/dashboard/settings/apis/live" target="_blank"
                                style="color:#7C3AED;text-decoration:none;">app.flutterwave.com →</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="krd-card" style="padding:20px;margin-top:12px;">
                <div style="font-size:13px;font-weight:600;color:#1C1917;margin-bottom:10px;">🔗 Webhook URLs</div>
                <div style="font-size:11px;color:#78716C;margin-bottom:8px;">Add these to your gateway dashboards:</div>
                <div style="background:#F5F5F4;border-radius:6px;padding:10px;font-size:11px;font-family:monospace;color:#57534E;word-break:break-all;margin-bottom:6px;">
                    {{ url('/api/webhooks/paystack') }}
                </div>
                <div style="background:#F5F5F4;border-radius:6px;padding:10px;font-size:11px;font-family:monospace;color:#57534E;word-break:break-all;">
                    {{ url('/api/webhooks/flutterwave') }}
                </div>
            </div>
        </div>
    </div>
    @endif

</div>

<style>
@media (max-width: 768px) {
    #billing-settings-grid  { grid-template-columns: 1fr !important; }
    #billing-gateways-grid  { grid-template-columns: 1fr !important; }
    #billing-keys-grid      { grid-template-columns: 1fr !important; }
    #billing-settings-right { position: static !important; }
    #billing-stats-grid     { grid-template-columns: repeat(2,1fr) !important; }
}
</style>