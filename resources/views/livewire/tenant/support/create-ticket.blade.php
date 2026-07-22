<div>
    <div style="margin-bottom:24px;">
        <div style="margin-bottom:8px;">
            <a href="{{ route('tenant.support.tickets') }}" wire:navigate style="color:#A8A29E;text-decoration:none;font-size:13px;">← Back to My Tickets</a>
        </div>
        <div class="krd-label" style="margin-bottom:4px;">Support</div>
        <h2 class="krd-heading-3" style="color:#1C1917;">Open a Ticket</h2>
    </div>

    <div style="max-width:600px;">
        <div class="krd-card" style="padding:24px;">
            <div class="krd-input-group">
                <label class="krd-label-text">Subject <span style="color:#EF4444;">*</span></label>
                <input wire:model="subject" type="text" class="krd-input @error('subject') krd-input-error @enderror" placeholder="Brief summary of your issue" />
                @error('subject') <span class="krd-input-error-msg">{{ $message }}</span> @enderror
            </div>

            <div class="krd-grid-2" style="gap:12px;">
                <div class="krd-input-group">
                    <label class="krd-label-text">Priority</label>
                    <x-ui.dropdown wire="priority" placeholder="Medium"
                        selected="{{ ucfirst($priority) }}">
                        <div class="krd-dropdown-option {{ $priority === 'low' ? 'selected' : '' }}" x-on:click="select('Low', 'low')">Low</div>
                        <div class="krd-dropdown-option {{ $priority === 'medium' ? 'selected' : '' }}" x-on:click="select('Medium', 'medium')">Medium</div>
                        <div class="krd-dropdown-option {{ $priority === 'high' ? 'selected' : '' }}" x-on:click="select('High', 'high')">High</div>
                        <div class="krd-dropdown-option {{ $priority === 'urgent' ? 'selected' : '' }}" x-on:click="select('Urgent', 'urgent')">Urgent</div>
                    </x-ui.dropdown>
                </div>
                <div class="krd-input-group">
                    <label class="krd-label-text">Category</label>
                    <x-ui.dropdown wire="category" placeholder="Technical Issue"
                        selected="{{ $categories[$category] ?? 'Technical Issue' }}">
                        @foreach($categories as $key => $label)
                        <div class="krd-dropdown-option {{ $category === $key ? 'selected' : '' }}" x-on:click="select(@js($label), '{{ $key }}')">{{ $label }}</div>
                        @endforeach
                    </x-ui.dropdown>
                </div>
            </div>

            <div class="krd-input-group">
                <label class="krd-label-text">Describe the issue <span style="color:#EF4444;">*</span></label>
                <textarea wire:model="description" class="krd-input @error('description') krd-input-error @enderror" rows="6" placeholder="Tell us what's happening, what you expected, and any steps to reproduce..."></textarea>
                @error('description') <span class="krd-input-error-msg">{{ $message }}</span> @enderror
            </div>

            <div class="krd-input-group" style="margin-bottom:0;">
                <label class="krd-label-text">Attachments <span style="color:#A8A29E;font-weight:400;">(optional, max 10MB each)</span></label>
                <input wire:model="attachments" type="file" multiple class="krd-input" style="padding:8px;" />
                @error('attachments.*') <span class="krd-input-error-msg">{{ $message }}</span> @enderror
                @if($attachments)
                <div style="margin-top:8px;display:flex;flex-wrap:wrap;gap:6px;">
                    @foreach($attachments as $file)
                    <span style="font-size:11px;background:#F5F5F4;padding:4px 10px;border-radius:6px;color:#57534E;">📎 {{ $file->getClientOriginalName() }}</span>
                    @endforeach
                </div>
                @endif
            </div>
        </div>

        <button wire:click="submit" wire:loading.attr="disabled" class="krd-btn krd-btn-primary" style="margin-top:16px;">
            <span wire:loading.remove wire:target="submit">Submit Ticket</span>
            <span wire:loading wire:target="submit">Submitting...</span>
        </button>
    </div>
</div>