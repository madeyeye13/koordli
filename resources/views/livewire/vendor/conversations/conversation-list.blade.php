<div>
    <div style="margin-bottom:24px;">
        <h1 style="font-size:22px;font-weight:600;color:#1C1917;letter-spacing:-0.01em;">Messages</h1>
        <p style="font-size:13px;color:#78716C;margin-top:4px;">Conversations with your event contacts.</p>
    </div>

    @if($conversations->isEmpty())
    <div class="krd-card">
        <div class="krd-empty-state">
            <div class="krd-empty-state-icon">💬</div>
            <div class="krd-empty-state-title">No conversations yet</div>
            <div class="krd-empty-state-desc">You'll see conversations here once you're added to one for an assigned event.</div>
        </div>
    </div>
    @else
    <div style="display:flex;flex-direction:column;gap:2px;">
        @foreach($conversations as $conv)
        <a href="{{ route('vendor.conversations.show', $conv->uuid) }}" wire:navigate class="krd-card" style="display:flex;align-items:center;justify-content:space-between;gap:12px;text-decoration:none;margin-bottom:10px;">
            <div style="flex:1;min-width:0;">
                <div style="display:flex;align-items:center;gap:8px;">
                    <span style="font-size:14px;font-weight:600;color:#1C1917;">{{ $conv->name ?: 'Direct Message' }}</span>
                    @if($conv->unread_count > 0)
                    <span style="background:#7C3AED;color:#fff;font-size:10px;font-weight:700;padding:2px 7px;border-radius:10px;">{{ $conv->unread_count }}</span>
                    @endif
                </div>
                <div style="font-size:11px;color:#A8A29E;margin-top:2px;">{{ $conv->event->name ?? '' }}</div>
                <div style="font-size:12px;color:#78716C;margin-top:4px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $conv->preview }}</div>
            </div>
            <span style="color:#A8A29E;">→</span>
        </a>
        @endforeach
    </div>
    @endif
</div>