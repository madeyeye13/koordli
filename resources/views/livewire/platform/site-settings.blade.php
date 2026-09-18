<div>
    <div style="margin-bottom:24px;">
        <div class="krd-label" style="margin-bottom:4px;">Platform Management</div>
        <h2 class="krd-heading-3" style="color:#1C1917;">Site Settings</h2>
    </div>

    <div style="max-width:1100px;display:grid;grid-template-columns:minmax(0,1fr) 280px;gap:24px;align-items:start;">
        <div class="krd-card" style="padding:24px;">
            <div class="krd-label" style="margin-bottom:16px;">Public Landing Page</div>

            <div class="krd-input-group">
                <label class="krd-label-text">Site Name</label>
                <input wire:model="site_name" type="text" class="krd-input" />
                @error('site_name') <span class="krd-input-error-msg">{{ $message }}</span> @enderror
            </div>

            <div class="krd-input-group">
                <label class="krd-label-text">Tagline</label>
                <input wire:model="site_tagline" type="text" class="krd-input" />
            </div>

            <div class="krd-input-group" style="margin-bottom:0;">
                <label class="krd-label-text">Favicon</label>
                @if($favicon_path)
                <div style="margin-bottom:8px;">
                    <img src="{{ Storage::url($favicon_path) }}" style="width:32px;height:32px;border-radius:4px;" />
                </div>
                @endif
                <input wire:model="favicon" type="file" accept="image/*" class="krd-input" style="padding:8px;" />
                <span class="krd-input-hint">Square image, ideally 32×32 or 64×64px.</span>
                @error('favicon') <span class="krd-input-error-msg">{{ $message }}</span> @enderror
            </div>

            <div class="krd-input-group" style="margin-top:24px;">
                <label class="krd-label-text">Tenant Quick Tour Video</label>

                @if($tenant_tour_video_path)
                    <div style="margin-bottom:10px;">
                        <video controls preload="metadata" src="{{ Storage::url($tenant_tour_video_path) }}" style="width:100%;max-width:480px;border-radius:10px;border:1px solid #E7E5E4;background:#0C0A09;display:block;"></video>
                    </div>
                    <button type="button" wire:click="removeTourVideo" class="krd-btn krd-btn-secondary krd-btn-sm" style="margin-bottom:8px;">Remove video</button>
                @endif

                <input wire:model="tenant_tour_video" type="file" accept="video/*" class="krd-input" style="padding:8px;" />
                <span class="krd-input-hint">Upload a short overview video. It shows only when the toggle below is enabled.</span>
                @error('tenant_tour_video') <span class="krd-input-error-msg">{{ $message }}</span> @enderror
            </div>

            <div class="krd-input-group" style="margin-top:12px;">
                <label class="krd-label-text">Enable tenant quick tour</label>
                <label style="display:flex;align-items:center;gap:10px;font-size:14px;color:#1C1917;cursor:pointer;">
                    <input type="checkbox" wire:model="tenant_tour_enabled" />
                    <span>Show the quick tour video for tenants</span>
                </label>
            </div>

            <div id="legal-pages" style="border-top:1px solid #E7E5E4;margin-top:24px;padding-top:24px;">
                <div class="krd-label" style="margin-bottom:6px;">Legal Pages</div>
                <p style="font-size:12px;color:#78716C;line-height:1.6;margin-bottom:16px;">These pages are linked from registration and are visible publicly. You can edit or expand the text at any time.</p>

                <div class="krd-input-group">
                    <label class="krd-label-text">Terms &amp; Conditions</label>
                    <textarea wire:model="terms_content" class="krd-input" rows="18" style="resize:vertical;font-family:inherit;line-height:1.6;"></textarea>
                    @error('terms_content') <span class="krd-input-error-msg">{{ $message }}</span> @enderror
                </div>

                <div class="krd-input-group" style="margin-bottom:0;">
                    <label class="krd-label-text">Privacy Policy</label>
                    <textarea wire:model="privacy_content" class="krd-input" rows="18" style="resize:vertical;font-family:inherit;line-height:1.6;"></textarea>
                    @error('privacy_content') <span class="krd-input-error-msg">{{ $message }}</span> @enderror
                </div>
            </div>

            <button wire:click="save" wire:loading.attr="disabled" class="krd-btn krd-btn-primary" style="margin-top:16px;">
                <span wire:loading.remove wire:target="save">Save Settings</span>
                <span wire:loading wire:target="save">Saving...</span>
            </button>
        </div>

        <aside style="display:flex;flex-direction:column;gap:14px;">
            <div class="krd-card" style="padding:20px;">
                <div class="krd-label" style="margin-bottom:8px;">Public Pages</div>
                <p style="font-size:12px;color:#78716C;line-height:1.6;margin-bottom:14px;">Preview the pages visitors see before registering.</p>
                <div style="display:flex;flex-direction:column;gap:8px;">
                    <a href="{{ route('terms') }}" target="_blank" rel="noopener" class="krd-btn krd-btn-secondary krd-btn-sm" style="justify-content:space-between;text-decoration:none;">
                        Terms &amp; Conditions <span>↗</span>
                    </a>
                    <a href="{{ route('privacy') }}" target="_blank" rel="noopener" class="krd-btn krd-btn-secondary krd-btn-sm" style="justify-content:space-between;text-decoration:none;">
                        Privacy Policy <span>↗</span>
                    </a>
                </div>
            </div>

            <div class="krd-card" style="padding:20px;background:#FAFAF9;">
                <div style="font-size:13px;font-weight:600;color:#1C1917;margin-bottom:10px;">Editing Tips</div>
                <div style="display:flex;flex-direction:column;gap:10px;">
                    <p style="font-size:12px;color:#78716C;line-height:1.6;margin:0;">Use a blank line between sections to keep the public page easy to read.</p>
                    <p style="font-size:12px;color:#78716C;line-height:1.6;margin:0;">The registration links always show the latest saved version.</p>
                    <p style="font-size:12px;color:#78716C;line-height:1.6;margin:0;">Review these documents with your legal adviser before publishing.</p>
                </div>
            </div>
        </aside>
    </div>
</div>

<style>
@media (max-width: 900px) {
    #legal-pages { scroll-margin-top: 24px; }
}
@media (max-width: 768px) {
    [style*="grid-template-columns:minmax(0,1fr) 280px"] { grid-template-columns: 1fr !important; }
}
</style>