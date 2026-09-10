<div x-data="{ showDeleteModal: false, deleteId: null, showDeactivateModal: false, deactivateUserId: null, deactivateIsActive: false, showRoleModal: false, roleUserId: null, roleLabel: '', showInviteForm: false }">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;">
        <div>
            <div class="krd-label" style="margin-bottom:4px;">Platform</div>
            <h2 class="krd-heading-3">Staff & Roles</h2>
        </div>
        <div style="display:flex;gap:8px;">
            <a href="{{ route('platform.staff.roles') }}" wire:navigate class="krd-btn krd-btn-secondary">Manage Roles</a>
            <button type="button" x-on:click="showInviteForm = true" class="krd-btn krd-btn-primary">+ Invite Staff</button>
        </div>
    </div>

    <div class="krd-card" style="padding:0;overflow:hidden;">
        <table class="krd-table">
            <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
                @foreach($staff as $user)
                <tr>
                    <td style="font-weight:500;">{{ $user->name }}</td>
                    <td style="font-size:12px;color:#78716C;">{{ $user->email }}</td>
                    <td><span class="krd-badge krd-badge-stone">{{ str_replace('platform_', '', $user->roles->first()?->name ?? '—') }}</span></td>
                    <td>
                        @if(!$user->invite_accepted_at && $user->invited_at)
                        <span class="krd-badge krd-badge-amber">Invited</span>
                        @elseif($user->is_active)
                        <span class="krd-badge krd-badge-green">Active</span>
                        @else
                        <span class="krd-badge krd-badge-red">Deactivated</span>
                        @endif
                    </td>
                    <td>
                        <div style="display:flex;align-items:center;gap:6px;">
                            <button type="button" x-on:click="showRoleModal = true; roleUserId = {{ $user->id }}; roleLabel = '{{ str_replace("'", "\\'", $user->roles->first()?->name ?? 'platform_support_agent') }}'; $wire.set('editingUserId', {{ $user->id }}); $wire.set('editingRole', '{{ str_replace("'", "\\'", $user->roles->first()?->name ?? 'platform_support_agent') }}')" class="krd-btn krd-btn-secondary krd-btn-sm">Change Role</button>
                            <button type="button" wire:click="openEmailEdit({{ $user->id }})" class="krd-btn krd-btn-secondary krd-btn-sm">Edit Email</button>
                            <button type="button" x-on:click="showDeactivateModal = true; deactivateUserId = {{ $user->id }}; deactivateIsActive = {{ $user->is_active ? 'true' : 'false' }}" class="krd-btn krd-btn-sm" style="background:{{ $user->is_active ? '#FEE2E2' : '#D1FAE5' }};color:{{ $user->is_active ? '#DC2626' : '#059669' }};">
                                {{ $user->is_active ? 'Deactivate' : 'Activate' }}
                            </button>
                            <button type="button" x-on:click="showDeleteModal = true; deleteId = {{ $user->id }}" class="krd-btn krd-btn-sm" style="background:#FEE2E2;color:#DC2626;border-color:#FECACA;" title="Delete staff member" aria-label="Delete staff member">
                                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6M14 11v6M9 6V4h6v2"/>
                                </svg>
                            </button>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div x-show="showInviteForm" x-cloak
        style="position:fixed;left:0;top:0;width:100vw;height:100vh;background:rgba(0,0,0,0.4);z-index:100;padding:20px;display:none;"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0">
        <div style="position:absolute;inset:0;background:rgba(28,25,23,0.42);" x-on:click="showInviteForm = false; $wire.closeInviteForm()"></div>
        <div style="position:absolute;left:50%;top:50%;transform:translate(-50%,-50%);width:100%;max-width:420px;background:#fff;border:1px solid #E7E5E4;border-radius:10px;padding:24px;box-shadow:0 20px 50px rgba(28,25,23,0.2);"
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-100"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95">
            <h3 style="font-size:15px;font-weight:600;margin-bottom:14px;">Invite Platform Staff</h3>

            <div class="krd-input-group" style="margin-bottom:10px;">
                <input wire:model="newName" type="text" class="krd-input" placeholder="Full name">
            </div>

            <div class="krd-input-group" style="margin-bottom:10px;">
                <input wire:model="newEmail" type="email" class="krd-input" placeholder="Email address">
            </div>

            <div class="krd-input-group" style="margin-bottom:14px;">
                <div x-data="{
                    dropdownOpen: false,
                    selectedRole: @js($newRole),
                    selectedLabel: @js(str_replace('platform_', '', $roles->firstWhere('name', $newRole)?->name ?? $newRole)),
                    chooseRole(roleName, roleLabel) {
                        this.selectedRole = roleName;
                        this.selectedLabel = roleLabel;
                        this.dropdownOpen = false;
                        $wire.set('newRole', roleName);
                    }
                }" x-on:click.outside="dropdownOpen = false" style="position:relative;" wire:ignore>
                    <button type="button" x-on:click="dropdownOpen = !dropdownOpen" x-bind:class="dropdownOpen ? 'krd-dropdown-trigger open' : 'krd-dropdown-trigger'" style="width:100%;display:flex;align-items:center;justify-content:space-between;text-align:left;">
                        <span x-text="selectedLabel"></span>
                        <svg class="krd-dropdown-chevron" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                    <div x-show="dropdownOpen" x-cloak class="krd-dropdown-menu" style="display:none;">
                        @foreach($roles as $r)
                        <div class="krd-dropdown-option {{ $newRole === $r->name ? 'selected' : '' }}" x-on:click.stop="chooseRole('{{ $r->name }}', '{{ str_replace('platform_', '', $r->name) }}')">{{ str_replace('platform_', '', $r->name) }}</div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div style="display:flex;gap:10px;">
                <button type="button" wire:click="inviteStaff" wire:loading.attr="disabled" wire:target="inviteStaff" class="krd-btn krd-btn-primary" style="flex:1;">
                    <span wire:loading.remove wire:target="inviteStaff">Send Invite</span>
                    <span wire:loading wire:target="inviteStaff">Sending...</span>
                </button>
                <button type="button" x-on:click="showInviteForm = false; $wire.closeInviteForm()" class="krd-btn krd-btn-secondary" style="flex:1;">Cancel</button>
            </div>
        </div>
    </div>

    <div x-show="showRoleModal" x-cloak
        style="position:fixed;left:0;top:0;width:100vw;height:100vh;background:rgba(0,0,0,0.4);z-index:100;padding:20px;display:none;"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0">
        <div style="position:absolute;left:50%;top:50%;transform:translate(-50%,-50%);width:100%;max-width:380px;background:#fff;border:1px solid #E7E5E4;border-radius:10px;padding:24px;box-shadow:0 20px 50px rgba(28,25,23,0.2);"
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-100"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95">
            <h3 style="font-size:15px;font-weight:600;margin-bottom:14px;">Change Role</h3>

            <div x-data="{
                dropdownOpen: false,
                selectedRole: @js($editingRole),
                selectedLabel: @js(str_replace('platform_', '', $roles->firstWhere('name', $editingRole)?->name ?? $editingRole)),
                chooseRole(roleName, roleLabel) {
                    this.selectedRole = roleName;
                    this.selectedLabel = roleLabel;
                    this.dropdownOpen = false;
                    $wire.set('editingRole', roleName);
                }
            }" x-on:click.outside="dropdownOpen = false" style="position:relative;" wire:ignore>
                <button type="button" x-on:click="dropdownOpen = !dropdownOpen" x-bind:class="dropdownOpen ? 'krd-dropdown-trigger open' : 'krd-dropdown-trigger'" style="width:100%;display:flex;align-items:center;justify-content:space-between;text-align:left;">
                    <span x-text="selectedLabel || 'Select role'"></span>
                    <svg class="krd-dropdown-chevron" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                </button>
                <div x-show="dropdownOpen" x-cloak class="krd-dropdown-menu" style="display:none;">
                    @foreach($roles as $r)
                    <div class="krd-dropdown-option {{ $editingRole === $r->name ? 'selected' : '' }}" x-on:click.stop="chooseRole('{{ $r->name }}', '{{ str_replace('platform_', '', $r->name) }}')">{{ str_replace('platform_', '', $r->name) }}</div>
                    @endforeach
                </div>
            </div>

            <div style="display:flex;gap:10px;margin-top:16px;">
                <button type="button" wire:click="saveRole" wire:loading.attr="disabled" wire:target="saveRole" class="krd-btn krd-btn-primary" style="flex:1;">
                    <span wire:loading.remove wire:target="saveRole">Save</span>
                    <span wire:loading wire:target="saveRole">Saving...</span>
                </button>
                <button type="button" x-on:click="showRoleModal = false; roleUserId = null; roleLabel = ''; $wire.closeRoleModal()" class="krd-btn krd-btn-secondary" style="flex:1;">Cancel</button>
            </div>
        </div>
    </div>

    <div x-show="showDeactivateModal" x-cloak
        style="position:fixed;left:0;top:0;width:100vw;height:100vh;background:rgba(0,0,0,0.4);z-index:100;padding:20px;display:none;"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0">
        <div style="position:absolute;left:50%;top:50%;transform:translate(-50%,-50%);width:100%;max-width:400px;background:#fff;border:1px solid #E7E5E4;border-radius:10px;padding:24px;box-shadow:0 20px 50px rgba(28,25,23,0.2);"
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-100"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95">
            <h3 style="font-size:16px;font-weight:600;color:#1C1917;margin-bottom:8px;">
                <span x-text="deactivateIsActive ? 'Deactivate Staff Member?' : 'Activate Staff Member?'"></span>
            </h3>
            <p style="font-size:13px;color:#78716C;margin-bottom:24px;line-height:1.6;">
                <span x-text="deactivateIsActive ? 'This staff member will no longer be able to log in. You can reactivate them at any time.' : 'This staff member will be able to log in again.'"></span>
            </p>
            <div style="display:flex;gap:10px;">
                <button type="button" x-on:click="const id = deactivateUserId; const isActive = deactivateIsActive; showDeactivateModal = false; deactivateUserId = null; deactivateIsActive = false; $wire.toggleActive(id)" class="krd-btn" :class="deactivateIsActive ? 'krd-btn-danger' : 'krd-btn-primary'" style="flex:1;">
                    <span x-text="deactivateIsActive ? 'Yes, Deactivate' : 'Yes, Activate'"></span>
                </button>
                <button type="button" x-on:click="showDeactivateModal = false; deactivateUserId = null; deactivateIsActive = false" class="krd-btn krd-btn-secondary" style="flex:1;">Cancel</button>
            </div>
        </div>
    </div>

    <div x-show="showDeleteModal" x-cloak
        style="position:fixed;left:0;top:0;width:100vw;height:100vh;background:rgba(0,0,0,0.4);z-index:100;padding:20px;display:none;"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0">
        <div style="position:absolute;left:50%;top:50%;transform:translate(-50%,-50%);width:100%;max-width:400px;background:#fff;border-radius:8px;padding:28px;box-shadow:0 20px 50px rgba(28,25,23,0.2);"
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-100"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95">
            <h3 style="font-size:16px;font-weight:600;color:#1C1917;margin-bottom:8px;">Delete Staff Member?</h3>
            <p style="font-size:13px;color:#78716C;margin-bottom:24px;line-height:1.6;">
                This will permanently delete this platform staff member and cannot be undone.
            </p>
            <div style="display:flex;gap:10px;">
                <button type="button" x-on:click="const id = deleteId; showDeleteModal = false; deleteId = null; $wire.deleteStaff(id)" class="krd-btn krd-btn-danger" style="flex:1;">Yes, Delete</button>
                <button type="button" x-on:click="showDeleteModal = false; deleteId = null" class="krd-btn krd-btn-secondary" style="flex:1;">Cancel</button>
            </div>
        </div>
    </div>

    <div x-show="$wire.showEmailEditModal" x-cloak
        style="position:fixed;left:0;top:0;width:100vw;height:100vh;background:rgba(0,0,0,0.4);z-index:100;padding:20px;display:none;">
        <div style="position:absolute;left:50%;top:50%;transform:translate(-50%,-50%);width:100%;max-width:380px;background:#fff;border-radius:10px;padding:24px;">
            <h3 style="font-size:15px;font-weight:600;margin-bottom:14px;">Edit Email Address</h3>
            <input wire:model="emailEditValue" type="email" class="krd-input @error('emailEditValue') krd-input-error @enderror" style="margin-bottom:8px;">
            @error('emailEditValue')<span class="krd-input-error-msg">{{ $message }}</span>@enderror
            <div style="display:flex;gap:10px;margin-top:14px;">
                <button wire:click="saveEmailEdit" class="krd-btn krd-btn-primary" style="flex:1;">Save</button>
                <button type="button" wire:click="$set('showEmailEditModal', false)" class="krd-btn krd-btn-secondary" style="flex:1;">Cancel</button>
            </div>
        </div>
    </div>
</div>