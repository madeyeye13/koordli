<div>
    <div style="margin-bottom:24px;">
        <h2 class="krd-heading-3" style="color:#1C1917;">Moodboards — {{ $event->name }}</h2>
        <p style="font-size:12px;color:#78716C;margin-top:4px;">Creative direction shared for your event.</p>
    </div>

    @if($moodboards->isEmpty())
        <div class="krd-card" style="padding:40px;text-align:center;color:#A8A29E;">Nothing has been shared with you yet.</div>
    @else
    <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(240px, 1fr));gap:14px;">
        @foreach($moodboards as $board)
        <a href="{{ route('client.moodboards.show', $board->id) }}" wire:navigate class="krd-card" style="padding:16px;text-decoration:none;display:block;">
            <div style="width:100%;height:100px;background:#F5F5F4;border-radius:6px;margin-bottom:10px;display:flex;align-items:center;justify-content:center;overflow:hidden;">
                @if($board->coverDocument)
                    <img src="{{ \Illuminate\Support\Facades\Storage::disk($board->coverDocument->disk)->url($board->coverDocument->path) }}" style="width:100%;height:100%;object-fit:cover;">
                @else
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="none" viewBox="0 0 24 24" stroke="#D6D3D1" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg>
                @endif
            </div>
            <div style="font-size:13px;font-weight:600;color:#1C1917;margin-bottom:6px;">{{ $board->title }}</div>
            <span class="krd-badge" style="font-size:9px;background:{{ $board->statusColor() }}22;color:{{ $board->statusColor() }};">{{ $board->statusLabel() }}</span>
        </a>
        @endforeach
    </div>
    @endif
</div>