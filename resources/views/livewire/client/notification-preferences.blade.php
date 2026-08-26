<div x-data="{
    email: {{ Js::from($emailEnabled) }},
    toggle(key) {
        this.email[key] = !this.email[key];
        $wire.toggle(key);
    }
}">
    <div style="margin-bottom:24px;">
        <h2 class="krd-heading-3" style="color:#1C1917;">Notification Preferences</h2>
        <p style="font-size:12px;color:#78716C;margin-top:4px;">
            Choose which notifications you'd also like emailed to you. You'll always see relevant updates here in your dashboard, regardless of these settings.
        </p>
    </div>

    <div class="krd-card" style="padding:8px;">
        @foreach($categories as $key => $meta)
        <div style="display:flex;align-items:center;justify-content:space-between;gap:16px;padding:16px;{{ !$loop->last ? 'border-bottom:1px solid #E7E5E4;' : '' }}">
            <div>
                <div style="font-size:13px;font-weight:600;color:#1C1917;">{{ $meta['label'] }}</div>
                <div style="font-size:12px;color:#78716C;margin-top:2px;">{{ $meta['desc'] }}</div>
            </div>
            <button type="button" x-on:click="toggle('{{ $key }}')"
                x-bind:style="email['{{ $key }}']
                    ? 'width:44px;height:24px;border-radius:12px;border:none;cursor:pointer;flex-shrink:0;position:relative;background:#7C3AED;transition:background 200ms ease;'
                    : 'width:44px;height:24px;border-radius:12px;border:none;cursor:pointer;flex-shrink:0;position:relative;background:#D6D3D1;transition:background 200ms ease;'">
                <div x-bind:style="email['{{ $key }}']
                    ? 'position:absolute;top:3px;right:3px;width:18px;height:18px;border-radius:50%;background:#fff;transition:all 200ms ease;'
                    : 'position:absolute;top:3px;left:3px;width:18px;height:18px;border-radius:50%;background:#fff;transition:all 200ms ease;'">
                </div>
            </button>
        </div>
        @endforeach
    </div>
</div>