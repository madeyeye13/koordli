<div x-data="{ showRoleForm: false }">
    <div style="margin-bottom:16px;">
        <a href="{{ route('platform.staff') }}" wire:navigate style="color:#A8A29E;font-size:13px;">← Back to Staff</a>
    </div>
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;">
        <h2 class="krd-heading-3">Platform Roles</h2>
        <button x-on:click="showRoleForm = true" wire:click="newRole" class="krd-btn krd-btn-primary">+ New Role</button>
    </div>

    <div style="display:flex;flex-direction:column;gap:12px;">
        @foreach($roles as $role)
        <div class="krd-card" style="padding:16px;display:flex;justify-content:space-between;align-items:center;">
            <div>
                <div style="font-weight:600;font-size:14px;">{{ str_replace('platform_', '', $role->name) }}</div>
                <div style="font-size:11.5px;color:#78716C;">{{ $role->permissions->count() }} permissions · {{ $role->users_count }} staff</div>
            </div>
            @if(!in_array($role->name, $systemRoles))
            <div style="display:flex;gap:6px;">
                <button x-on:click="showRoleForm = true" wire:click="editRole({{ $role->id }})" class="krd-btn krd-btn-secondary krd-btn-sm">Edit</button>
                <button wire:click="deleteRole({{ $role->id }})" class="krd-btn krd-btn-sm" style="background:#FEE2E2;color:#DC2626;">Delete</button>
            </div>
            @else
            <span class="krd-badge krd-badge-stone">System role</span>
            @endif
        </div>
        @endforeach
    </div>

    <template x-teleport="body">
    <div x-show="showRoleForm" x-cloak
        style="position:fixed;left:0;top:0;width:100vw;height:100vh;background:rgba(0,0,0,0.4);z-index:100;padding:20px;display:none;"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0">
        <div style="position:absolute;left:50%;top:50%;transform:translate(-50%,-50%);width:100%;max-width:520px;background:#fff;border:1px solid #E7E5E4;border-radius:10px;padding:24px;box-shadow:0 20px 50px rgba(28,25,23,0.2);max-height:80vh;overflow-y:auto;"
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-100"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95">
            <h3 style="font-size:15px;font-weight:600;margin-bottom:14px;">{{ $editingRoleId ? 'Edit Role' : 'New Role' }}</h3>
            <input wire:model="roleName" type="text" class="krd-input" placeholder="Role name, e.g. Billing Support" style="margin-bottom:14px;">

            <div class="krd-label" style="margin-bottom:8px;">Permissions</div>
            <div style="display:flex;flex-direction:column;gap:6px;max-height:280px;overflow-y:auto;margin-bottom:16px;">
                @foreach($permissions as $p)
                <label style="display:flex;align-items:center;gap:8px;font-size:12.5px;cursor:pointer;">
                    <input type="checkbox" wire:click="togglePermission('{{ $p->name }}')" {{ in_array($p->name, $selectedPermissions) ? 'checked' : '' }} style="accent-color:#7C3AED;">
                    {{ $p->name }}
                </label>
                @endforeach
            </div>

            <div style="display:flex;gap:10px;">
                <button wire:click="saveRole" x-on:click="showRoleForm = false" class="krd-btn krd-btn-primary" style="flex:1;">Save Role</button>
                <button type="button" x-on:click="showRoleForm = false" class="krd-btn krd-btn-secondary" style="flex:1;">Cancel</button>
            </div>
        </div>
    </div>
    </template>
</div>