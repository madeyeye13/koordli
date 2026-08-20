<div x-data="rolePermissionsUI()" x-init="init()">
    <div style="margin-bottom:24px;">
        <div style="margin-bottom:8px;">
            <a href="{{ route('tenant.staff') }}" wire:navigate style="color:#A8A29E;text-decoration:none;font-size:13px;">
                ← Back to Staff
            </a>
        </div>
        <div class="krd-label" style="margin-bottom:4px;">Business</div>
        <h2 class="krd-heading-3" style="color:#1C1917;">Roles &amp; Permissions</h2>
    </div>

    <div id="roles-grid" style="display:grid;grid-template-columns:1fr;gap:16px;">

        {{-- Role list --}}
        <div class="krd-card" style="padding:16px;">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
                <div style="font-size:13px;font-weight:600;color:#1C1917;">Roles</div>
                <button wire:click="showCreateRole" class="krd-btn krd-btn-primary krd-btn-sm">+ New Role</button>
            </div>

            <div style="display:flex;flex-direction:column;gap:6px;">
                @foreach($roles as $r)
                <div x-on:click="pick({{ $r->id }})"
                    :style="selectedRoleId === {{ $r->id }} ? 'padding:10px 12px;border-radius:6px;cursor:pointer;display:flex;align-items:center;justify-content:space-between;gap:8px;background:#F5F3FF;border:1px solid #DDD6FE;' : 'padding:10px 12px;border-radius:6px;cursor:pointer;display:flex;align-items:center;justify-content:space-between;gap:8px;background:transparent;border:1px solid transparent;'">
                    <div>
                        <div :style="selectedRoleId === {{ $r->id }} ? 'font-size:13px;font-weight:600;color:#7C3AED;' : 'font-size:13px;font-weight:600;color:#1C1917;'">
                            {{ $r->name }}{{ $r->is_system ? ' 🔒' : '' }}
                        </div>
                        <div style="font-size:11px;color:#A8A29E;">{{ $r->users_count }} staff · {{ $r->permissions->count() }} permission(s)</div>
                    </div>
                    @if(!$r->is_system)
                    <div style="display:flex;gap:4px;" x-on:click.stop>
                        <button wire:click="showRenameRole({{ $r->id }})" title="Rename"
                            style="background:none;border:none;cursor:pointer;color:#A8A29E;font-size:12px;padding:2px 4px;">✎</button>
                        <button wire:click="confirmDeleteRole({{ $r->id }})" title="Delete"
                            style="background:none;border:none;cursor:pointer;color:#DC2626;font-size:12px;padding:2px 4px;">✕</button>
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>

        {{-- Permission checklist --}}
        <div class="krd-card" style="padding:20px;">
            <template x-if="selectedRole">
                <div>
                    <div style="margin-bottom:16px;">
                        <div style="font-size:14px;font-weight:700;color:#1C1917;" x-text="selectedRole.name"></div>
                        <template x-if="selectedRole.isSystem">
                            <div style="font-size:12px;color:#7C3AED;margin-top:4px;">
                                This is the account owner role — it always has full access and can't be edited.
                            </div>
                        </template>
                        <template x-if="!selectedRole.isSystem">
                            <div style="font-size:12px;color:#A8A29E;margin-top:4px;">
                                Toggle which actions staff with this role can perform.
                            </div>
                        </template>
                    </div>

                    <div style="display:flex;flex-direction:column;gap:16px;">
                        @foreach($groupedPermissions as $groupName => $perms)
                        <div>
                            <div style="font-size:11px;font-weight:700;color:#78716C;text-transform:uppercase;letter-spacing:0.03em;margin-bottom:8px;">{{ $groupName }}</div>
                            <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(220px, 1fr));gap:6px;">
                                @foreach($perms as $perm)
                                <label style="display:flex;align-items:center;gap:8px;padding:6px 8px;border-radius:5px;font-size:12px;color:#1C1917;"
                                    :style="selectedRole.isSystem ? '' : 'cursor:pointer;'">
                                    {{-- PLACEHOLDER: native checkbox — swap for the app's real checkbox
                                         component once available. Bound to Alpine state (isChecked) rather
                                         than a Blade {{ }} value since it must update instantly when the
                                         selected role changes client-side, per Rule 29 (Alpine ownership). --}}
                                    <input type="checkbox"
                                        :checked="isChecked('{{ $perm->name }}')"
                                        :disabled="selectedRole.isSystem"
                                        x-on:click="!selectedRole.isSystem && toggle('{{ $perm->name }}')"
                                        style="width:14px;height:14px;accent-color:#7C3AED;" />
                                    {{ $perm->name }}
                                </label>
                                @endforeach
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </template>
            <template x-if="!selectedRole">
                <div style="color:#A8A29E;font-size:13px;">Select a role to view its permissions.</div>
            </template>
        </div>

    </div>

    {{-- Create/Rename Role Modal --}}
    @if($showRoleForm)
    <div style="position:fixed;inset:0;background:rgba(0,0,0,0.4);z-index:60;display:flex;align-items:center;justify-content:center;padding:16px;">
        <div style="background:#fff;border-radius:8px;padding:24px;max-width:400px;width:100%;">
            <h3 style="font-size:16px;font-weight:600;color:#1C1917;margin-bottom:16px;">
                {{ $editingRoleId ? 'Rename Role' : 'New Role' }}
            </h3>
            <div class="krd-input-group">
                <label class="krd-label-text">Role Name</label>
                <input wire:model="roleName" type="text" class="krd-input @error('roleName') krd-input-error @enderror"
                    placeholder="e.g. Lead Planner" autofocus />
                @error('roleName') <span class="krd-input-error-msg">{{ $message }}</span> @enderror
            </div>
            <div style="display:flex;gap:10px;margin-top:16px;">
                <button wire:click="saveRole" class="krd-btn krd-btn-primary" style="flex:1;">Save</button>
                <button wire:click="cancelRoleForm" class="krd-btn krd-btn-ghost" style="flex:1;">Cancel</button>
            </div>
        </div>
    </div>
    @endif

    {{-- Delete Role Modal --}}
    @if($showDeleteModal)
    <div style="position:fixed;inset:0;background:rgba(0,0,0,0.4);z-index:60;display:flex;align-items:center;justify-content:center;padding:16px;">
        <div style="background:#fff;border-radius:8px;padding:24px;max-width:400px;width:100%;">
            <h3 style="font-size:16px;font-weight:600;color:#1C1917;margin-bottom:8px;">Delete Role?</h3>
            <p style="font-size:13px;color:#78716C;margin-bottom:24px;line-height:1.6;">
                This cannot be undone. If any staff members are still assigned this role, deletion will be blocked until they're reassigned.
            </p>
            <div style="display:flex;gap:10px;">
                <button wire:click="deleteRole" class="krd-btn krd-btn-danger" style="flex:1;">Yes, Delete</button>
                <button wire:click="cancelDeleteRole" class="krd-btn krd-btn-secondary" style="flex:1;">Cancel</button>
            </div>
        </div>
    </div>
    @endif

</div>

<script type="application/json" id="roles-permissions-data">
    {!! $rolesDataJson !!}
</script>

<script>
    function rolePermissionsUI() {
        return {
            rolesById: {},
            selectedRoleId: {{ $initialSelectedRoleId ?? 'null' }},
            selectedRole: null,

            init() {
                const raw = document.getElementById('roles-permissions-data').textContent;
                this.rolesById = JSON.parse(raw);
                this.syncSelectedRole();
            },

            syncSelectedRole() {
                this.selectedRole = this.rolesById[this.selectedRoleId] ?? null;
            },

            pick(roleId) {
                this.selectedRoleId = roleId;
                this.syncSelectedRole();
                // Keep the server in sync in the background — Renderless,
                // so this never triggers a re-render or visible delay.
                $wire.selectRole(roleId);
            },

            isChecked(permissionName) {
                if (!this.selectedRole) return false;
                return this.selectedRole.permissions.includes(permissionName);
            },

            toggle(permissionName) {
                if (!this.selectedRole) return;

                // Optimistic local update so the checkbox flips instantly —
                // Livewire's Renderless call persists it in the background.
                const idx = this.selectedRole.permissions.indexOf(permissionName);
                if (idx === -1) {
                    this.selectedRole.permissions.push(permissionName);
                } else {
                    this.selectedRole.permissions.splice(idx, 1);
                }

                $wire.togglePermission(permissionName);
            },
        };
    }
</script>

<style>
@media (min-width: 900px) {
    #roles-grid { grid-template-columns: 280px 1fr !important; }
}
</style>