<div>
    <div style="margin-bottom:24px;">
        <div class="krd-label" style="margin-bottom:4px;">Platform</div>
        <h2 class="krd-heading-3">Comment Moderation</h2>
    </div>

    @forelse($comments as $c)
    <div class="krd-card" style="padding:18px;margin-bottom:12px;">
        <div style="font-size:12px;color:#78716C;margin-bottom:6px;">
            On: <strong>{{ $c->post->title }}</strong> · {{ $c->author_name }} ({{ $c->author_email }})
        </div>
        <p style="font-size:13.5px;margin-bottom:14px;">{{ $c->body }}</p>
        <div style="display:flex;gap:8px;">
            <button wire:click="approve({{ $c->id }})" class="krd-btn krd-btn-sm" style="background:#D1FAE5;color:#059669;">Approve</button>
            <button wire:click="reject({{ $c->id }})" class="krd-btn krd-btn-sm" style="background:#FEE2E2;color:#DC2626;">Reject</button>
        </div>
    </div>
    @empty
    <div class="krd-empty-state"><div class="krd-empty-state-icon">💬</div><div class="krd-empty-state-title">No pending comments</div></div>
    @endforelse

    {{ $comments->links() }}
</div>