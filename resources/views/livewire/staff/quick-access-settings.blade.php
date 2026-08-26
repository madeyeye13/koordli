<div>
    <div style="margin-bottom:24px;">
        <div class="krd-label" style="margin-bottom:4px;">My Account</div>
        <h2 class="krd-heading-3" style="color:#1C1917;">My Quick Access Link</h2>
        <p style="font-size:12px;color:#78716C;margin-top:4px;">A permanent link you can use to update your tasks and runsheet items without logging in — handy when you're busy or on-site.</p>
    </div>

    <div class="krd-card" style="padding:24px;max-width:520px;">
        @if(!$link->is_active)
        <div style="background:#FEF2F2;border:1px solid #FECACA;border-radius:6px;padding:12px;margin-bottom:16px;font-size:12px;color:#DC2626;">
            Your link has been deactivated by your organization. Contact them to reactivate it.
        </div>
        @endif

        <div style="background:#F5F5F4;border-radius:8px;padding:12px;font-size:12.5px;color:#57534E;word-break:break-all;margin-bottom:12px;font-family:monospace;">
            {{ $url }}
        </div>

        <div style="display:flex;gap:8px;flex-wrap:wrap;">
            <button type="button" x-data x-on:click="navigator.clipboard.writeText('{{ $url }}'); $el.textContent = 'Copied!'; setTimeout(() => $el.textContent = 'Copy Link', 1500)" class="krd-btn krd-btn-secondary krd-btn-sm">Copy Link</button>
            <x-ui.confirm-button action="regenerate" message="This will immediately invalidate your current link. Continue?" label="Regenerate Link" class="krd-btn krd-btn-danger krd-btn-sm" />
            @if($link->pin_enabled)
            <x-ui.confirm-button action="resetPin" message="You'll need to set a new PIN next time you use your link. Continue?" label="Reset My PIN" class="krd-btn krd-btn-ghost krd-btn-sm" />
            @endif
        </div>

        <div style="margin-top:14px;font-size:11.5px;color:#A8A29E;">
            PIN required: <strong style="color:#78716C;">{{ $link->pin_enabled ? 'Yes (set by your organization)' : 'No' }}</strong>
        </div>

        <x-ui.dismissible-tip id="quick-access-staff" text="This link is yours alone — treat it like a password. Anyone with this link and your PIN (if required) can update your tasks. Regenerate it immediately if you think it's been shared or compromised." />
    </div>
</div>