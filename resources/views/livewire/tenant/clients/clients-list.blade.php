<div>
    <div style="margin-bottom:24px;">
        <div class="krd-label" style="margin-bottom:4px;">Business</div>
        <h2 class="krd-heading-3" style="color:#1C1917;">Clients</h2>
        <p style="font-size:12px;color:#78716C;margin-top:4px;">Every client tied to an event, and whether they've been invited to their portal.</p>
    </div>

    <div class="krd-card" style="overflow:hidden;">
        <table style="width:100%;border-collapse:collapse;">
            <thead>
                <tr style="border-bottom:1px solid #E7E5E4;">
                    <th style="text-align:left;padding:12px 16px;font-size:11px;color:#78716C;text-transform:uppercase;">Client</th>
                    <th style="text-align:left;padding:12px 16px;font-size:11px;color:#78716C;text-transform:uppercase;">Event</th>
                    <th style="text-align:left;padding:12px 16px;font-size:11px;color:#78716C;text-transform:uppercase;">Status</th>
                    <th style="text-align:right;padding:12px 16px;font-size:11px;color:#78716C;text-transform:uppercase;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $row)
                <tr style="border-bottom:1px solid #F5F5F4;">
                    <td style="padding:12px 16px;">
                        <div style="font-size:13px;font-weight:600;color:#1C1917;">{{ $row['client_name'] }}</div>
                        <div style="font-size:11px;color:#A8A29E;">{{ $row['client_email'] }}</div>
                    </td>
                    <td style="padding:12px 16px;">
                        <a href="{{ route('tenant.events.show', $row['event_slug']) }}" wire:navigate style="font-size:12.5px;color:#7C3AED;text-decoration:none;">{{ $row['event_name'] }}</a>
                    </td>
                    <td style="padding:12px 16px;">
                        @if($row['invited'])
                        <span class="krd-badge" style="font-size:10px;background:#10B98122;color:#10B981;">Invited</span>
                        @else
                        <span class="krd-badge" style="font-size:10px;background:#F59E0B22;color:#F59E0B;">Not Invited</span>
                        @endif
                    </td>
                    <td style="padding:12px 16px;text-align:right;">
                        <div style="display:flex;gap:8px;justify-content:flex-end;align-items:center;">
                            @if(!$row['invited'])
                            <button wire:click="invite({{ $row['event_id'] }})" wire:loading.attr="disabled" wire:target="invite({{ $row['event_id'] }})" class="krd-btn krd-btn-primary krd-btn-sm">
                                <span wire:loading.remove wire:target="invite({{ $row['event_id'] }})">Invite</span>
                                <span wire:loading wire:target="invite({{ $row['event_id'] }})">Sending...</span>
                            </button>
                            @else
                            <div x-data="{ open: false }">
                                <button type="button" x-on:click="open = true" title="Revoke access" style="background:none;border:none;color:#DC2626;cursor:pointer;padding:4px;display:flex;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6M14 11v6M9 6V4h6v2"/>
                                    </svg>
                                </button>
                                <template x-teleport="body">
                                <div x-show="open" x-cloak style="position:fixed;inset:0;z-index:70;">
                                    <div style="position:absolute;inset:0;background:rgba(0,0,0,0.4);"></div>
                                    <div style="position:relative;height:100%;display:flex;align-items:center;justify-content:center;padding:16px;">
                                        <div style="background:#fff;border-radius:8px;padding:24px;max-width:380px;width:100%;">
                                            <p style="font-size:13px;color:#1C1917;margin-bottom:20px;line-height:1.6;">Revoke this client's access to this event? Their login stays active for any other events they have access to.</p>
                                            <div style="display:flex;gap:10px;">
                                                <button wire:click="revokeAccess({{ $row['access_id'] }})" x-on:click="open = false" class="krd-btn krd-btn-danger" style="flex:1;">Revoke</button>
                                                <button type="button" x-on:click="open = false" class="krd-btn krd-btn-secondary" style="flex:1;">Cancel</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                </template>
                            </div>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" style="padding:32px;text-align:center;color:#A8A29E;font-size:13px;">No clients yet — clients appear here once an event has a client name and email set.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>