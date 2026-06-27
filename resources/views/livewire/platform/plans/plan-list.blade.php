<div>

    {{-- Header --}}
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px;">
        <div>
            <div class="krd-label" style="margin-bottom:4px;">Platform Management</div>
            <h2 class="krd-heading-3" style="color:#1C1917;">Plans</h2>
            <p style="font-size:12px;color:#A8A29E;margin-top:4px;">
                Mark one plan as Recommended to highlight it during registration.
            </p>
        </div>
        <a href="{{ route('platform.plans.create') }}" class="krd-btn krd-btn-primary" wire:navigate>
            + Create Plan
        </a>
    </div>

    @if($plans->isEmpty())
    <div class="krd-card">
        <div class="krd-empty-state">
            <div class="krd-empty-state-icon">📦</div>
            <div class="krd-empty-state-title">No plans yet</div>
            <div class="krd-empty-state-desc">Create your first plan to get started.</div>
        </div>
    </div>
    @else

    {{-- Plans Grid --}}
    <div class="krd-grid-3" style="gap:16px;">
        @foreach($plans as $plan)
        <div class="krd-card" style="position:relative;padding:20px;{{ $plan->is_featured ? 'border-color:#7C3AED;border-width:2px;' : '' }}">

            {{-- Featured ribbon --}}
            @if($plan->is_featured)
            <div style="position:absolute;top:-1px;left:20px;background:#7C3AED;color:#fff;font-size:10px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;padding:3px 10px;border-radius:0 0 6px 6px;">
                ⭐ Recommended
            </div>
            @endif

            {{-- Top row --}}
            <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:10px;margin-top:{{ $plan->is_featured ? '16px' : '0' }};">
                <div style="flex:1;min-width:0;">
                    <div style="font-size:18px;font-weight:700;color:#1C1917;margin-bottom:3px;">
                        {{ $plan->name }}
                    </div>
                    <div style="font-size:12px;color:#A8A29E;">
                        {{ ucfirst($plan->billing_cycle) }}
                        @if($plan->trial_days > 0)
                        · {{ $plan->trial_days }}-day trial
                        @endif
                    </div>
                </div>
                @if($plan->is_active)
                <span class="krd-badge krd-badge-green">Active</span>
                @else
                <span class="krd-badge krd-badge-stone">Inactive</span>
                @endif
            </div>

            <div class="krd-divider" style="margin:14px 0;"></div>

            {{-- Limits --}}
            @if(!empty($plan->limits))
            <div style="margin-bottom:14px;">
                <div class="krd-label" style="margin-bottom:8px;">Limits</div>
                @foreach($plan->limits as $key => $value)
                <div style="display:flex;justify-content:space-between;padding:4px 0;font-size:12px;border-bottom:1px solid #F5F5F4;">
                    <span style="color:#78716C;">{{ str_replace('_', ' ', ucwords($key, '_')) }}</span>
                    <span style="font-weight:600;color:#1C1917;">
                        {{ $value == -1 ? '∞ Unlimited' : $value }}
                    </span>
                </div>
                @endforeach
            </div>
            @endif

            {{-- Features --}}
            @if(!empty($plan->features))
            <div style="margin-bottom:16px;">
                <div class="krd-label" style="margin-bottom:8px;">Features</div>
                <div style="display:flex;flex-wrap:wrap;gap:5px;">
                    @foreach($plan->features as $key => $value)
                    @if($value === 'true' || $value === 'unlimited' || (is_numeric($value) && $value > 0))
                    <span style="font-size:10px;background:#F0FDF4;color:#166534;border:1px solid #86EFAC;padding:2px 7px;border-radius:4px;">
                        ✓ {{ str_replace('_', ' ', ucwords($key, '_')) }}
                    </span>
                    @endif
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Actions --}}
            <div style="display:flex;flex-direction:column;gap:8px;">
                <div style="display:flex;gap:8px;">
                    <a href="{{ route('platform.plans.edit', $plan->id) }}"
                        class="krd-btn krd-btn-secondary krd-btn-sm"
                        wire:navigate
                        style="flex:1;justify-content:center;">
                        ✏️ Edit
                    </a>
                    <button wire:click="toggleActive({{ $plan->id }})"
                        class="krd-btn krd-btn-sm"
                        style="flex:1;background:{{ $plan->is_active ? '#FEE2E2' : '#D1FAE5' }};color:{{ $plan->is_active ? '#DC2626' : '#059669' }};border-color:{{ $plan->is_active ? '#FECACA' : '#6EE7B7' }};">
                        {{ $plan->is_active ? 'Deactivate' : 'Activate' }}
                    </button>
                </div>
                <div style="display:flex;gap:8px;">
                    <button wire:click="setFeatured({{ $plan->id }})"
                        class="krd-btn krd-btn-sm"
                        style="flex:1;background:{{ $plan->is_featured ? '#EDE9FE' : '#F5F5F4' }};color:{{ $plan->is_featured ? '#7C3AED' : '#78716C' }};border-color:{{ $plan->is_featured ? '#DDD6FE' : '#E7E5E4' }};">
                        {{ $plan->is_featured ? '⭐ Unmark Recommended' : '☆ Mark Recommended' }}
                    </button>
                    <button wire:click="confirmDelete({{ $plan->id }}, '{{ $plan->name }}')"
                        class="krd-btn krd-btn-sm"
                        style="background:#FEE2E2;color:#DC2626;border-color:#FECACA;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/>
                            <path d="M10 11v6M14 11v6M9 6V4h6v2"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    {{-- Delete Confirmation Modal --}}
    @if($showDeleteModal)
    <div style="position:fixed;inset:0;background:rgba(0,0,0,0.4);z-index:60;display:flex;align-items:center;justify-content:center;padding:16px;">
        <div style="background:#fff;border-radius:8px;padding:28px;max-width:420px;width:100%;">
            <div style="width:44px;height:44px;border-radius:50%;background:#FEE2E2;display:flex;align-items:center;justify-content:center;margin-bottom:16px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="#DC2626" stroke-width="2">
                    <polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/>
                    <path d="M10 11v6M14 11v6M9 6V4h6v2"/>
                </svg>
            </div>
            <h3 style="font-size:16px;font-weight:600;color:#1C1917;margin-bottom:8px;">
                Delete "{{ $deleteName }}"?
            </h3>
            <p style="font-size:13px;color:#78716C;margin-bottom:6px;line-height:1.6;">
                This will permanently delete this plan. This action cannot be undone.
            </p>
            <p style="font-size:12px;color:#F59E0B;margin-bottom:24px;line-height:1.6;background:#FFFBEB;padding:10px 12px;border-radius:6px;border:1px solid #FDE68A;">
                ⚠️ Plans with active subscribers cannot be deleted.
            </p>
            <div style="display:flex;gap:10px;">
                <button wire:click="deletePlan" wire:loading.attr="disabled"
                    class="krd-btn krd-btn-danger" style="flex:1;">
                    <span wire:loading.remove wire:target="deletePlan">Yes, Delete Plan</span>
                    <span wire:loading wire:target="deletePlan">Deleting...</span>
                </button>
                <button wire:click="cancelDelete" class="krd-btn krd-btn-secondary" style="flex:1;">
                    Cancel
                </button>
            </div>
        </div>
    </div>
    @endif

</div>