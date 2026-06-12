<div>

    {{-- Header --}}
    <div style="margin-bottom:28px;">
        <div style="margin-bottom:8px;">
            <a href="{{ route('platform.plans') }}" wire:navigate style="color:#A8A29E;text-decoration:none;font-size:13px;">
                ← Back to Plans
            </a>
        </div>
        <div class="krd-label" style="margin-bottom:4px;">Platform Management</div>
        <h2 class="krd-heading-3" style="color:#1C1917;">
            {{ $planId ? 'Edit Plan' : 'Create Plan' }}
        </h2>
    </div>

    <div style="display:grid;grid-template-columns:1fr 340px;gap:24px;align-items:start;">

        {{-- Left — Form --}}
        <div style="display:flex;flex-direction:column;gap:16px;">

            {{-- Basic Info --}}
            <div class="krd-card" style="padding:24px;">
                <div class="krd-label" style="margin-bottom:16px;">Plan Details</div>

                <div class="krd-input-group">
                    <label class="krd-label-text">Plan Name</label>
                    <input wire:model.live="name" type="text" class="krd-input @error('name') krd-input-error @enderror" placeholder="e.g. Pro" />
                    @error('name') <span class="krd-input-error-msg">{{ $message }}</span> @enderror
                </div>

                <div class="krd-input-group">
                    <label class="krd-label-text">Slug</label>
                    <input wire:model="slug" type="text" class="krd-input @error('slug') krd-input-error @enderror" placeholder="e.g. pro" style="font-family:monospace;" />
                    @error('slug') <span class="krd-input-error-msg">{{ $message }}</span> @enderror
                    <span class="krd-input-hint">Auto-generated from name. Used in URLs and API.</span>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div class="krd-input-group" style="margin-bottom:0;">
                        <label class="krd-label-text">Billing Cycle</label>
                        <select wire:model="billing_cycle" class="krd-input" style="cursor:pointer;">
                            <option value="monthly">Monthly</option>
                            <option value="annual">Annual</option>
                            <option value="lifetime">Lifetime</option>
                            <option value="trial">Trial Only</option>
                        </select>
                    </div>
                    <div class="krd-input-group" style="margin-bottom:0;">
                        <label class="krd-label-text">Trial Days</label>
                        <input wire:model="trial_days" type="number" min="0" class="krd-input" placeholder="0" />
                    </div>
                </div>
            </div>

            {{-- Active toggle --}}
            <div class="krd-card" style="padding:16px 20px;">
                <div style="display:flex;align-items:center;justify-content:space-between;">
                    <div>
                        <div style="font-size:13px;font-weight:500;color:#1C1917;">Plan Active</div>
                        <div style="font-size:12px;color:#A8A29E;margin-top:2px;">Active plans are visible to users during registration.</div>
                    </div>
                    <input wire:model="is_active" type="checkbox" style="width:16px;height:16px;accent-color:#7C3AED;cursor:pointer;" />
                </div>
            </div>

            {{-- Limits --}}
            <div class="krd-card" style="padding:24px;">
                <div class="krd-label" style="margin-bottom:16px;">Usage Limits</div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div class="krd-input-group">
                        <label class="krd-label-text">Max Events</label>
                        <input wire:model="max_events" type="number" min="0" class="krd-input" placeholder="Unlimited" />
                    </div>
                    <div class="krd-input-group">
                        <label class="krd-label-text">Max Staff</label>
                        <input wire:model="max_staff" type="number" min="0" class="krd-input" placeholder="Unlimited" />
                    </div>
                    <div class="krd-input-group">
                        <label class="krd-label-text">Max Storage (MB)</label>
                        <input wire:model="max_storage_mb" type="number" min="0" class="krd-input" placeholder="Unlimited" />
                    </div>
                    <div class="krd-input-group" style="margin-bottom:0;">
                        <label class="krd-label-text">Max Guests / Event</label>
                        <input wire:model="max_guests" type="number" min="0" class="krd-input" placeholder="Unlimited" />
                    </div>
                </div>
                <div style="margin-top:10px;">
                    <span class="krd-input-hint">Leave blank for unlimited. Enter -1 explicitly for unlimited in API.</span>
                </div>
            </div>

            {{-- Features --}}
            <div class="krd-card" style="padding:24px;">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
                    <div class="krd-label">Feature Access</div>
                    <button
                        wire:click="openAddFeature"
                        type="button"
                        class="krd-btn krd-btn-secondary krd-btn-sm"
                    >
                        + Add Feature
                    </button>
                </div>

                <div style="display:flex;flex-direction:column;gap:0;">
                    @foreach($featureFlags as $flag)
                    @php $currentValue = $features[$flag->key] ?? 'false'; @endphp
                    <div style="display:flex;align-items:center;justify-content:space-between;padding:11px 0;border-bottom:1px solid #E7E5E4;">
                        <div style="flex:1;min-width:0;">
                            <div style="font-size:13px;font-weight:500;color:#1C1917;">{{ $flag->label }}</div>
                            @if($flag->description)
                            <div style="font-size:11px;color:#A8A29E;margin-top:1px;">{{ $flag->description }}</div>
                            @endif
                        </div>

                        {{-- Custom toggle buttons instead of native select --}}
                        {{-- Custom toggle buttons — instant UI, isolated per feature --}}
                        <div
                            style="display:flex;gap:4px;flex-shrink:0;margin-left:16px;"
                            x-data="featureToggle('{{ $flag->key }}', '{{ $currentValue }}')"
                        >
                            <button type="button" x-on:click="set('false')"
                                :style="val === 'false'
                                    ? 'padding:4px 10px;font-size:11px;font-weight:500;border-radius:4px;cursor:pointer;border:1px solid #EF4444;background:#FEE2E2;color:#DC2626;'
                                    : 'padding:4px 10px;font-size:11px;font-weight:500;border-radius:4px;cursor:pointer;border:1px solid #E7E5E4;background:#FFFFFF;color:#A8A29E;'"
                            >Off</button>
                            <button type="button" x-on:click="set('true')"
                                :style="val === 'true'
                                    ? 'padding:4px 10px;font-size:11px;font-weight:500;border-radius:4px;cursor:pointer;border:1px solid #10B981;background:#D1FAE5;color:#059669;'
                                    : 'padding:4px 10px;font-size:11px;font-weight:500;border-radius:4px;cursor:pointer;border:1px solid #E7E5E4;background:#FFFFFF;color:#A8A29E;'"
                            >On</button>
                            <button type="button" x-on:click="set('unlimited')"
                                :style="val === 'unlimited'
                                    ? 'padding:4px 10px;font-size:11px;font-weight:500;border-radius:4px;cursor:pointer;border:1px solid #7C3AED;background:#EDE9FE;color:#7C3AED;'
                                    : 'padding:4px 10px;font-size:11px;font-weight:500;border-radius:4px;cursor:pointer;border:1px solid #E7E5E4;background:#FFFFFF;color:#A8A29E;'"
                            >∞</button>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Submit --}}
            <div style="display:flex;gap:10px;">
                <button wire:click="save" wire:loading.attr="disabled" class="krd-btn krd-btn-primary krd-btn-lg">
                    <span wire:loading.remove wire:target="save">{{ $planId ? 'Update Plan' : 'Create Plan' }}</span>
                    <span wire:loading wire:target="save">Saving...</span>
                </button>
                <a href="{{ route('platform.plans') }}" wire:navigate class="krd-btn krd-btn-ghost">Cancel</a>
            </div>

        </div>

        {{-- Right — Instructions --}}
        <div style="display:flex;flex-direction:column;gap:12px;position:sticky;top:80px;">

            <div class="krd-card" style="padding:20px;">
                <div style="font-size:13px;font-weight:600;color:#1C1917;margin-bottom:12px;display:flex;align-items:center;gap:8px;">
                    <span>💡</span> How plans work
                </div>
                <div style="display:flex;flex-direction:column;gap:10px;">
                    @foreach([
                        'Plans define what features and limits each tenant gets access to.',
                        'Feature Access controls which modules are visible and usable.',
                        'Usage Limits cap how many events, staff, guests etc. a tenant can have. Leave blank for unlimited.',
                        'Trial Days sets how long a free trial lasts when this plan is selected at registration.',
                        'Inactive plans are hidden from users but existing subscribers keep access.',
                    ] as $tip)
                    <div style="display:flex;gap:8px;align-items:flex-start;">
                        <div style="width:5px;height:5px;border-radius:50%;background:#7C3AED;flex-shrink:0;margin-top:6px;"></div>
                        <p style="font-size:12px;color:#78716C;line-height:1.6;">{{ $tip }}</p>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="krd-card" style="padding:20px;background:#F5F3FF;border-color:#DDD6FE;">
                <div style="font-size:13px;font-weight:600;color:#7C3AED;margin-bottom:8px;">
                    Feature Toggle Guide
                </div>
                <div style="display:flex;flex-direction:column;gap:8px;">
                    <div style="display:flex;align-items:center;gap:8px;">
                        <span style="background:#FEE2E2;color:#DC2626;font-size:10px;font-weight:600;padding:2px 8px;border-radius:4px;">Off</span>
                        <span style="font-size:12px;color:#78716C;">Feature disabled, hidden from tenant</span>
                    </div>
                    <div style="display:flex;align-items:center;gap:8px;">
                        <span style="background:#D1FAE5;color:#059669;font-size:10px;font-weight:600;padding:2px 8px;border-radius:4px;">On</span>
                        <span style="font-size:12px;color:#78716C;">Feature enabled with plan limits</span>
                    </div>
                    <div style="display:flex;align-items:center;gap:8px;">
                        <span style="background:#EDE9FE;color:#7C3AED;font-size:10px;font-weight:600;padding:2px 8px;border-radius:4px;">∞</span>
                        <span style="font-size:12px;color:#78716C;">Unlimited access, no restrictions</span>
                    </div>
                </div>
            </div>

        </div>

    </div>

    {{-- Add Feature Modal --}}
    @if($showAddFeature)
    <div style="position:fixed;inset:0;background:rgba(0,0,0,0.4);z-index:60;display:flex;align-items:center;justify-content:center;">
        <div style="background:#fff;border-radius:8px;padding:28px;max-width:440px;width:90%;">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
                <h3 style="font-size:16px;font-weight:600;color:#1C1917;">Add New Feature</h3>
                <button wire:click="closeAddFeature" style="background:none;border:none;cursor:pointer;color:#A8A29E;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                    </svg>
                </button>
            </div>

            <div class="krd-input-group">
                <label class="krd-label-text">Feature Key</label>
                <input
                    wire:model="new_feature_key"
                    type="text"
                    class="krd-input @error('new_feature_key') krd-input-error @enderror"
                    placeholder="e.g. analytics_dashboard"
                    style="font-family:monospace;"
                />
                @error('new_feature_key') <span class="krd-input-error-msg">{{ $message }}</span> @enderror
                <span class="krd-input-hint">Unique identifier. Use underscores, no spaces.</span>
            </div>

            <div class="krd-input-group">
                <label class="krd-label-text">Feature Label</label>
                <input
                    wire:model="new_feature_label"
                    type="text"
                    class="krd-input @error('new_feature_label') krd-input-error @enderror"
                    placeholder="e.g. Analytics Dashboard"
                />
                @error('new_feature_label') <span class="krd-input-error-msg">{{ $message }}</span> @enderror
            </div>

            <div class="krd-input-group" style="margin-bottom:20px;">
                <label class="krd-label-text">Description <span style="color:#A8A29E;">(optional)</span></label>
                <input
                    wire:model="new_feature_desc"
                    type="text"
                    class="krd-input"
                    placeholder="Brief description of what this feature does"
                />
            </div>

            <div style="display:flex;gap:10px;">
                <button wire:click="saveFeatureFlag" wire:loading.attr="disabled" class="krd-btn krd-btn-primary" style="flex:1;">
                    <span wire:loading.remove wire:target="saveFeatureFlag">Save Feature</span>
                    <span wire:loading wire:target="saveFeatureFlag">Saving...</span>
                </button>
                <button wire:click="closeAddFeature" class="krd-btn krd-btn-ghost">Cancel</button>
            </div>
        </div>
    </div>
    @endif

</div>