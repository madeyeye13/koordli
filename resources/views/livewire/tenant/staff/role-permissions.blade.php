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
                <button x-on:click="openCreateRole()" class="krd-btn krd-btn-primary krd-btn-sm">+ New Role</button>
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
                        <button x-on:click="openRenameRole({{ $r->id }}, @js($r->name))" title="Rename"
                            style="background:none;border:none;cursor:pointer;color:#A8A29E;font-size:12px;padding:2px 4px;">✎</button>
                        <button x-on:click="openDeleteRole({{ $r->id }})" title="Delete"
                            style="background:none;border:none;cursor:pointer;color:#DC2626;padding:2px 4px;display:flex;align-items:center;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/>
                                <path d="M10 11v6M14 11v6M9 6V4h6v2"/>
                            </svg>
                        </button>
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

    {{-- Create/Rename Role Modal — Alpine-owned visibility, instant open/close --}}
    <template x-teleport="body">
    <div x-show="showRoleModal" x-cloak style="position:fixed;inset:0;z-index:60;">
        <div style="position:absolute;inset:0;background:rgba(0,0,0,0.4);"></div>
        <div style="position:relative;height:100%;display:flex;align-items:center;justify-content:center;padding:16px;">
            <div style="background:#fff;border-radius:8px;padding:24px;max-width:400px;width:100%;">
                <h3 style="font-size:16px;font-weight:600;color:#1C1917;margin-bottom:16px;" x-text="roleModalMode === 'rename' ? 'Rename Role' : 'New Role'"></h3>
                <div class="krd-input-group">
                    <label class="krd-label-text">Role Name</label>
                    <input x-model="roleModalName" type="text" class="krd-input @error('roleName') krd-input-error @enderror"
                        placeholder="e.g. Lead Planner" />
                    @error('roleName') <span class="krd-input-error-msg">{{ $message }}</span> @enderror
                </div>
                <div style="display:flex;gap:10px;margin-top:16px;">
                    <button x-on:click="saveRoleModal()" class="krd-btn krd-btn-primary" style="flex:1;">Save</button>
                    <button x-on:click="closeRoleModal()" class="krd-btn krd-btn-ghost" style="flex:1;">Cancel</button>
                </div>
            </div>
        </div>
    </div>
    </template>

    {{-- Delete Role Modal — Alpine-owned visibility, instant open/close --}}
    <template x-teleport="body">
    <div x-show="showDeleteRoleModal" x-cloak style="position:fixed;inset:0;z-index:60;">
        <div style="position:absolute;inset:0;background:rgba(0,0,0,0.4);"></div>
        <div style="position:relative;height:100%;display:flex;align-items:center;justify-content:center;padding:16px;">
            <div style="background:#fff;border-radius:8px;padding:24px;max-width:400px;width:100%;">
                <h3 style="font-size:16px;font-weight:600;color:#1C1917;margin-bottom:8px;">Delete Role?</h3>
                <p style="font-size:13px;color:#78716C;margin-bottom:24px;line-height:1.6;">
                    This cannot be undone. If any staff members are still assigned this role, deletion will be blocked until they're reassigned.
                </p>
                <div style="display:flex;gap:10px;">
                    <button x-on:click="confirmDeleteRoleModal()" class="krd-btn krd-btn-danger" style="flex:1;">Yes, Delete</button>
                    <button x-on:click="closeDeleteRoleModal()" class="krd-btn krd-btn-secondary" style="flex:1;">Cancel</button>
                </div>
            </div>
        </div>
    </div>
    </template>

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

            showRoleModal: false,
            roleModalMode: 'create',
            roleModalId: null,
            roleModalName: '',

            showDeleteRoleModal: false,
            deleteRoleModalId: null,

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
                $wire.selectRole(roleId); // Renderless — background sync only
            },

            isChecked(permissionName) {
                if (!this.selectedRole) return false;
                return this.selectedRole.permissions.includes(permissionName);
            },

            toggle(permissionName) {
                if (!this.selectedRole) return;

                const idx = this.selectedRole.permissions.indexOf(permissionName);
                if (idx === -1) {
                    this.selectedRole.permissions.push(permissionName);
                } else {
                    this.selectedRole.permissions.splice(idx, 1);
                }

                $wire.togglePermission(permissionName); // Renderless — background sync only
            },

            // ── Create/Rename modal — instant, no network call to open/close ──
            openCreateRole() {
                this.roleModalMode = 'create';
                this.roleModalId = null;
                this.roleModalName = '';
                this.showRoleModal = true;
            },

            openRenameRole(id, name) {
                this.roleModalMode = 'rename';
                this.roleModalId = id;
                this.roleModalName = name;
                this.showRoleModal = true;
            },

            closeRoleModal() {
                this.showRoleModal = false;
            },

            async saveRoleModal() {
                try {
                    await $wire.saveRole(this.roleModalMode === 'rename' ? this.roleModalId : null, this.roleModalName);
                    this.showRoleModal = false;
                } catch (e) {
                    // Validation failed server-side — Livewire re-renders with
                    // the inline error message; keep the modal open so the
                    // person can see it and correct their input.
                }
            },

            // ── Delete modal — instant, no network call to open/close ──
            openDeleteRole(id) {
                this.deleteRoleModalId = id;
                this.showDeleteRoleModal = true;
            },

            closeDeleteRoleModal() {
                this.showDeleteRoleModal = false;
            },

            confirmDeleteRoleModal() {
                $wire.deleteRole(this.deleteRoleModalId);
                this.showDeleteRoleModal = false;
            },
        };
    }
</script>

<style>
@media (min-width: 900px) {
    #roles-grid { grid-template-columns: 280px 1fr !important; }
}
</style>