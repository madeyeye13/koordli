<div>

    {{-- Header --}}
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px;">
        <div>
            <div class="krd-label" style="margin-bottom:4px;">Platform Management</div>
            <h2 class="krd-heading-3" style="color:#1C1917;">Plans</h2>
        </div>
        <a href="{{ route('platform.plans.create') }}" class="krd-btn krd-btn-primary" wire:navigate>
            + Create Plan
        </a>
    </div>

    {{-- Plans Grid --}}
    <div class="krd-grid-3">
        @foreach($plans as $plan)
        <div class="krd-card" style="position:relative;">

            {{-- Status badge --}}
            <div style="position:absolute;top:16px;right:16px;">
                @if($plan->is_active)
                    <span class="krd-badge krd-badge-green">Active</span>
                @else
                    <span class="krd-badge krd-badge-stone">Inactive</span>
                @endif
            </div>

            {{-- Plan name --}}
            <div style="font-size:18px;font-weight:700;color:#1C1917;margin-bottom:4px;padding-right:70px;">
                {{ $plan->name }}
            </div>
            <div style="font-size:12px;color:#A8A29E;margin-bottom:16px;">
                {{ ucfirst($plan->billing_cycle) }}
                @if($plan->trial_days > 0)
                    · {{ $plan->trial_days }} day trial
                @endif
            </div>

            <div class="krd-divider"></div>

            {{-- Limits --}}
            <div style="margin-bottom:16px;">
                <div class="krd-label" style="margin-bottom:8px;">Limits</div>
                @foreach($plan->limits ?? [] as $key => $value)
                <div style="display:flex;justify-content:space-between;padding:5px 0;font-size:12px;">
                    <span style="color:#78716C;">{{ str_replace('_', ' ', ucfirst($key)) }}</span>
                    <span style="font-weight:500;color:#1C1917;">
                        {{ $value == -1 ? 'Unlimited' : $value }}
                    </span>
                </div>
                @endforeach
            </div>

            {{-- Features --}}
            <div style="margin-bottom:16px;">
                <div class="krd-label" style="margin-bottom:8px;">Features</div>
                <div style="display:flex;flex-wrap:wrap;gap:5px;">
                    @foreach($plan->features ?? [] as $key => $value)
                        @if($value === 'true' || $value === 'unlimited' || (is_numeric($value) && $value > 0))
                        <span style="font-size:10px;background:#F5F5F4;color:#57534E;padding:2px 7px;border-radius:4px;">
                            {{ str_replace('_', ' ', ucfirst($key)) }}
                        </span>
                        @endif
                    @endforeach
                </div>
            </div>

            {{-- Actions --}}
            <div style="display:flex;gap:8px;">
                <a href="{{ route('platform.plans.create') }}"
                   class="krd-btn krd-btn-secondary krd-btn-sm"
                   wire:navigate
                   style="flex:1;justify-content:center;">
                    Edit
                </a>
                <button
                    wire:click="toggleActive({{ $plan->id }})"
                    class="krd-btn krd-btn-sm"
                    style="background:{{ $plan->is_active ? '#FEE2E2' : '#D1FAE5' }};color:{{ $plan->is_active ? '#DC2626' : '#059669' }};border-color:{{ $plan->is_active ? '#FECACA' : '#6EE7B7' }};"
                >
                    {{ $plan->is_active ? 'Deactivate' : 'Activate' }}
                </button>
            </div>

        </div>
        @endforeach
    </div>

</div>