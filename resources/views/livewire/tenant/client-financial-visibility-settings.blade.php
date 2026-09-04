<div x-data="{
    settings: {{ Js::from($settings) }},
    toggle(key) {
        this.settings[key] = !this.settings[key];
        $wire.setVisibility(key, this.settings[key]);
    }
}">
    <div style="margin-bottom:24px;">
        <div class="krd-label" style="margin-bottom:4px;">Settings</div>
        <h2 class="krd-heading-3" style="color:#1C1917;">Client Financial Visibility</h2>
        <p style="font-size:12px;color:#78716C;margin-top:4px;">
            Control what financial information your clients can see. Sensitive details (vendor costs, your fee) are hidden by default.
        </p>
    </div>

    <div class="krd-card" style="padding:8px;">
        @foreach($fields as $key => $meta)
        <div style="display:flex;align-items:center;justify-content:space-between;gap:16px;padding:16px;{{ !$loop->last ? 'border-bottom:1px solid #E7E5E4;' : '' }}">
            <div>
                <div style="font-size:13px;font-weight:600;color:#1C1917;">{{ $meta['label'] }}</div>
                <div style="font-size:12px;color:#78716C;margin-top:2px;">{{ $meta['desc'] }}</div>
            </div>
            <button type="button" x-on:click="toggle('{{ $key }}')"
                x-bind:style="settings['{{ $key }}']
                    ? 'width:44px;height:24px;border-radius:12px;border:none;cursor:pointer;flex-shrink:0;position:relative;background:#7C3AED;'
                    : 'width:44px;height:24px;border-radius:12px;border:none;cursor:pointer;flex-shrink:0;position:relative;background:#D6D3D1;'">
                <div x-bind:style="settings['{{ $key }}']
                    ? 'position:absolute;top:3px;right:3px;width:18px;height:18px;border-radius:50%;background:#fff;'
                    : 'position:absolute;top:3px;left:3px;width:18px;height:18px;border-radius:50%;background:#fff;'">
                </div>
            </button>
        </div>
        @endforeach
    </div>
</div>