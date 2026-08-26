<div>
    <div style="margin-bottom:24px;">
        <div class="krd-label" style="margin-bottom:4px;">Business</div>
        <h2 class="krd-heading-3" style="color:#1C1917;">Settings</h2>
    </div>

    <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(260px, 1fr));gap:14px;">
        @foreach($cards as $card)
        <a href="{{ route($card['route']) }}" wire:navigate class="krd-card" style="padding:20px;text-decoration:none;display:block;">
            <div style="font-size:14px;font-weight:600;color:#1C1917;margin-bottom:6px;">{{ $card['label'] }}</div>
            <div style="font-size:12px;color:#78716C;line-height:1.6;">{{ $card['desc'] }}</div>
        </a>
        @endforeach
    </div>
</div>