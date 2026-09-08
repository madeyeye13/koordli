<div x-data="{ activeTab: '{{ $activeTab }}', showChapterForm: false }">
    <div style="margin-bottom:16px;">
        <a href="{{ route('tenant.events.rsvp', $event->slug) }}" wire:navigate style="color:#A8A29E;text-decoration:none;font-size:13px;">
            ← Back to RSVP
        </a>
    </div>
    <div style="margin-bottom:24px;">
        <div class="krd-label">Event Microsite</div>
        <h2 class="krd-heading-3">{{ $event->name }} — Wedding Website</h2>
    </div>

    <div style="display:flex;gap:8px;margin-bottom:24px;flex-wrap:wrap;border-bottom:1px solid #E7E5E4;">
        @foreach(['settings' => 'Settings', 'story' => 'Story', 'gallery' => 'Gallery', 'wishes' => 'Wishes', 'gifts' => 'Gifts'] as $tab => $label)
        <button type="button" x-on:click="activeTab = '{{ $tab }}'; $wire.setTab('{{ $tab }}')"
            x-bind:style="activeTab === '{{ $tab }}' ? 'padding:10px 16px;background:none;border:none;border-bottom:2px solid #7C3AED;color:#7C3AED;font-weight:600;font-size:13px;cursor:pointer;' : 'padding:10px 16px;background:none;border:none;border-bottom:2px solid transparent;color:#78716C;font-weight:600;font-size:13px;cursor:pointer;'">
            {{ $label }}
            @if($tab === 'wishes' && $wishes->where('status','pending')->count() > 0)
            <span class="krd-badge krd-badge-amber" style="margin-left:6px;">{{ $wishes->where('status','pending')->count() }}</span>
            @endif
        </button>
        @endforeach
    </div>

    {{-- SETTINGS TAB — always rendered, x-show toggles visibility, no more wire:ignore needed --}}
    <div x-show="activeTab === 'settings'">
        <div class="krd-card" style="padding:20px;" x-data="{
            story: @js($settings->story_enabled), gallery: @js($settings->gallery_enabled),
            wishes: @js($settings->wishes_enabled), gifts: @js($settings->gifts_enabled),
            dress: @js($settings->dress_code_enabled), countdown: @js($settings->countdown_enabled),
            approval: @js($settings->wishes_require_approval), gate: @js($settings->gate_venue_address)
        }">
            <p style="font-size:12.5px;color:#78716C;margin-bottom:20px;">Turn on whichever sections apply to this event. Guests only see what you enable here.</p>

            @foreach([
                ['field' => 'story', 'model' => 'story_enabled', 'label' => 'Our Story', 'desc' => 'A chaptered story, written by you and/or your client.'],
                ['field' => 'gallery', 'model' => 'gallery_enabled', 'label' => 'Gallery', 'desc' => 'Shared photos from you and your client.'],
                ['field' => 'wishes', 'model' => 'wishes_enabled', 'label' => 'Guest Wishes', 'desc' => 'A public message wall guests can write on.'],
                ['field' => 'gifts', 'model' => 'gifts_enabled', 'label' => 'Gift Information', 'desc' => 'Bank details and registry links for guests who want to gift.'],
                ['field' => 'dress', 'model' => 'dress_code_enabled', 'label' => 'Dress Code', 'desc' => 'A color palette for guests to dress in.'],
                ['field' => 'countdown', 'model' => 'countdown_enabled', 'label' => 'Countdown Timer', 'desc' => 'A live countdown to the event date.'],
            ] as $s)
            <div style="display:flex;justify-content:space-between;align-items:center;padding:14px 0;border-bottom:1px solid #F5F5F4;">
                <div>
                    <div style="font-size:13px;font-weight:600;">{{ $s['label'] }}</div>
                    <div style="font-size:11.5px;color:#78716C;">{{ $s['desc'] }}</div>
                </div>
                <button type="button" x-on:click="{{ $s['field'] }} = !{{ $s['field'] }}; $wire.toggleSection('{{ $s['model'] }}', {{ $s['field'] }})"
                    x-bind:style="{{ $s['field'] }} ? 'width:44px;height:24px;border-radius:12px;border:none;cursor:pointer;position:relative;background:#7C3AED;' : 'width:44px;height:24px;border-radius:12px;border:none;cursor:pointer;position:relative;background:#D6D3D1;'">
                    <div x-bind:style="{{ $s['field'] }} ? 'position:absolute;top:3px;right:3px;width:18px;height:18px;border-radius:50%;background:#fff;' : 'position:absolute;top:3px;left:3px;width:18px;height:18px;border-radius:50%;background:#fff;'"></div>
                </button>
            </div>
            @endforeach

            <div style="margin-top:20px;padding-top:20px;border-top:1px solid #E7E5E4;">
                <div style="display:flex;justify-content:space-between;align-items:center;padding:14px 0;">
                    <div>
                        <div style="font-size:13px;font-weight:600;">Wishes Need Approval</div>
                        <div style="font-size:11.5px;color:#78716C;">When on, either you or your client can approve a wish before it's public.</div>
                    </div>
                    <button type="button" x-on:click="approval = !approval; $wire.toggleSection('wishes_require_approval', approval)"
                        x-bind:style="approval ? 'width:44px;height:24px;border-radius:12px;border:none;cursor:pointer;position:relative;background:#7C3AED;' : 'width:44px;height:24px;border-radius:12px;border:none;cursor:pointer;position:relative;background:#D6D3D1;'">
                        <div x-bind:style="approval ? 'position:absolute;top:3px;right:3px;width:18px;height:18px;border-radius:50%;background:#fff;' : 'position:absolute;top:3px;left:3px;width:18px;height:18px;border-radius:50%;background:#fff;'"></div>
                    </button>
                </div>
                <div style="display:flex;justify-content:space-between;align-items:center;padding:14px 0;">
                    <div>
                        <div style="font-size:13px;font-weight:600;">Hide Venue Address Until Confirmed</div>
                        <div style="font-size:11.5px;color:#78716C;">Only guests who RSVP "attending" will see the real venue address.</div>
                    </div>
                    <button type="button" x-on:click="gate = !gate; $wire.toggleSection('gate_venue_address', gate)"
                        x-bind:style="gate ? 'width:44px;height:24px;border-radius:12px;border:none;cursor:pointer;position:relative;background:#7C3AED;' : 'width:44px;height:24px;border-radius:12px;border:none;cursor:pointer;position:relative;background:#D6D3D1;'">
                        <div x-bind:style="gate ? 'position:absolute;top:3px;right:3px;width:18px;height:18px;border-radius:50%;background:#fff;' : 'position:absolute;top:3px;left:3px;width:18px;height:18px;border-radius:50%;background:#fff;'"></div>
                    </button>
                </div>
            </div>

            <div style="margin-top:20px;padding-top:20px;border-top:1px solid #E7E5E4;">
                <div class="krd-label" style="margin-bottom:10px;">Dress Code Colors</div>
                <div style="display:flex;flex-wrap:wrap;gap:10px;margin-bottom:14px;">
                    @foreach($dressColors as $i => $c)
                    <div style="display:flex;align-items:center;gap:6px;background:#F5F5F4;padding:6px 10px;border-radius:8px;">
                        <span style="width:16px;height:16px;border-radius:50%;background:{{ $c['hex'] }};border:1px solid #E7E5E4;"></span>
                        <span style="font-size:12px;">{{ $c['name'] }}</span>
                        <button wire:click="removeDressColor({{ $i }})" style="background:none;border:none;color:#DC2626;cursor:pointer;font-size:11px;">✕</button>
                    </div>
                    @endforeach
                </div>
                <div style="display:flex;gap:8px;align-items:center;">
                    <input type="color" wire:model="newColorHex" style="width:40px;height:36px;border:1px solid #E7E5E4;border-radius:6px;">
                    <input wire:model="newColorName" type="text" class="krd-input" placeholder="Color name, e.g. Emerald" style="max-width:200px;">
                    <button wire:click="addDressColor" class="krd-btn krd-btn-secondary krd-btn-sm">+ Add</button>
                </div>
                <textarea wire:model="dressNote" class="krd-input" rows="2" placeholder="Optional note for guests..." style="margin-top:12px;"></textarea>
                <button wire:click="saveDressNote" class="krd-btn krd-btn-secondary krd-btn-sm" style="margin-top:8px;">Save Note</button>
            </div>
        </div>
    </div>

    {{-- STORY TAB --}}
    <div x-show="activeTab === 'story'">
        <button x-on:click="showChapterForm = true" wire:click="showAddChapter" class="krd-btn krd-btn-primary krd-btn-sm" style="margin-bottom:16px;">+ Add Chapter</button>
        @forelse($chapters as $chapter)
        <div class="krd-card" style="padding:18px;margin-bottom:12px;">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;">
                <div>
                    <div style="font-size:14px;font-weight:700;">{{ $chapter->title }}</div>
                    <div style="font-size:11px;color:#A8A29E;">Last edited by {{ ucfirst($chapter->last_edited_by_type ?? 'tenant') }}</div>
                </div>
                <div style="display:flex;gap:6px;">
                    <button x-on:click="showChapterForm = true" wire:click="editChapter({{ $chapter->id }})" class="krd-btn krd-btn-secondary krd-btn-sm">Edit</button>
                    <button wire:click="deleteChapter({{ $chapter->id }})" class="krd-btn krd-btn-sm" style="background:#FEE2E2;color:#DC2626;">Delete</button>
                </div>
            </div>
            <p style="font-size:13px;color:#57534E;margin-top:10px;">{{ \Illuminate\Support\Str::limit($chapter->content, 200) }}</p>
        </div>
        @empty
        <div class="krd-empty-state"><div class="krd-empty-state-icon">📖</div><div class="krd-empty-state-title">No chapters yet</div></div>
        @endforelse
    </div>

    {{-- GALLERY TAB --}}
    <div x-show="activeTab === 'gallery'">
        <div class="krd-card" style="padding:18px;margin-bottom:16px;">
            <input wire:model="newImages" type="file" multiple accept="image/*" class="krd-input" style="margin-bottom:10px;">
            <input wire:model="newCaption" type="text" class="krd-input" placeholder="Caption (optional)" style="margin-bottom:10px;">
            <button wire:click="uploadImages" wire:loading.attr="disabled" class="krd-btn krd-btn-primary krd-btn-sm">
                <span wire:loading.remove wire:target="uploadImages">Upload</span>
                <span wire:loading wire:target="uploadImages">Uploading...</span>
            </button>
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:12px;">
            @foreach($images as $img)
            <div style="position:relative;border-radius:8px;overflow:hidden;">
                <img src="{{ $img->url() }}" style="width:100%;height:140px;object-fit:cover;">
                <span class="krd-badge krd-badge-stone" style="position:absolute;top:6px;left:6px;font-size:9px;">{{ ucfirst($img->uploaded_by_type) }}</span>
                <button wire:click="deleteImage({{ $img->id }})" style="position:absolute;top:6px;right:6px;background:rgba(0,0,0,0.6);color:#fff;border:none;border-radius:50%;width:22px;height:22px;cursor:pointer;">✕</button>
            </div>
            @endforeach
        </div>
    </div>

    {{-- WISHES TAB --}}
    <div x-show="activeTab === 'wishes'">
        @forelse($wishes as $wish)
        <div class="krd-card" style="padding:16px;margin-bottom:10px;">
            <div style="display:flex;justify-content:space-between;">
                <div>
                    <span style="font-size:13px;font-weight:600;">{{ $wish->guest_name }}</span>
                    <span class="krd-badge {{ $wish->status === 'approved' ? 'krd-badge-green' : ($wish->status === 'rejected' ? 'krd-badge-red' : 'krd-badge-amber') }}" style="margin-left:8px;">{{ ucfirst($wish->status) }}</span>
                    @if($wish->approved_by_type)<span style="font-size:10.5px;color:#A8A29E;margin-left:6px;">by {{ ucfirst($wish->approved_by_type) }}</span>@endif
                </div>
            </div>
            <p style="font-size:13px;color:#57534E;margin-top:8px;">{{ $wish->message }}</p>
            <div style="display:flex;gap:6px;margin-top:10px;">
                @if($wish->status === 'pending')
                <button wire:click="approveWish({{ $wish->id }})" class="krd-btn krd-btn-sm" style="background:#D1FAE5;color:#059669;">Approve</button>
                <button wire:click="rejectWish({{ $wish->id }})" class="krd-btn krd-btn-sm" style="background:#FEE2E2;color:#DC2626;">Reject</button>
                @endif
                <button wire:click="deleteWish({{ $wish->id }})" class="krd-btn krd-btn-ghost krd-btn-sm">Delete</button>
            </div>
        </div>
        @empty
        <div class="krd-empty-state"><div class="krd-empty-state-icon">💌</div><div class="krd-empty-state-title">No wishes yet</div></div>
        @endforelse
    </div>

    {{-- GIFTS TAB --}}
    <div x-show="activeTab === 'gifts'">
        <div class="krd-card" style="padding:20px;margin-bottom:16px;">
            <div class="krd-label" style="margin-bottom:8px;">Note to Guests</div>
            <textarea wire:model="giftNote" class="krd-input" rows="3" placeholder="Your presence is the greatest gift..."></textarea>
            <button wire:click="saveGiftNote" class="krd-btn krd-btn-secondary krd-btn-sm" style="margin-top:8px;">Save</button>
        </div>

        <div class="krd-card" style="padding:20px;margin-bottom:16px;">
            <div class="krd-label" style="margin-bottom:10px;">Bank Accounts</div>
            @foreach($bankAccounts as $i => $acc)
            <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid #F5F5F4;">
                <span style="font-size:12.5px;"><strong>{{ $acc['currency'] ?? 'NGN' }}</strong> — {{ $acc['bank_name'] }} — {{ $acc['account_name'] }} — {{ $acc['account_number'] }}</span>
                <button wire:click="removeBankAccount({{ $i }})" style="background:none;border:none;color:#DC2626;cursor:pointer;">✕</button>
            </div>
            @endforeach
            <div style="display:flex;gap:6px;margin-top:12px;flex-wrap:wrap;align-items:center;">
                <select wire:model="newAccountCurrency" class="krd-input" style="max-width:110px;">
                    @foreach(['NGN','USD','GBP','EUR','CAD'] as $c)<option value="{{ $c }}">{{ $c }}</option>@endforeach
                </select>
                <input wire:model="newBankName" type="text" class="krd-input" placeholder="Bank name" style="max-width:150px;">
                <input wire:model="newAccountName" type="text" class="krd-input" placeholder="Account name" style="max-width:150px;">
                <input wire:model="newAccountNumber" type="text" class="krd-input" placeholder="Account number" style="max-width:150px;">
                <button wire:click="addBankAccount" class="krd-btn krd-btn-secondary krd-btn-sm">+ Add</button>
            </div>
        </div>

        <div class="krd-card" style="padding:20px;">
            <div class="krd-label" style="margin-bottom:10px;">Registry Links</div>
            @foreach($registryLinks as $i => $link)
            <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid #F5F5F4;">
                <span style="font-size:12.5px;">
                    @if(!empty($link['currencies']))<strong>{{ implode(', ', $link['currencies']) }}</strong> — @endif
                    {{ $link['label'] }} — {{ $link['url'] }}
                </span>
                <button wire:click="removeRegistryLink({{ $i }})" style="background:none;border:none;color:#DC2626;cursor:pointer;">✕</button>
            </div>
            @endforeach
            <div style="margin-top:12px;">
                <div style="display:flex;gap:8px;margin-bottom:8px;flex-wrap:wrap;">
                    @foreach(['NGN','USD','GBP','EUR','CAD'] as $c)
                    <label style="display:flex;align-items:center;gap:4px;font-size:11.5px;cursor:pointer;">
                        <input type="checkbox" wire:click="toggleLinkCurrency('{{ $c }}')" {{ in_array($c, $newLinkCurrencies) ? 'checked' : '' }} style="accent-color:#7C3AED;"> {{ $c }}
                    </label>
                    @endforeach
                </div>
                <div style="display:flex;gap:6px;flex-wrap:wrap;">
                    <input wire:model="newRegistryLabel" type="text" class="krd-input" placeholder="Label, e.g. Revolut.me" style="max-width:200px;">
                    <input wire:model="newRegistryUrl" type="text" class="krd-input" placeholder="URL" style="max-width:250px;">
                    <button wire:click="addRegistryLink" class="krd-btn krd-btn-secondary krd-btn-sm">+ Add</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Chapter Modal --}}
    <template x-teleport="body">
    <div x-show="showChapterForm" x-cloak style="position:fixed;inset:0;width:100vw;height:100vh;background:rgba(0,0,0,0.4);z-index:60;">
        <div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);background:#fff;border-radius:8px;padding:24px;max-width:500px;width:calc(100% - 32px);">
            <h3 style="font-size:15px;font-weight:600;margin-bottom:14px;">{{ $editChapterId ? 'Edit Chapter' : 'New Chapter' }}</h3>
            <input wire:model="chapterTitle" type="text" class="krd-input" placeholder="Chapter title, e.g. How It All Began" style="margin-bottom:10px;">
            <textarea wire:model="chapterContent" class="krd-input" rows="8" placeholder="Tell your story..."></textarea>
            <div style="display:flex;gap:10px;margin-top:14px;">
                <button wire:click="saveChapter" x-on:click="showChapterForm = false" class="krd-btn krd-btn-primary" style="flex:1;">Save</button>
                <button type="button" x-on:click="showChapterForm = false" class="krd-btn krd-btn-secondary" style="flex:1;">Cancel</button>
            </div>
        </div>
    </div>
    </template>
</div>