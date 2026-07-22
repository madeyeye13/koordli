<div>
    <div style="margin-bottom:24px;">
        <div class="krd-label" style="margin-bottom:4px;">Settings</div>
        <h2 class="krd-heading-3" style="color:#1C1917;">Domain Settings</h2>
        <p style="font-size:13px;color:#78716C;margin-top:4px;">Control how your team and clients access your Koordli workspace.</p>
    </div>

    <div style="display:grid;grid-template-columns:1fr 320px;gap:24px;align-items:start;" id="domain-settings-grid">
        <div style="display:flex;flex-direction:column;gap:20px;">

            {{-- Subdomain --}}
            <div class="krd-card" style="padding:24px;">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px;flex-wrap:wrap;gap:8px;">
                    <div class="krd-label" style="margin-bottom:0;">Platform Subdomain</div>
                    @if(!$canSubdomain)
                    <span class="krd-badge krd-badge-stone">Not on your plan</span>
                    @endif
                </div>
                <p style="font-size:12px;color:#A8A29E;margin-bottom:16px;">Your workspace is always reachable here.</p>

                <div class="krd-input-group" style="margin-bottom:0;">
                    <div style="display:flex;align-items:center;gap:0;">
                        <input wire:model="subdomain" type="text" class="krd-input"
                            style="border-radius:6px 0 0 6px;" placeholder="yourcompany"
                            {{ !$canSubdomain ? 'disabled' : '' }} />
                        <div style="background:#F5F5F4;border:1px solid #E7E5E4;border-left:none;border-radius:0 6px 6px 0;padding:10px 14px;font-size:13px;color:#78716C;white-space:nowrap;">
                            .{{ $appHost }}
                        </div>
                    </div>
                    @error('subdomain') <span class="krd-input-error-msg">{{ $message }}</span> @enderror
                </div>

                @if($canSubdomain)
                <button wire:click="saveSubdomain" wire:loading.attr="disabled" class="krd-btn krd-btn-primary krd-btn-sm" style="margin-top:14px;">
                    <span wire:loading.remove wire:target="saveSubdomain">Save Subdomain</span>
                    <span wire:loading wire:target="saveSubdomain">Saving...</span>
                </button>
                @endif
            </div>

            {{-- Custom Domain --}}
            <div class="krd-card" style="padding:24px;">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px;flex-wrap:wrap;gap:8px;">
                    <div class="krd-label" style="margin-bottom:0;">Custom Domain</div>
                    @if(!$canCustomDomain)
                    <span class="krd-badge krd-badge-stone">Not on your plan</span>
                    @else
                        @if($tenant->custom_domain)
                            @php
                                $statusColor = match($tenant->domain_status) {
                                    'verified' => '#10B981',
                                    'pending'  => '#F59E0B',
                                    'failed'   => '#EF4444',
                                    default    => '#A8A29E',
                                };
                            @endphp
                            <span class="krd-badge" style="background:{{ $statusColor }}1a;color:{{ $statusColor }};">
                                {{ ucfirst($tenant->domain_status) }}
                            </span>
                        @endif
                    @endif
                </div>
                <p style="font-size:12px;color:#A8A29E;margin-bottom:16px;">Use your own domain, e.g. app.yourcompany.com</p>

                @if($canCustomDomain)
                <div class="krd-input-group">
                    <input wire:model="custom_domain" type="text" class="krd-input" placeholder="app.yourcompany.com" />
                    @error('custom_domain') <span class="krd-input-error-msg">{{ $message }}</span> @enderror
                </div>

                <div style="display:flex;gap:10px;flex-wrap:wrap;">
                    <button wire:click="saveCustomDomain" wire:loading.attr="disabled" class="krd-btn krd-btn-primary krd-btn-sm">
                        <span wire:loading.remove wire:target="saveCustomDomain">{{ $tenant->custom_domain ? 'Update Domain' : 'Add Domain' }}</span>
                        <span wire:loading wire:target="saveCustomDomain">Saving...</span>
                    </button>
                    @if($tenant->custom_domain)
                    <button wire:click="verifyNow" wire:loading.attr="disabled" class="krd-btn krd-btn-secondary krd-btn-sm">
                        <span wire:loading.remove wire:target="verifyNow">🔄 Verify Now</span>
                        <span wire:loading wire:target="verifyNow">Checking...</span>
                    </button>
                    <button wire:click="removeCustomDomain" class="krd-btn krd-btn-sm" style="background:#FEE2E2;color:#DC2626;border-color:#FECACA;">Remove</button>
                    @endif
                </div>

                @if($tenant->custom_domain)
                <div style="margin-top:20px;background:#F5F5F4;border-radius:8px;padding:16px 20px;">
                    <div style="font-size:12px;font-weight:600;color:#1C1917;margin-bottom:12px;">DNS Records Required</div>

                    <div style="margin-bottom:14px;">
                        <div style="font-size:11px;color:#78716C;margin-bottom:4px;">1. CNAME Record</div>
                        <div style="display:grid;grid-template-columns:60px 1fr;gap:8px;font-size:11px;font-family:monospace;background:#fff;border:1px solid #E7E5E4;border-radius:6px;padding:10px 12px;">
                            <span style="color:#A8A29E;">Host:</span><span style="color:#1C1917;">{{ $tenant->custom_domain }}</span>
                            <span style="color:#A8A29E;">Value:</span><span style="color:#1C1917;">{{ $appHost }}</span>
                        </div>
                    </div>

                    <div>
                        <div style="font-size:11px;color:#78716C;margin-bottom:4px;">2. TXT Record (ownership verification)</div>
                        <div style="display:grid;grid-template-columns:60px 1fr;gap:8px;font-size:11px;font-family:monospace;background:#fff;border:1px solid #E7E5E4;border-radius:6px;padding:10px 12px;">
                            <span style="color:#A8A29E;">Host:</span><span style="color:#1C1917;">_koordli-verify.{{ $tenant->custom_domain }}</span>
                            <span style="color:#A8A29E;">Value:</span><span style="color:#1C1917;word-break:break-all;">{{ $tenant->domain_verification_token }}</span>
                        </div>
                    </div>

                    @if($tenant->domain_last_checked_at)
                    <div style="font-size:11px;color:#A8A29E;margin-top:12px;">Last checked: {{ $tenant->domain_last_checked_at->diffForHumans() }}</div>
                    @endif
                    @if($tenant->domain_verified_at)
                    <div style="font-size:11px;color:#10B981;margin-top:4px;">✓ Verified since {{ $tenant->domain_verified_at->format('d M Y') }}</div>
                    @endif
                </div>
                @endif
                @else
                <a href="{{ route('tenant.billing.upgrade') }}" wire:navigate class="krd-btn krd-btn-secondary krd-btn-sm">Upgrade to unlock →</a>
                @endif
            </div>

            {{-- White Label teaser --}}
            <div class="krd-card" style="padding:24px;">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px;flex-wrap:wrap;gap:8px;">
                    <div class="krd-label" style="margin-bottom:0;">White Label Branding</div>
                    <span class="krd-badge {{ $canWhiteLabel ? 'krd-badge-green' : 'krd-badge-stone' }}">
                        {{ $canWhiteLabel ? 'Enabled' : 'Not on your plan' }}
                    </span>
                </div>
                <p style="font-size:12px;color:#A8A29E;">
                    @if($canWhiteLabel)
                    Your logo and brand colors replace Koordli branding across your login page, client portal, RSVP pages, booking forms, and emails.
                    @else
                    Remove all Koordli branding across your login page, client portal, RSVP pages, booking forms, and emails.
                    @endif
                </p>
            </div>
        </div>

        {{-- Right sidebar --}}
        <div style="display:flex;flex-direction:column;gap:12px;position:sticky;top:80px;" id="domain-settings-tips">

            <div class="krd-card" style="padding:20px;">
                <div style="font-size:13px;font-weight:600;color:#1C1917;margin-bottom:12px;display:flex;align-items:center;gap:8px;">
                    <span>💡</span> How domains work
                </div>
                <div style="display:flex;flex-direction:column;gap:10px;">
                    @foreach([
                        'Your subdomain is always active and never requires DNS setup — it works instantly.',
                        'A custom domain requires adding two DNS records at your domain registrar.',
                        'Verification checks both a CNAME record and a TXT ownership record.',
                        'Domains are automatically re-checked daily in case DNS changes or breaks.',
                        'White label removes Koordli branding once your plan includes it.',
                    ] as $tip)
                    <div style="display:flex;gap:8px;align-items:flex-start;">
                        <div style="width:5px;height:5px;border-radius:50%;background:#7C3AED;flex-shrink:0;margin-top:6px;"></div>
                        <p style="font-size:12px;color:#78716C;line-height:1.6;">{{ $tip }}</p>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="krd-card" style="padding:20px;background:#F5F3FF;border-color:#DDD6FE;">
                <div style="font-size:13px;font-weight:600;color:#7C3AED;margin-bottom:8px;">Status Guide</div>
                <div style="display:flex;flex-direction:column;gap:8px;">
                    <div style="display:flex;align-items:center;gap:8px;">
                        <span style="background:#D1FAE5;color:#059669;font-size:10px;font-weight:600;padding:2px 8px;border-radius:4px;">Verified</span>
                        <span style="font-size:12px;color:#78716C;">DNS is correctly configured</span>
                    </div>
                    <div style="display:flex;align-items:center;gap:8px;">
                        <span style="background:#FEF3C7;color:#D97706;font-size:10px;font-weight:600;padding:2px 8px;border-radius:4px;">Pending</span>
                        <span style="font-size:12px;color:#78716C;">Waiting for DNS to propagate</span>
                    </div>
                    <div style="display:flex;align-items:center;gap:8px;">
                        <span style="background:#FEE2E2;color:#DC2626;font-size:10px;font-weight:600;padding:2px 8px;border-radius:4px;">Failed</span>
                        <span style="font-size:12px;color:#78716C;">DNS records missing or incorrect</span>
                    </div>
                </div>
            </div>

            @if($tenant->domain_status === 'pending' || $tenant->domain_status === 'failed')
            <div class="krd-card" style="padding:20px;background:#FFFBEB;border-color:#FDE68A;">
                <div style="font-size:13px;font-weight:600;color:#92400E;margin-bottom:8px;">⏳ DNS Propagation</div>
                <p style="font-size:12px;color:#78716C;line-height:1.6;">
                    DNS changes can take anywhere from a few minutes to 48 hours to fully propagate. If verification fails right after adding records, wait a bit and try again.
                </p>
            </div>
            @endif
        </div>
    </div>
</div>

<style>
@media (max-width: 768px) {
    #domain-settings-grid { grid-template-columns: 1fr !important; }
    #domain-settings-tips { position: static !important; }
}
</style>