<div>
    {{-- Header --}}
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px;">
        <div>
            <div class="krd-label" style="margin-bottom:4px;">Business</div>
            <h2 class="krd-heading-3" style="color:#1C1917;">Staff</h2>
        </div>
        <a href="{{ route('tenant.staff.invite') }}" wire:navigate class="krd-btn krd-btn-primary">
            + Invite Staff
        </a>
    </div>

    {{-- Filters --}}
    <div style="display:flex;gap:10px;margin-bottom:16px;flex-wrap:wrap;align-items:center;">
        <input
            wire:model.live.debounce.300ms="search"
            type="text"
            class="krd-input"
            placeholder="Search staff..."
            style="max-width:240px;min-width:140px;"
        />

        <x-ui.dropdown wire="statusFilter" placeholder="All statuses"
            selected="{{ $statusFilter === 'active' ? 'Active' : ($statusFilter === 'inactive' ? 'Inactive' : 'All statuses') }}"
            max-width="160px">
            <div class="krd-dropdown-option" x-on:click="select('Active', 'active')">
                <span style="width:8px;height:8px;border-radius:50%;background:#10B981;flex-shrink:0;display:inline-block;"></span>
                Active
            </div>
            <div class="krd-dropdown-option" x-on:click="select('Inactive', 'inactive')">
                <span style="width:8px;height:8px;border-radius:50%;background:#A8A29E;flex-shrink:0;display:inline-block;"></span>
                Inactive
            </div>
        </x-ui.dropdown>

        <x-ui.dropdown wire="roleFilter" placeholder="All roles"
            selected="{{ $roleFilter ? ucfirst(str_replace('_', ' ', $roleFilter)) : 'All roles' }}"
            max-width="160px">
            @foreach(['company_owner' => 'Owner', 'manager' => 'Manager', 'staff' => 'Staff'] as $value => $label)
            <div class="krd-dropdown-option {{ $roleFilter === $value ? 'selected' : '' }}"
                x-on:click="select('{{ $label }}', '{{ $value }}')">
                {{ $label }}
            </div>
            @endforeach
        </x-ui.dropdown>

        @if($search || $statusFilter || $roleFilter)
        <button wire:click="$set('search', ''); $set('statusFilter', ''); $set('roleFilter', '')"
            class="krd-btn krd-btn-ghost krd-btn-sm" style="color:#EF4444;">
            Clear
        </button>
        @endif
    </div>

    {{-- Staff List — card-based on mobile, table on desktop --}}
    <div class="krd-card" style="padding:0;overflow:hidden;">

        {{-- Desktop Table --}}
        <div class="krd-table-wrap" style="display:none;" id="staff-table-desktop">
            <table class="krd-table">
                <thead>
                    <tr>
                        <th>Staff Member</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Last Login</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($staff as $member)
                    @php $role = $member->roles->first(); @endphp
                    <tr>
                        <td>
                            <div style="display:flex;align-items:center;gap:10px;">
                                <div style="width:34px;height:34px;border-radius:6px;background:#EDE9FE;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:600;color:#7C3AED;flex-shrink:0;">
                                    {{ strtoupper(substr($member->name, 0, 1)) }}
                                </div>
                                <div>
                                    <div style="font-size:13px;font-weight:500;color:#1C1917;">
                                        {{ $member->name }}
                                        @if($member->id === auth()->id())
                                        <span style="font-size:10px;color:#A8A29E;font-weight:400;"> (you)</span>
                                        @endif
                                    </div>
                                    <div style="font-size:11px;color:#A8A29E;">{{ $member->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            @if($role)
                            <span class="krd-badge {{ $role->name === 'company_owner' ? 'krd-badge-violet' : ($role->name === 'manager' ? 'krd-badge-blue' : 'krd-badge-stone') }}">
                                {{ ucfirst(str_replace('_', ' ', $role->name)) }}
                            </span>
                            @else
                            <span class="krd-badge krd-badge-ghost">No Role</span>
                            @endif
                        </td>
                        <td>
                            @if($member->is_active)
                            <span class="krd-badge krd-badge-green">Active</span>
                            @else
                            <span class="krd-badge krd-badge-stone">Inactive</span>
                            @endif
                        </td>
                        <td style="font-size:12px;color:#78716C;">
                            {{ $member->last_login_at ? $member->last_login_at->diffForHumans() : 'Never' }}
                        </td>
                        <td>
                            <div style="display:flex;align-items:center;gap:6px;">
                                <a href="{{ route('tenant.staff.edit', $member->id) }}" wire:navigate class="krd-btn krd-btn-secondary krd-btn-sm">Edit</a>
                                @if($member->id !== auth()->id())
                                <button
                                    wire:click="confirmToggleActive({{ $member->id }}, {{ $member->is_active ? 'true' : 'false' }})"
                                    class="krd-btn krd-btn-sm"
                                    style="{{ $member->is_active ? 'background:#FEE2E2;color:#DC2626;border-color:#FECACA;' : 'background:#D1FAE5;color:#059669;border-color:#6EE7B7;' }}"
                                >{{ $member->is_active ? 'Deactivate' : 'Activate' }}</button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5">
                            <div class="krd-empty-state">
                                <div class="krd-empty-state-icon">👥</div>
                                <div class="krd-empty-state-title">No staff found</div>
                                <div class="krd-empty-state-desc">Invite your first team member to get started.</div>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile Cards --}}
        <div id="staff-cards-mobile">
            @forelse($staff as $member)
            @php $role = $member->roles->first(); @endphp
            <div style="padding:16px;border-bottom:1px solid #E7E5E4;">
                <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px;">
                    {{-- Left: Avatar + Info --}}
                    <div style="display:flex;align-items:center;gap:10px;flex:1;min-width:0;">
                        <div style="width:36px;height:36px;border-radius:6px;background:#EDE9FE;display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:600;color:#7C3AED;flex-shrink:0;">
                            {{ strtoupper(substr($member->name, 0, 1)) }}
                        </div>
                        <div style="min-width:0;">
                            <div style="font-size:13px;font-weight:500;color:#1C1917;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                {{ $member->name }}
                                @if($member->id === auth()->id())
                                <span style="font-size:10px;color:#A8A29E;font-weight:400;"> (you)</span>
                                @endif
                            </div>
                            <div style="font-size:11px;color:#A8A29E;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $member->email }}</div>
                        </div>
                    </div>

                    {{-- Right: Status badge --}}
                    <div style="flex-shrink:0;">
                        @if($member->is_active)
                        <span class="krd-badge krd-badge-green">Active</span>
                        @else
                        <span class="krd-badge krd-badge-stone">Inactive</span>
                        @endif
                    </div>
                </div>

                {{-- Role + Last Login --}}
                <div style="display:flex;align-items:center;gap:10px;margin-top:10px;flex-wrap:wrap;">
                    @if($role)
                    <span class="krd-badge {{ $role->name === 'company_owner' ? 'krd-badge-violet' : ($role->name === 'manager' ? 'krd-badge-blue' : 'krd-badge-stone') }}">
                        {{ ucfirst(str_replace('_', ' ', $role->name)) }}
                    </span>
                    @else
                    <span class="krd-badge krd-badge-ghost">No Role</span>
                    @endif
                    <span style="font-size:11px;color:#A8A29E;">
                        {{ $member->last_login_at ? $member->last_login_at->diffForHumans() : 'Never logged in' }}
                    </span>
                </div>

                {{-- Actions --}}
                <div style="display:flex;gap:8px;margin-top:12px;">
                    <a href="{{ route('tenant.staff.edit', $member->id) }}" wire:navigate class="krd-btn krd-btn-secondary krd-btn-sm">Edit</a>
                    @if($member->id !== auth()->id())
                    <button
                        wire:click="confirmToggleActive({{ $member->id }}, {{ $member->is_active ? 'true' : 'false' }})"
                        class="krd-btn krd-btn-sm"
                        style="{{ $member->is_active ? 'background:#FEE2E2;color:#DC2626;border-color:#FECACA;' : 'background:#D1FAE5;color:#059669;border-color:#6EE7B7;' }}"
                    >{{ $member->is_active ? 'Deactivate' : 'Activate' }}</button>
                    @endif
                </div>
            </div>
            @empty
            <div class="krd-empty-state">
                <div class="krd-empty-state-icon">👥</div>
                <div class="krd-empty-state-title">No staff found</div>
                <div class="krd-empty-state-desc">Invite your first team member to get started.</div>
            </div>
            @endforelse
        </div>

        @if($staff->hasPages())
        <div style="padding:12px 16px;border-top:1px solid #E7E5E4;">
            {{ $staff->links() }}
        </div>
        @endif
    </div>

    {{-- Deactivate/Activate Modal --}}
    @if($showDeactivateModal)
    <div style="position:fixed;inset:0;background:rgba(0,0,0,0.4);z-index:60;display:flex;align-items:center;justify-content:center;padding:16px;">
        <div style="background:#fff;border-radius:8px;padding:24px;max-width:400px;width:100%;">
            <h3 style="font-size:16px;font-weight:600;color:#1C1917;margin-bottom:8px;">
                {{ $targetIsActive ? 'Deactivate Staff Member?' : 'Activate Staff Member?' }}
            </h3>
            <p style="font-size:13px;color:#78716C;margin-bottom:24px;line-height:1.6;">
                @if($targetIsActive)
                    This staff member will no longer be able to log in. You can reactivate them at any time.
                @else
                    This staff member will be able to log in again.
                @endif
            </p>
            <div style="display:flex;gap:10px;">
                <button wire:click="toggleActive" class="krd-btn {{ $targetIsActive ? 'krd-btn-danger' : 'krd-btn-primary' }}" style="flex:1;">
                    {{ $targetIsActive ? 'Yes, Deactivate' : 'Yes, Activate' }}
                </button>
                <button wire:click="cancelToggle" class="krd-btn krd-btn-secondary" style="flex:1;">Cancel</button>
            </div>
        </div>
    </div>
    @endif

</div>

<style>
@media (min-width: 768px) {
    #staff-table-desktop { display: block !important; }
    #staff-cards-mobile  { display: none !important; }
}
</style>