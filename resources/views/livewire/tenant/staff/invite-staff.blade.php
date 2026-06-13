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

    {{-- Responsive grid: stacks on mobile --}}
    <div style="display:grid;grid-template-columns:1fr;gap:16px;">

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
                <x-ui.dropdown wire="role" placeholder="Select role"
                    selected="{{ $role === 'company_owner' ? 'Company Owner' : ($role === 'manager' ? 'Manager' : 'Staff') }}">
                    <div class="krd-dropdown-option {{ $role === 'company_owner' ? 'selected' : '' }}"
                        x-on:click="select('Company Owner', 'company_owner')">
                        <span style="width:8px;height:8px;border-radius:50%;background:#7C3AED;flex-shrink:0;display:inline-block;"></span>
                        Company Owner
                    </div>
                    <div class="krd-dropdown-option {{ $role === 'manager' ? 'selected' : '' }}"
                        x-on:click="select('Manager', 'manager')">
                        <span style="width:8px;height:8px;border-radius:50%;background:#3B82F6;flex-shrink:0;display:inline-block;"></span>
                        Manager
                    </div>
                    <div class="krd-dropdown-option {{ $role === 'staff' ? 'selected' : '' }}"
                        x-on:click="select('Staff', 'staff')">
                        <span style="width:8px;height:8px;border-radius:50%;background:#A8A29E;flex-shrink:0;display:inline-block;"></span>
                        Staff
                    </div>
                </x-ui.dropdown>
                @error('role') <span class="krd-input-error-msg">{{ $message }}</span> @enderror
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
                <div style="font-size:13px;font-weight:600;color:#1C1917;margin-bottom:12px;">👥 Role Permissions</div>
                <div style="display:flex;flex-direction:column;gap:12px;">
                    @foreach([
                        ['role' => 'Company Owner', 'color' => '#7C3AED', 'desc' => 'Full access to everything. Can manage staff, billing, and settings.'],
                        ['role' => 'Manager',       'color' => '#3B82F6', 'desc' => 'Can manage events, tasks, vendors, and guests. Cannot manage billing or staff.'],
                        ['role' => 'Staff',         'color' => '#A8A29E', 'desc' => 'Can view and update tasks assigned to them. Limited access.'],
                    ] as $item)
                    <div style="padding:12px;border-radius:6px;border-left:3px solid {{ $item['color'] }};background:{{ $item['color'] }}11;">
                        <div style="font-size:12px;font-weight:600;color:{{ $item['color'] }};margin-bottom:3px;">{{ $item['role'] }}</div>
                        <div style="font-size:11px;color:#78716C;line-height:1.5;">{{ $item['desc'] }}</div>
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
</div>

<style>
@media (min-width: 768px) {
    #invite-grid { grid-template-columns: 1fr 320px !important; }
}
</style>