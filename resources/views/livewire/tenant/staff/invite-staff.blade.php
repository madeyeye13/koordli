<div>
    {{-- Header --}}
    <div style="margin-bottom:28px;">
        <div style="margin-bottom:8px;">
            <a href="{{ route('tenant.staff') }}" wire:navigate style="color:#A8A29E;text-decoration:none;font-size:13px;">
                ← Back to Staff
            </a>
        </div>
        <div class="krd-label" style="margin-bottom:4px;">Business</div>
        <h2 class="krd-heading-3" style="color:#1C1917;">
            {{ $editUser ? 'Edit Staff Member' : 'Invite Staff Member' }}
        </h2>
    </div>

    {{-- Responsive grid: stacks on mobile, side-by-side on desktop --}}
    <div id="invite-grid" style="display:grid;grid-template-columns:1fr;gap:16px;">

        {{-- Form --}}
        <div class="krd-card" style="padding:24px;">

            <div class="krd-input-group">
                <label class="krd-label-text">Full Name <span style="color:#EF4444;">*</span></label>
                <input wire:model="name" type="text" class="krd-input @error('name') krd-input-error @enderror"
                    placeholder="e.g. Amara Johnson" autofocus />
                @error('name') <span class="krd-input-error-msg">{{ $message }}</span> @enderror
            </div>

            <div class="krd-input-group">
                <label class="krd-label-text">Email Address <span style="color:#EF4444;">*</span></label>
                <input wire:model="email" type="email"
                    class="krd-input @error('email') krd-input-error @enderror"
                    placeholder="staff@company.com"
                    {{ $editUser ? 'disabled' : '' }} />
                @error('email') <span class="krd-input-error-msg">{{ $message }}</span> @enderror
                @if($editUser)
                <span class="krd-input-hint">Email cannot be changed after invitation.</span>
                @endif
            </div>

            <div class="krd-input-group">
                <label class="krd-label-text">Role <span style="color:#EF4444;">*</span></label>
                <x-ui.dropdown wire="selectedRoleId" placeholder="Select role" selected="{{ $selectedRoleLabel }}">
                    @foreach($roles as $r)
                    <div class="krd-dropdown-option {{ $selectedRoleId === $r->id ? 'selected' : '' }}"
                        x-on:click="select(@js($r->name), {{ $r->id }})">
                        <span style="width:8px;height:8px;border-radius:50%;background:{{ $r->is_system ? '#7C3AED' : '#3B82F6' }};flex-shrink:0;display:inline-block;"></span>
                        {{ $r->name }}
                    </div>
                    @endforeach
                </x-ui.dropdown>
                @error('selectedRoleId') <span class="krd-input-error-msg">{{ $message }}</span> @enderror
            </div>

            <div class="krd-input-group" style="margin-bottom:0;">
                <label class="krd-label-text">
                    {{ $editUser ? 'New Password' : 'Temporary Password' }}
                    @if($editUser)
                    <span style="color:#A8A29E;font-weight:400;"> — leave blank to keep current</span>
                    @endif
                </label>
                <input wire:model="password" type="text"
                    class="krd-input @error('password') krd-input-error @enderror"
                    placeholder="{{ $editUser ? 'Leave blank to keep current' : 'Auto-generated' }}" />
                @error('password') <span class="krd-input-error-msg">{{ $message }}</span> @enderror
                @if(!$editUser)
                <span class="krd-input-hint">This will be sent to the staff member. They must change it on first login.</span>
                @endif
            </div>

            <div style="margin-top:20px;display:flex;gap:10px;flex-wrap:wrap;">
                <button wire:click="save" wire:loading.attr="disabled" class="krd-btn krd-btn-primary krd-btn-lg" style="flex:1;min-width:160px;">
                    <span wire:loading.remove wire:target="save">
                        {{ $editUser ? 'Update Staff Member' : 'Send Invitation' }}
                    </span>
                    <span wire:loading wire:target="save">Saving...</span>
                </button>
                <a href="{{ route('tenant.staff') }}" wire:navigate class="krd-btn krd-btn-ghost">Cancel</a>
            </div>
        </div>

        {{-- Info Panel --}}
        <div style="display:flex;flex-direction:column;gap:12px;">
            <div class="krd-card" style="padding:20px;">
                <div style="font-size:13px;font-weight:600;color:#1C1917;margin-bottom:12px;">👥 Roles in your workspace</div>
                <div style="display:flex;flex-direction:column;gap:12px;">
                    @foreach($roles as $r)
                    <div style="padding:12px;border-radius:6px;border-left:3px solid {{ $r->is_system ? '#7C3AED' : '#3B82F6' }};background:{{ $r->is_system ? '#7C3AED' : '#3B82F6' }}11;">
                        <div style="font-size:12px;font-weight:600;color:{{ $r->is_system ? '#7C3AED' : '#3B82F6' }};margin-bottom:3px;">
                            {{ $r->name }}{{ $r->is_system ? ' (Owner)' : '' }}
                        </div>
                        <div style="font-size:11px;color:#78716C;line-height:1.5;">
                            {{ $r->permissions->count() }} permission(s) granted
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            @if(!$editUser)
            <div class="krd-card" style="padding:20px;background:#FFFBEB;border-color:#FDE68A;">
                <div style="font-size:13px;font-weight:600;color:#92400E;margin-bottom:8px;">⚠️ Important</div>
                <p style="font-size:12px;color:#92400E;line-height:1.6;">
                    The temporary password is shown only once. Make sure to share it securely with the staff member.
                    They will be prompted to change it on first login.
                </p>
            </div>
            @endif
        </div>

    </div>

    {{-- Individual Permission Overrides — only for existing staff, only
         visible to whoever holds staff.roles.manage, hidden for the
         account owner (who always has full access already). --}}
    @if($editUser && $canManageOverrides && !$editedUserRoleIsSystem)
    <div class="krd-card" style="padding:20px;margin-top:16px;" x-data="userOverridesUI()" x-init="init()">
        <div style="font-size:13px;font-weight:600;color:#1C1917;margin-bottom:4px;">Individual Permission Overrides</div>
        <p style="font-size:11px;color:#A8A29E;margin-bottom:16px;line-height:1.5;">
            Grant or revoke specific permissions for {{ $editUser->name }} on top of what their <strong>{{ $selectedRoleLabel }}</strong> role normally allows. Permissions marked <span style="color:#F59E0B;font-weight:600;">override</span> differ from the role's default for this person only.
        </p>

        <div style="display:flex;flex-direction:column;gap:16px;">
            @foreach($groupedPermissions as $groupName => $perms)
            <div>
                <div style="font-size:11px;font-weight:700;color:#78716C;text-transform:uppercase;letter-spacing:0.03em;margin-bottom:8px;">{{ $groupName }}</div>
                <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(220px, 1fr));gap:6px;">
                    @foreach($perms as $perm)
                    <label style="display:flex;align-items:center;gap:8px;padding:6px 8px;border-radius:5px;font-size:12px;color:#1C1917;cursor:pointer;">
                        <input type="checkbox"
                            :checked="effective('{{ $perm->name }}')"
                            x-on:click="toggle('{{ $perm->name }}')"
                            style="width:14px;height:14px;accent-color:#7C3AED;" />
                        {{ $perm->name }}
                        <span x-show="isOverride('{{ $perm->name }}')" x-cloak style="font-size:9px;color:#F59E0B;font-weight:600;">override</span>
                    </label>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <script type="application/json" id="user-overrides-data">
        {!! $overridesDataJson !!}
    </script>

    <script>
        function userOverridesUI() {
            return {
                rolePermissions: [],
                overrides: {},

                init() {
                    const raw = document.getElementById('user-overrides-data').textContent;
                    const data = JSON.parse(raw);
                    this.rolePermissions = data.rolePermissions || [];
                    this.overrides = data.overrides || {};
                },

                roleDefault(name) {
                    return this.rolePermissions.includes(name);
                },

                effective(name) {
                    return this.overrides.hasOwnProperty(name) ? this.overrides[name] : this.roleDefault(name);
                },

                isOverride(name) {
                    return this.effective(name) !== this.roleDefault(name);
                },

                toggle(name) {
                    const newVal = !this.effective(name);
                    this.overrides[name] = newVal; // optimistic local shadow for instant checkbox flip
                    $wire.toggleOverride(name);
                },
            };
        }
    </script>
    @endif

</div>

<style>
@media (min-width: 768px) {
    #invite-grid { grid-template-columns: 1fr 320px !important; }
}
</style>