<div style="position:relative;" x-data="{ showPanel: false }" x-on:click.outside="showPanel = false">
    <button x-on:click="showPanel = !showPanel"
        style="position:relative;background:none;border:none;cursor:pointer;color:#57534E;padding:6px;display:flex;align-items:center;"
        x-on:notification-received.window="$wire.$refresh()">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path d="M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 00-4-5.7V5a2 2 0 10-4 0v.3A6 6 0 006 11v3.2a2 2 0 01-.6 1.4L4 17h5"/>
            <path d="M9 17v1a3 3 0 006 0v-1"/>
        </svg>
        @if($unreadCount > 0)
        <span style="position:absolute;top:0;right:0;background:#EF4444;color:#fff;font-size:10px;font-weight:700;width:16px;height:16px;border-radius:50%;display:flex;align-items:center;justify-content:center;">
            {{ $unreadCount > 9 ? '9+' : $unreadCount }}
        </span>
        @endif
    </button>
    <div x-show="showPanel" x-cloak style="position:absolute;top:36px;right:0;width:360px;max-height:480px;background:#fff;border:1px solid #E7E5E4;border-radius:10px;box-shadow:0 12px 32px rgba(0,0,0,0.15);z-index:60;overflow:hidden;display:flex;flex-direction:column;">
        <div style="padding:14px 16px;border-bottom:1px solid #E7E5E4;display:flex;align-items:center;justify-content:space-between;">
            <div style="font-size:14px;font-weight:600;color:#1C1917;">Notifications</div>
            @if($unreadCount > 0)
            <button wire:click="markAllRead" style="font-size:11px;color:#7C3AED;background:none;border:none;cursor:pointer;">Mark all read</button>
            @endif
        </div>
        <div style="overflow-y:auto;flex:1;">
            @forelse($notifications as $n)
            <a href="{{ $n->data['action_url'] ?? '#' }}" wire:navigate
                wire:click="markAsRead('{{ $n->id }}')"
                style="display:block;padding:12px 16px;border-bottom:1px solid #F5F5F4;text-decoration:none;{{ $n->read_at ? '' : 'background:#F5F3FF;' }}">
                <div style="display:flex;align-items:flex-start;gap:8px;">
                    <span style="width:6px;height:6px;border-radius:50%;background:{{ match($n->data['priority'] ?? 'normal') { 'critical' => '#EF4444', 'high' => '#F59E0B', 'low' => '#A8A29E', default=> '#3B82F6' } }};flex-shrink:0;margin-top:5px;"></span>
                    <div style="flex:1;min-width:0;">
                        <div style="font-size:12.5px;font-weight:600;color:#1C1917;">{{ $n->data['subject'] ?? '' }}</div>
                        <div style="font-size:11.5px;color:#78716C;margin-top:2px;line-height:1.5;">{{ \Illuminate\Support\Str::limit($n->data['body'] ?? '', 90) }}</div>
                        <div style="font-size:10px;color:#A8A29E;margin-top:4px;">{{ $n->created_at->diffForHumans() }}</div>
                    </div>
                </div>
            </a>
            @empty
            <div style="padding:32px 16px;text-align:center;font-size:12px;color:#A8A29E;">No notifications yet.</div>
            @endforelse
        </div>
        <div style="padding:10px 16px;border-top:1px solid #E7E5E4;text-align:center;">
            <a href="{{ route('client.notifications.preferences') }}" wire:navigate style="font-size:11px;color:#7C3AED;text-decoration:none;">Notification Settings</a>
        </div>
    </div>
</div>
<script>
document.addEventListener('alpine:init', () => {
    // ASSUMPTION: channel name pattern mirrors the tenant version
    // (notifications.tenant-user.{id}) — needs verifying against
    // KoordliNotification::broadcastOn()'s actual logic. If this channel
    // name is wrong, the live badge push simply won't fire; opening the
    // panel still works correctly regardless, since it always pulls fresh.
    window.Echo.private('notifications.client.{{ auth("client")->id() }}')
        .notification(() => {
            window.dispatchEvent(new CustomEvent('notification-received'));
        });
});
</script>