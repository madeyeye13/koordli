<div>
    <div style="margin-bottom:24px;">
        <h1 style="font-size:22px;font-weight:600;color:#1C1917;">Notification Settings</h1>
        <p style="font-size:13px;color:#78716C;margin-top:4px;">Control how you're notified about conversation activity.</p>
    </div>

    <div class="krd-card">
        <div style="display:flex;align-items:center;justify-content:space-between;">
            <div>
                <div style="font-size:14px;font-weight:600;color:#1C1917;">Email me about unread messages</div>
                <div style="font-size:12px;color:#78716C;margin-top:2px;">Get an email reminder if you have unread conversation messages while away.</div>
            </div>
            <button wire:click="toggle" style="width:44px;height:24px;border-radius:12px;border:none;cursor:pointer;position:relative;background:{{ $emailEnabled ? '#7C3AED' : '#D6D3D1' }};transition:background 150ms;">
                <span style="position:absolute;top:2px;left:{{ $emailEnabled ? '22px' : '2px' }};width:20px;height:20px;border-radius:50%;background:#fff;transition:left 150ms;"></span>
            </button>
        </div>
    </div>
</div>