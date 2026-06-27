<div>
    {{-- Header --}}
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px;">
        <div>
            <div class="krd-label" style="margin-bottom:4px;">Platform Management</div>
            <h2 class="krd-heading-3" style="color:#1C1917;">Companies</h2>
        </div>
        <a href="{{ route('platform.tenants.create') }}" class="krd-btn krd-btn-primary" wire:navigate>
            + Add Company
        </a>
    </div>

    {{-- Filters --}}
    <div style="display:flex;gap:10px;margin-bottom:16px;flex-wrap:wrap;align-items:flex-start;">
        <input wire:model.live.debounce.300ms="search" type="text"
            class="krd-input" placeholder="Search companies..."
            style="max-width:280px;" />

        <div x-data="{
                open: false,
                label: '{{ $status ? ucfirst($status) : 'All statuses' }}',
                pick(val, label) { this.label = label; this.open = false; $wire.set('status', val); }
            }"
            x-on:click.outside="open = false"
            style="position:relative;min-width:160px;">
            <button type="button"
                x-on:click="open = !open"
                x-bind:class="open ? 'krd-dropdown-trigger open' : 'krd-dropdown-trigger'"
                style="width:100%;">
                <span x-text="label"></span>
                <svg class="krd-dropdown-chevron" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
            </button>
            <div x-show="open" x-cloak class="krd-dropdown-menu">
                <div class="krd-dropdown-option {{ !$status ? 'selected' : '' }}"
                    x-on:click="pick('', 'All statuses')">All statuses</div>
                @foreach(['trial' => 'Trial', 'active' => 'Active', 'suspended' => 'Suspended', 'cancelled' => 'Cancelled'] as $val => $label)
                <div class="krd-dropdown-option {{ $status === $val ? 'selected' : '' }}"
                    x-on:click="pick('{{ $val }}', '{{ $label }}')">{{ $label }}</div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Desktop Table --}}
    <div class="krd-card" style="padding:0;overflow:clip;" id="tenants-desktop">
        <div class="krd-table-wrap">
            <table class="krd-table">
                <thead>
                    <tr>
                        <th>Company</th>
                        <th>Status</th>
                        <th class="krd-col-hide-mobile">Plan</th>
                        <th class="krd-col-hide-mobile">Currency</th>
                        <th class="krd-col-hide-mobile">Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tenants as $tenant)
                    <tr>
                        <td>
                            <div style="font-weight:500;color:#1C1917;">{{ $tenant->name }}</div>
                            <div style="font-size:11px;color:#A8A29E;font-family:monospace;">{{ $tenant->slug }}</div>
                        </td>
                        <td>
                            @if($tenant->status === 'active')
                                <span class="krd-badge krd-badge-green">Active</span>
                            @elseif($tenant->status === 'trial')
                                <span class="krd-badge krd-badge-amber">Trial</span>
                            @elseif($tenant->status === 'suspended')
                                <span class="krd-badge krd-badge-red">Suspended</span>
                            @else
                                <span class="krd-badge krd-badge-stone">{{ ucfirst($tenant->status) }}</span>
                            @endif
                        </td>
                        <td class="krd-col-hide-mobile" style="font-size:12px;color:#78716C;">
                            {{ $tenant->plan?->name ?? '—' }}
                        </td>
                        <td class="krd-col-hide-mobile" style="font-size:12px;color:#78716C;">
                            {{ $tenant->billing_currency }}
                        </td>
                        <td class="krd-col-hide-mobile" style="font-size:12px;color:#78716C;">
                            {{ $tenant->created_at->format('M d, Y') }}
                        </td>
                        <td>
                            <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;">
                                <button wire:click="viewTenant({{ $tenant->id }})"
                                    class="krd-btn krd-btn-secondary krd-btn-sm">
                                    View
                                </button>
                                <a href="{{ route('platform.tenants.edit', $tenant->id) }}"
                                    wire:navigate
                                    class="krd-btn krd-btn-secondary krd-btn-sm">
                                    Edit
                                </a>
                                @if($tenant->status === 'suspended')
                                <button wire:click="activate({{ $tenant->id }})"
                                    class="krd-btn krd-btn-sm"
                                    style="background:#D1FAE5;color:#059669;border-color:#6EE7B7;">
                                    Activate
                                </button>
                                @else
                                <button wire:click="confirmSuspend({{ $tenant->id }})"
                                    class="krd-btn krd-btn-sm"
                                    style="background:#FEE2E2;color:#DC2626;border-color:#FECACA;">
                                    Suspend
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6">
                            <div class="krd-empty-state">
                                <div class="krd-empty-state-icon">🏢</div>
                                <div class="krd-empty-state-title">No companies found</div>
                                <div class="krd-empty-state-desc">
                                    {{ $search ? 'Try a different search term.' : 'Create your first company to get started.' }}
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($tenants->hasPages())
        <div style="padding:12px 16px;border-top:1px solid #E7E5E4;">
            {{ $tenants->links() }}
        </div>
        @endif
    </div>

    {{-- Mobile Cards --}}
    <div id="tenants-mobile" style="display:flex;flex-direction:column;gap:10px;">
        @forelse($tenants as $tenant)
        <div class="krd-card" style="padding:16px;">
            <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:10px;margin-bottom:10px;">
                <div>
                    <div style="font-size:14px;font-weight:600;color:#1C1917;">{{ $tenant->name }}</div>
                    <div style="font-size:11px;color:#A8A29E;font-family:monospace;margin-top:2px;">{{ $tenant->slug }}</div>
                </div>
                @if($tenant->status === 'active')
                    <span class="krd-badge krd-badge-green">Active</span>
                @elseif($tenant->status === 'trial')
                    <span class="krd-badge krd-badge-amber">Trial</span>
                @elseif($tenant->status === 'suspended')
                    <span class="krd-badge krd-badge-red">Suspended</span>
                @else
                    <span class="krd-badge krd-badge-stone">{{ ucfirst($tenant->status) }}</span>
                @endif
            </div>
            <div style="font-size:12px;color:#78716C;margin-bottom:12px;">
                {{ $tenant->plan?->name ?? 'No plan' }} · {{ $tenant->billing_currency }} · {{ $tenant->created_at->format('M d, Y') }}
            </div>
            <div style="display:flex;gap:8px;flex-wrap:wrap;">
                <button wire:click="viewTenant({{ $tenant->id }})"
                    class="krd-btn krd-btn-secondary krd-btn-sm">View</button>
                <a href="{{ route('platform.tenants.edit', $tenant->id) }}"
                    wire:navigate class="krd-btn krd-btn-secondary krd-btn-sm">Edit</a>
                @if($tenant->status === 'suspended')
                <button wire:click="activate({{ $tenant->id }})"
                    class="krd-btn krd-btn-sm"
                    style="background:#D1FAE5;color:#059669;border-color:#6EE7B7;">
                    Activate
                </button>
                @else
                <button wire:click="confirmSuspend({{ $tenant->id }})"
                    class="krd-btn krd-btn-sm"
                    style="background:#FEE2E2;color:#DC2626;border-color:#FECACA;">
                    Suspend
                </button>
                @endif
            </div>
        </div>
        @empty
        <div class="krd-card">
            <div class="krd-empty-state">
                <div class="krd-empty-state-icon">🏢</div>
                <div class="krd-empty-state-title">No companies found</div>
                <div class="krd-empty-state-desc">
                    {{ $search ? 'Try a different search term.' : 'Create your first company to get started.' }}
                </div>
            </div>
        </div>
        @endforelse
    </div>

    {{-- Tenant Detail Panel --}}
    @if($viewing && $viewingTenant)
    <div style="position:fixed;top:0;right:0;width:420px;max-width:100vw;height:100vh;background:#fff;border-left:1px solid #E7E5E4;z-index:50;overflow-y:auto;padding:24px;">

        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;">
            <h3 style="font-size:16px;font-weight:600;color:#1C1917;">Company Details</h3>
            <button wire:click="closeTenant"
                style="background:none;border:none;cursor:pointer;color:#A8A29E;padding:4px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>

        <div style="margin-bottom:20px;">
            <div style="font-size:20px;font-weight:700;color:#1C1917;margin-bottom:4px;">
                {{ $viewingTenant->name }}
            </div>
            <div style="font-size:12px;color:#A8A29E;font-family:monospace;">
                {{ $viewingTenant->slug }}
            </div>
        </div>

        <div class="krd-divider"></div>

        @php
            $owner = \App\Models\Tenant\User::withoutGlobalScope('tenant')
                ->where('tenant_id', $viewingTenant->id)
                ->orderBy('id')->first();
        @endphp

        @foreach([
            ['label' => 'Status',         'value' => ucfirst($viewingTenant->status)],
            ['label' => 'Plan',           'value' => $viewingTenant->plan?->name ?? '—'],
            ['label' => 'Currency',       'value' => $viewingTenant->billing_currency],
            ['label' => 'Country',        'value' => $viewingTenant->country ?? '—'],
            ['label' => 'Owner',          'value' => $owner?->name ?? '—'],
            ['label' => 'Owner Email',    'value' => $owner?->email ?? '—'],
            ['label' => 'Created',        'value' => $viewingTenant->created_at->format('M d, Y')],
        ] as $detail)
        <div style="display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid #E7E5E4;gap:12px;">
            <span style="font-size:12px;color:#78716C;flex-shrink:0;">{{ $detail['label'] }}</span>
            <span style="font-size:12px;font-weight:500;color:#1C1917;text-align:right;">{{ $detail['value'] }}</span>
        </div>
        @endforeach

        <div class="krd-divider"></div>

        <div style="display:flex;flex-direction:column;gap:8px;">
            <a href="{{ route('platform.tenants.edit', $viewingTenant->id) }}"
                wire:navigate class="krd-btn krd-btn-secondary">
                ✏️ Edit Company
            </a>
            @if($viewingTenant->status === 'suspended')
            <button wire:click="activate({{ $viewingTenant->id }})"
                class="krd-btn"
                style="background:#D1FAE5;color:#059669;border-color:#6EE7B7;">
                Activate Company
            </button>
            @else
            <button wire:click="confirmSuspend({{ $viewingTenant->id }})"
                class="krd-btn krd-btn-danger">
                Suspend Company
            </button>
            @endif
        </div>
    </div>

    <div wire:click="closeTenant"
        style="position:fixed;inset:0;background:rgba(0,0,0,0.3);z-index:49;"></div>
    @endif

    {{-- Suspend Modal --}}
    @if($showSuspendModal)
    <div style="position:fixed;inset:0;background:rgba(0,0,0,0.4);z-index:60;display:flex;align-items:center;justify-content:center;padding:16px;">
        <div style="background:#fff;border-radius:8px;padding:28px;max-width:400px;width:100%;">
            <h3 style="font-size:16px;font-weight:600;color:#1C1917;margin-bottom:8px;">Suspend Company?</h3>
            <p style="font-size:13px;color:#78716C;margin-bottom:24px;line-height:1.6;">
                This will prevent all users of this company from accessing Koordli.
                You can reactivate them at any time.
            </p>
            <div style="display:flex;gap:10px;">
                <button wire:click="suspend" class="krd-btn krd-btn-danger" style="flex:1;">Yes, Suspend</button>
                <button wire:click="cancelSuspend" class="krd-btn krd-btn-secondary" style="flex:1;">Cancel</button>
            </div>
        </div>
    </div>
    @endif

</div>

<style>
@media (min-width: 768px) {
    #tenants-desktop { display: block !important; }
    #tenants-mobile  { display: none !important; }
}
@media (max-width: 767px) {
    #tenants-desktop { display: none !important; }
    #tenants-mobile  { display: flex !important; }
}
</style>