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
    <div style="display:flex;gap:10px;margin-bottom:16px;flex-wrap:wrap;">
        <input
            wire:model.live.debounce.300ms="search"
            type="text"
            class="krd-input"
            placeholder="Search companies..."
            style="max-width:280px;"
        />
        <select wire:model.live="status" class="krd-input" style="max-width:160px;cursor:pointer;">
            <option value="">All statuses</option>
            <option value="trial">Trial</option>
            <option value="active">Active</option>
            <option value="suspended">Suspended</option>
            <option value="cancelled">Cancelled</option>
        </select>
    </div>

    {{-- Table --}}
    <div class="krd-card" style="padding:0;overflow:hidden;">
        <div class="krd-table-wrap">
            <table class="krd-table">
                <thead>
                    <tr>
                        <th>Company</th>
                        <th>Status</th>
                        <th>Plan</th>
                        <th>Currency</th>
                        <th>Created</th>
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
                                <span class="krd-badge krd-badge-stone">{{ $tenant->status }}</span>
                            @endif
                        </td>
                        <td style="font-size:12px;color:#78716C;">
                            {{ $tenant->plan?->name ?? '—' }}
                        </td>
                        <td style="font-size:12px;color:#78716C;">{{ $tenant->billing_currency }}</td>
                        <td style="font-size:12px;color:#78716C;white-space:nowrap;">
                            {{ $tenant->created_at->format('M d, Y') }}
                        </td>
                        <td>
                            <div style="display:flex;align-items:center;gap:6px;">
                                <button
                                    wire:click="viewTenant({{ $tenant->id }})"
                                    class="krd-btn krd-btn-secondary krd-btn-sm"
                                >
                                    View
                                </button>
                                @if($tenant->status === 'suspended')
                                <button
                                    wire:click="activate({{ $tenant->id }})"
                                    class="krd-btn krd-btn-sm"
                                    style="background:#D1FAE5;color:#059669;border-color:#6EE7B7;"
                                >
                                    Activate
                                </button>
                                @else
                                <button
                                    wire:click="confirmSuspend({{ $tenant->id }})"
                                    class="krd-btn krd-btn-sm"
                                    style="background:#FEE2E2;color:#DC2626;border-color:#FECACA;"
                                >
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

        {{-- Pagination --}}
        @if($tenants->hasPages())
        <div style="padding:12px 16px;border-top:1px solid #E7E5E4;">
            {{ $tenants->links() }}
        </div>
        @endif
    </div>

    {{-- Tenant Detail Panel --}}
    @if($viewing && $viewingTenant)
    <div style="position:fixed;top:0;right:0;width:420px;height:100vh;background:#fff;border-left:1px solid #E7E5E4;z-index:50;overflow-y:auto;padding:24px;"
         x-data x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-x-4"
         x-transition:enter-end="opacity-100 translate-x-0">

        {{-- Panel Header --}}
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;">
            <h3 style="font-size:16px;font-weight:600;color:#1C1917;">Company Details</h3>
            <button wire:click="closeTenant" style="background:none;border:none;cursor:pointer;color:#A8A29E;padding:4px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>

        {{-- Company Info --}}
        <div style="margin-bottom:20px;">
            <div style="font-size:20px;font-weight:700;color:#1C1917;margin-bottom:4px;">
                {{ $viewingTenant->name }}
            </div>
            <div style="font-size:12px;color:#A8A29E;font-family:monospace;">
                {{ $viewingTenant->slug }}
            </div>
        </div>

        <div class="krd-divider"></div>

        {{-- Details --}}
        @foreach([
            ['label' => 'Status',    'value' => ucfirst($viewingTenant->status)],
            ['label' => 'Plan',      'value' => $viewingTenant->plan?->name ?? '—'],
            ['label' => 'Currency',  'value' => $viewingTenant->billing_currency],
            ['label' => 'Domain',    'value' => $viewingTenant->domain ?? '—'],
            ['label' => 'Created',   'value' => $viewingTenant->created_at->format('M d, Y')],
        ] as $detail)
        <div style="display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid #E7E5E4;">
            <span style="font-size:12px;color:#78716C;">{{ $detail['label'] }}</span>
            <span style="font-size:12px;font-weight:500;color:#1C1917;">{{ $detail['value'] }}</span>
        </div>
        @endforeach

        <div class="krd-divider"></div>

        {{-- Actions --}}
        <div style="display:flex;flex-direction:column;gap:8px;">
            <a href="{{ route('platform.tenants.create') }}" class="krd-btn krd-btn-secondary" wire:navigate>
                Edit Company
            </a>
            @if($viewingTenant->status === 'suspended')
            <button wire:click="activate({{ $viewingTenant->id }})" class="krd-btn" style="background:#D1FAE5;color:#059669;border-color:#6EE7B7;">
                Activate Company
            </button>
            @else
            <button wire:click="confirmSuspend({{ $viewingTenant->id }})" class="krd-btn krd-btn-danger">
                Suspend Company
            </button>
            @endif
        </div>

    </div>

    {{-- Overlay --}}
    <div
        wire:click="closeTenant"
        style="position:fixed;inset:0;background:rgba(0,0,0,0.3);z-index:49;"
    ></div>
    @endif

    {{-- Suspend Confirmation Modal --}}
    @if($showSuspendModal)
    <div style="position:fixed;inset:0;background:rgba(0,0,0,0.4);z-index:60;display:flex;align-items:center;justify-content:center;">
        <div style="background:#fff;border-radius:8px;padding:28px;max-width:400px;width:90%;">
            <h3 style="font-size:16px;font-weight:600;color:#1C1917;margin-bottom:8px;">Suspend Company?</h3>
            <p style="font-size:13px;color:#78716C;margin-bottom:24px;line-height:1.6;">
                This will prevent all users of this company from accessing Koordli.
                You can reactivate them at any time.
            </p>
            <div style="display:flex;gap:10px;">
                <button wire:click="suspend" class="krd-btn krd-btn-danger" style="flex:1;">
                    Yes, Suspend
                </button>
                <button wire:click="cancelSuspend" class="krd-btn krd-btn-secondary" style="flex:1;">
                    Cancel
                </button>
            </div>
        </div>
    </div>
    @endif

</div>