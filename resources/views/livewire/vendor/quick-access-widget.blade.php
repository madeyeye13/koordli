@if($enabled)
<div style="position:relative;" x-data="{ show: false, tipDismissed: !!localStorage.getItem('krd-tip-dismissed-quick-access-icon-vendor') }">
    <button x-on:click="show = !show"
        style="background:none;border:none;cursor:pointer;color:#57534E;padding:6px;display:flex;align-items:center;" title="Quick Access Link">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path d="M13 2L3 14h7l-1 8 10-12h-7l1-8z"/>
        </svg>
    </button>

    <div x-show="!tipDismissed" x-cloak
        style="position:absolute;top:36px;right:0;width:230px;background:#1C1917;color:#fff;border-radius:8px;padding:10px 12px;font-size:11px;line-height:1.5;z-index:61;box-shadow:0 8px 20px rgba(0,0,0,0.2);">
        <div style="position:absolute;top:-5px;right:14px;width:10px;height:10px;background:#1C1917;transform:rotate(45deg);"></div>
        <div style="display:flex;justify-content:space-between;gap:8px;">
            <span>This is your permanent Quick Access link — update tasks without logging in. Click to view or copy yours.</span>
            <button x-on:click="tipDismissed = true; localStorage.setItem('krd-tip-dismissed-quick-access-icon-vendor', '1')"
                style="background:none;border:none;color:#A8A29E;cursor:pointer;padding:0;flex-shrink:0;">
                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
    </div>

    <div x-show="show" x-cloak x-on:click.outside="show = false" style="position:absolute;top:36px;right:0;width:300px;background:#fff;border:1px solid #E7E5E4;border-radius:10px;box-shadow:0 12px 32px rgba(0,0,0,0.15);z-index:60;padding:16px;">
        <div style="font-size:13px;font-weight:600;color:#1C1917;margin-bottom:2px;">My Quick Access Link</div>
        <div style="font-size:11px;color:#78716C;margin-bottom:10px;">Update tasks/runsheet items without logging in.</div>

        <div style="background:#F5F5F4;border-radius:6px;padding:8px 10px;font-size:11px;color:#57534E;word-break:break-all;font-family:monospace;margin-bottom:10px;">
            {{ $url }}
        </div>

        <div style="display:flex;gap:6px;">
            <button type="button" x-on:click="navigator.clipboard.writeText('{{ $url }}'); $el.textContent = 'Copied!'; setTimeout(() => $el.textContent = 'Copy', 1500)" class="krd-btn krd-btn-primary krd-btn-sm" style="flex:1;">Copy</button>
            <button wire:click="regenerate" wire:loading.attr="disabled" wire:target="regenerate" wire:confirm="This invalidates your current link. Continue?" class="krd-btn krd-btn-ghost krd-btn-sm" style="flex:1;">
                <span wire:loading.remove wire:target="regenerate">Regenerate</span>
                <span wire:loading wire:target="regenerate">...</span>
            </button>
        </div>

        <div style="margin-top:10px;font-size:10.5px;color:#A8A29E;">
            PIN required: <strong style="color:#78716C;">{{ $link->pin_enabled ? 'Yes' : 'No' }}</strong>
        </div>

        <a href="{{ route('vendor.profile') }}" wire:navigate style="display:block;text-align:center;font-size:11px;color:#7C3AED;text-decoration:none;margin-top:10px;">Manage settings →</a>
    </div>
</div>
@endif