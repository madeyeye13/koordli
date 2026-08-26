<div x-data="qaAdminTable()" x-init="init()">
    <div style="margin-bottom:24px;">
        <div class="krd-label" style="margin-bottom:4px;">Settings</div>
        <h2 class="krd-heading-3" style="color:#1C1917;">Quick Access Links</h2>
        <p style="font-size:12px;color:#78716C;margin-top:4px;">
            Permanent, no-login links your staff and vendors can use to update tasks and runsheet items without signing in. Treat these like passwords — regenerate immediately if one is compromised.
        </p>
    </div>

    <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px;min-height:32px;">
        <template x-if="selected.length > 0">
            <div style="display:flex;align-items:center;gap:10px;">
                <span style="font-size:12px;color:#78716C;" x-text="selected.length + ' selected'"></span>
                <button x-on:click="$wire.bulkGenerate(selected); selected = []" class="krd-btn krd-btn-primary krd-btn-sm">Bulk Generate</button>
                <button x-on:click="confirmBulkDeactivate = true" class="krd-btn krd-btn-danger krd-btn-sm">Bulk Deactivate</button>
                <button x-on:click="selected = []" class="krd-btn krd-btn-ghost krd-btn-sm">Clear</button>
            </div>
        </template>
    </div>

    <div class="krd-card" style="overflow:hidden;">
        <table style="width:100%;border-collapse:collapse;">
            <thead>
                <tr style="border-bottom:1px solid #E7E5E4;">
                    <th style="padding:12px 12px 12px 16px;width:36px;">
                        <div x-on:click="toggleAll()" :style="allChecked ? checkedBoxStyle : uncheckedBoxStyle">
                            <svg x-show="allChecked" xmlns="http://www.w3.org/2000/svg" width="10" height="10" fill="none" viewBox="0 0 24 24" stroke="#fff" stroke-width="3"><path d="M20 6L9 17l-5-5"/></svg>
                        </div>
                    </th>
                    <th style="text-align:left;padding:12px 16px;font-size:11px;color:#78716C;text-transform:uppercase;">Person</th>
                    <th style="text-align:left;padding:12px 16px;font-size:11px;color:#78716C;text-transform:uppercase;">Status</th>
                    <th style="text-align:left;padding:12px 16px;font-size:11px;color:#78716C;text-transform:uppercase;">PIN Required</th>
                    <th style="text-align:left;padding:12px 16px;font-size:11px;color:#78716C;text-transform:uppercase;">Last Used</th>
                    <th style="text-align:right;padding:12px 16px;font-size:11px;color:#78716C;text-transform:uppercase;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rows as $row)
                @php $rowKey = $row['person_type'] . ':' . $row['person_id']; @endphp
                <tr style="border-bottom:1px solid #F5F5F4;">
                    <td style="padding:12px 12px 12px 16px;">
                        <div x-on:click="toggle(@js($rowKey))" :style="isSelected(@js($rowKey)) ? checkedBoxStyle : uncheckedBoxStyle">
                            <svg x-show="isSelected(@js($rowKey))" xmlns="http://www.w3.org/2000/svg" width="10" height="10" fill="none" viewBox="0 0 24 24" stroke="#fff" stroke-width="3"><path d="M20 6L9 17l-5-5"/></svg>
                        </div>
                    </td>
                    <td style="padding:12px 16px;">
                        <div style="font-size:13px;font-weight:600;color:#1C1917;">{{ $row['name'] }}</div>
                        <div style="font-size:11px;color:#A8A29E;">{{ $row['sub'] }} · {{ $row['role'] }}</div>
                    </td>
                    <td style="padding:12px 16px;">
                        @if(!$row['link'])
                        <span class="krd-badge" style="font-size:10px;background:#E7E5E4;color:#78716C;">No link yet</span>
                        @elseif($row['link']->is_active)
                        <span class="krd-badge" style="font-size:10px;background:#10B98122;color:#10B981;">Active</span>
                        @else
                        <span class="krd-badge" style="font-size:10px;background:#EF444422;color:#EF4444;">Deactivated</span>
                        @endif
                    </td>
                    <td style="padding:12px 16px;">
                        @if($row['link'])
                        <button wire:click="togglePin({{ $row['link']->id }})" wire:loading.attr="disabled" wire:target="togglePin({{ $row['link']->id }})"
                            style="background:none;border:none;cursor:pointer;font-size:11px;color:{{ $row['link']->pin_enabled ? '#7C3AED' : '#A8A29E' }};">
                            <span wire:loading.remove wire:target="togglePin({{ $row['link']->id }})">
                                {{ $row['link']->pin_enabled ? 'Required — click to disable' : 'Not required — click to enable' }}
                            </span>
                            <span wire:loading wire:target="togglePin({{ $row['link']->id }})">Saving...</span>
                        </button>
                        @else
                        <span style="font-size:11px;color:#D6D3D1;">—</span>
                        @endif
                    </td>
                    <td style="padding:12px 16px;font-size:12px;color:#78716C;">
                        {{ $row['link']?->last_used_at?->diffForHumans() ?? 'Never' }}
                    </td>
                    <td style="padding:12px 16px;text-align:right;">
                        <div style="display:flex;gap:6px;justify-content:flex-end;">
                            @if($row['link'])
                            <button wire:click="viewAudit({{ $row['link']->id }})" class="krd-btn krd-btn-ghost krd-btn-sm">Log</button>
                            @if($row['link']->is_active)
                            <x-ui.confirm-button action="deactivate({{ $row['link']->id }})" message="Deactivate this link? They won't be able to use it until reactivated." label="Deactivate" class="krd-btn krd-btn-ghost krd-btn-sm" style="color:#DC2626;" />
                            @else
                            <button wire:click="reactivate({{ $row['link']->id }})" class="krd-btn krd-btn-ghost krd-btn-sm">Reactivate</button>
                            @endif
                            @endif
                            <x-ui.confirm-button
                                action="regenerate('{{ $row['person_type'] }}', {{ $row['person_id'] }})"
                                message="{{ $row['link'] ? 'This invalidates their current link immediately.' : 'Generate a new quick access link for this person?' }}"
                                label="{{ $row['link'] ? 'Regenerate' : 'Generate Link' }}"
                                class="krd-btn krd-btn-secondary krd-btn-sm" />
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Bulk Deactivate Confirm --}}
    <template x-teleport="body">
    <div x-show="confirmBulkDeactivate" x-cloak style="position:fixed;inset:0;z-index:70;">
        <div style="position:absolute;inset:0;background:rgba(0,0,0,0.4);"></div>
        <div style="position:relative;height:100%;display:flex;align-items:center;justify-content:center;padding:16px;">
            <div style="background:#fff;border-radius:8px;padding:24px;max-width:380px;width:100%;">
                <p style="font-size:13px;color:#1C1917;margin-bottom:20px;">Deactivate <span x-text="selected.length"></span> link(s)?</p>
                <div style="display:flex;gap:10px;">
                    <button x-on:click="$wire.bulkDeactivate(selected); selected = []; confirmBulkDeactivate = false" class="krd-btn krd-btn-danger" style="flex:1;">Confirm</button>
                    <button x-on:click="confirmBulkDeactivate = false" class="krd-btn krd-btn-secondary" style="flex:1;">Cancel</button>
                </div>
            </div>
        </div>
    </div>
    </template>

    {{-- Audit Log Modal --}}
    <template x-teleport="body">
    <div x-show="$wire.showAuditFor" x-cloak style="position:fixed;inset:0;z-index:60;">
        <div style="position:absolute;inset:0;background:rgba(0,0,0,0.4);"></div>
        <div style="position:relative;height:100%;display:flex;align-items:center;justify-content:center;padding:16px;">
            <div style="background:#fff;border-radius:8px;padding:24px;max-width:480px;width:100%;max-height:70vh;display:flex;flex-direction:column;">
                <h3 style="font-size:15px;font-weight:600;margin-bottom:12px;">Activity Log</h3>
                <div style="overflow-y:auto;flex:1;">
                    @forelse($auditLog as $entry)
                    <div style="padding:10px 0;border-bottom:1px solid #F5F5F4;">
                        <div style="font-size:12.5px;color:#1C1917;">{{ $entry->description }}</div>
                        <div style="font-size:10.5px;color:#A8A29E;margin-top:2px;">{{ $entry->created_at->diffForHumans() }}</div>
                    </div>
                    @empty
                    <div style="text-align:center;color:#A8A29E;font-size:12px;padding:24px;">No actions logged yet.</div>
                    @endforelse
                </div>
                <button wire:click="$set('showAuditFor', false)" class="krd-btn krd-btn-ghost" style="width:100%;margin-top:12px;">Close</button>
            </div>
        </div>
    </div>
    </template>
</div>

<script type="application/json" id="qa-admin-rows">{!! $rowsJson !!}</script>
<script>
function qaAdminTable() {
    return {
        allRows: [],
        selected: [],
        confirmBulkDeactivate: false,
        checkedBoxStyle: 'width:16px;height:16px;border-radius:4px;border:2px solid #7C3AED;background:#7C3AED;cursor:pointer;display:flex;align-items:center;justify-content:center;',
        uncheckedBoxStyle: 'width:16px;height:16px;border-radius:4px;border:2px solid #D6D3D1;background:#fff;cursor:pointer;display:flex;align-items:center;justify-content:center;',

        init() {
            this.allRows = JSON.parse(document.getElementById('qa-admin-rows').textContent);
        },

        isSelected(key) { return this.selected.includes(key); },

        toggle(key) {
            this.selected = this.isSelected(key)
                ? this.selected.filter(k => k !== key)
                : [...this.selected, key];
        },

        get allChecked() {
            return this.allRows.length > 0 && this.selected.length === this.allRows.length;
        },

        toggleAll() {
            this.selected = this.allChecked ? [] : this.allRows.map(r => r.key);
        },
    };
}
</script>