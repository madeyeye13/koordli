<div x-data="{ tab: @js($activeTab), chapterModalOpen: false, deleteModalOpen: false, currencyOpen: false }" style="max-width:1100px;margin:0 auto;padding:24px;">
    <div style="margin-bottom:28px;">
        <div class="krd-label">Event Microsite</div>
        <h1 class="krd-heading-3">{{ $event->name }}</h1>
        <p style="font-size:13px;color:#78716C;margin-top:6px;">Manage only the sections enabled by your planner.</p>
    </div>

    <div style="display:flex;gap:8px;flex-wrap:wrap;border-bottom:1px solid #E7E5E4;margin-bottom:20px;padding-bottom:2px;">
        @foreach(['story'=>'Story','gallery'=>'Gallery','wishes'=>'Wishes','gifts'=>'Gifts','responses'=>'RSVP Responses','settings'=>'Microsite Settings'] as $key => $label)
            @if($key === 'story' || $key === 'settings' || ($settings?->{$key.'_enabled'} ?? false) || $key === 'responses')
                <button type="button"
                    x-on:click="tab='{{ $key }}'; $wire.setTab('{{ $key }}')"
                    x-bind:style="tab === '{{ $key }}' ? 'padding:10px 16px;border:none;border-bottom:2px solid #7C3AED;color:#7C3AED;font-weight:600;font-size:13px;background:transparent;cursor:pointer;' : 'padding:10px 16px;border:none;border-bottom:2px solid transparent;color:#78716C;font-weight:600;font-size:13px;background:transparent;cursor:pointer;'"
                >
                    {{ $label }}
                </button>
            @endif
        @endforeach
    </div>

    <div x-show="tab === 'settings'" x-cloak>
        <div class="krd-card" style="padding:20px;">
            <div class="krd-label" style="margin-bottom:12px;">Client-editable microsite details</div>
            <div style="display:grid;grid-template-columns:1fr;gap:10px;">
                <div>
                    <label class="krd-label-text">Reception venue</label>
                    <input wire:model="receptionVenue" class="krd-input" placeholder="Reception venue name">
                </div>
                <div>
                    <label class="krd-label-text">Reception address</label>
                    <textarea wire:model="receptionAddress" class="krd-input" rows="2" placeholder="Reception address"></textarea>
                </div>
                <div>
                    <label class="krd-label-text">Reception time</label>
                    <input wire:model="receptionTime" type="time" class="krd-input">
                </div>
                <div>
                    <label class="krd-label-text">Hotel note</label>
                    <textarea wire:model="hotelsNote" class="krd-input" rows="2" placeholder="Hotel note"></textarea>
                </div>
                <div>
                    <label class="krd-label-text">Hotel map link</label>
                    <input wire:model="hotelsMapsUrl" class="krd-input" placeholder="Hotels Google Maps URL">
                </div>
                <div>
                    <label class="krd-label-text">Gallery password</label>
                    <input wire:model="galleryPassword" class="krd-input" placeholder="Password for gallery access">
                </div>
                <div>
                    <label class="krd-label-text">WhatsApp link for gallery password</label>
                    <input wire:model="galleryPasswordWhatsapp" class="krd-input" placeholder="WhatsApp URL for gallery password">
                </div>
                <div>
                    <label class="krd-label-text">Dress code note</label>
                    <textarea wire:model="dressNote" class="krd-input" rows="2" placeholder="We'd love for you to celebrate in any of these beautiful colours."></textarea>
                </div>
            </div>

            <div style="margin-top:20px;padding-top:20px;border-top:1px solid #E7E5E4;">
                <div class="krd-label" style="margin-bottom:12px;">Dress code colors</div>
                <div style="display:flex;flex-wrap:wrap;gap:10px;margin-bottom:12px;">
                    @foreach($dressColors as $groupIndex => $group)
                        <div style="width:100%;padding:12px 14px;border:1px solid #E7E5E4;border-radius:8px;background:#F9F9F8;">
                            <div style="display:flex;justify-content:space-between;align-items:center;gap:8px;margin-bottom:8px;">
                                @if($editingDressGroupIndex === $groupIndex)
                                    <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                                        <input wire:model="editingDressGroupName" type="text" class="krd-input" style="max-width:220px;">
                                        <button wire:click="saveDressGroupName" class="krd-btn krd-btn-secondary krd-btn-sm">Save</button>
                                    </div>
                                @else
                                    <strong style="font-size:12px;">{{ $group['name'] }}</strong>
                                    <button wire:click="startEditDressGroup({{ $groupIndex }})" class="krd-btn krd-btn-ghost krd-btn-sm">Edit</button>
                                @endif
                                <button wire:click="removeDressGroup({{ $groupIndex }})" class="krd-btn krd-btn-ghost krd-btn-sm" style="color:#DC2626;">Remove</button>
                            </div>
                            <div style="display:flex;flex-wrap:wrap;gap:8px;">
                                @foreach($group['colors'] as $colorIndex => $color)
                                    <div style="display:inline-flex;align-items:center;gap:8px;padding:6px 10px;border:1px solid #E7E5E4;background:#fff;border-radius:999px;">
                                        <span style="display:inline-block;width:14px;height:14px;border-radius:50%;background:{{ $color['hex'] ?? '#000' }};border:1px solid rgba(0,0,0,0.08);"></span>
                                        <span style="font-size:11px;">{{ $color['name'] }}</span>
                                        <button wire:click="removeDressColor({{ $groupIndex }}, {{ $colorIndex }})" style="border:none;background:none;color:#DC2626;cursor:pointer;font-size:11px;">×</button>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>

                <div style="display:flex;flex-wrap:wrap;gap:8px;align-items:center;">
                    <input wire:model="newGroupName" class="krd-input" placeholder="New color group" style="max-width:220px;">
                    <button wire:click="addDressGroup" class="krd-btn krd-btn-secondary krd-btn-sm">+ Group</button>
                    <div x-data="{ open: false }" style="position:relative;min-width:180px;">
                        <button type="button" x-on:click="open = !open" style="display:flex;align-items:center;justify-content:space-between;width:100%;padding:9px 12px;border:1px solid #E7E5E4;border-radius:6px;background:#fff;cursor:pointer;color:#1C1917;">
                            <span>{{ $selectedColorGroup ?: 'Choose group' }}</span>
                            <span>▾</span>
                        </button>
                        <div x-show="open" x-cloak x-on:click.outside="open = false" style="position:absolute;top:calc(100% + 6px);left:0;right:0;background:#fff;border:1px solid #E7E5E4;border-radius:8px;overflow:hidden;z-index:20;box-shadow:0 8px 24px rgba(0,0,0,0.08);">
                            @foreach($dressColors as $group)
                                <button type="button" x-on:click="$wire.set('selectedColorGroup', '{{ $group['name'] }}'); open = false" style="display:block;width:100%;text-align:left;padding:10px 12px;border:none;background:none;cursor:pointer;color:#1C1917;">{{ $group['name'] }}</button>
                            @endforeach
                        </div>
                    </div>
                    <input type="color" wire:model="newColorHex" style="width:42px;height:36px;border:1px solid #E7E5E4;border-radius:6px;">
                    <input wire:model="newColorName" class="krd-input" placeholder="Color name" style="max-width:180px;">
                    <button wire:click="addDressColor" class="krd-btn krd-btn-secondary krd-btn-sm">+ Add color</button>
                </div>
            </div>

            <div style="margin-top:18px;display:flex;justify-content:flex-start;">
                <button wire:click="saveMicrositeSettings" wire:loading.attr="disabled" class="krd-btn krd-btn-primary krd-btn-sm">Save Microsite Settings</button>
            </div>
        </div>
    </div>

    <div x-show="tab === 'story'" x-cloak>
        <div class="krd-card" style="padding:20px;margin-bottom:14px;display:flex;justify-content:space-between;align-items:center;gap:12px;">
            <div>
                <div style="font-weight:600;margin-bottom:4px;">Story</div>
                <p style="font-size:13px;color:#78716C;margin:0;">Edit the chapters your guests see on the public microsite.</p>
            </div>
            <button type="button" x-on:click="$wire.set('chapterTitle', ''); $wire.set('chapterContent', ''); $wire.set('editChapterId', null); $wire.showAddChapter(); chapterModalOpen = true" class="krd-btn krd-btn-primary krd-btn-sm">+ Add Chapter</button>
        </div>

        @forelse($chapters as $chapter)
            <div class="krd-card" style="padding:16px;margin-top:10px;">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;">
                    <div>
                        <strong>{{ $chapter->title }}</strong>
                        <p style="font-size:12px;color:#78716C;margin-top:4px;">{{ \Illuminate\Support\Str::limit($chapter->content, 180) }}</p>
                    </div>
                    <div style="display:flex;gap:6px;">
                        <button type="button" x-on:click="$wire.editChapter({{ $chapter->id }}); chapterModalOpen = true" class="krd-btn krd-btn-secondary krd-btn-sm">Edit</button>
                        <button type="button" x-on:click="deleteModalOpen = true; $wire.set('deleteTargetType', 'chapter'); $wire.set('deleteTargetId', {{ $chapter->id }})" class="krd-btn krd-btn-ghost krd-btn-sm" style="color:#DC2626;">Delete</button>
                    </div>
                </div>
            </div>
        @empty
            <div class="krd-empty-state">No story chapters yet.</div>
        @endforelse
    </div>

    <template x-teleport="body">
        <div x-cloak x-bind:style="chapterModalOpen ? 'position:fixed;top:0;left:0;right:0;bottom:0;display:grid;place-items:center;background:rgba(28,25,23,0.35);z-index:80;padding:24px;box-sizing:border-box;' : 'display:none;'">
            <div style="position:relative;margin:0 auto;width:min(640px, 100%);background:#fff;border:1px solid #E7E5E4;border-radius:16px;padding:20px;box-shadow:0 20px 48px rgba(28,25,23,0.15);">
                <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;margin-bottom:18px;">
                    <div style="font-size:18px;font-weight:700;color:#1C1917;">{{ $editChapterId ? 'Edit chapter' : 'Add chapter' }}</div>
                    <button type="button" x-on:click="$wire.set('chapterTitle', ''); $wire.set('chapterContent', ''); $wire.set('editChapterId', null); chapterModalOpen = false; $wire.closeChapterModal()" style="border:none;background:none;cursor:pointer;color:#78716C;font-size:20px;">×</button>
                </div>

                <div style="display:grid;gap:12px;">
                    <div>
                        <label class="krd-label-text">Chapter title</label>
                        <input wire:model="chapterTitle" class="krd-input" placeholder="e.g. Our Story">
                    </div>
                    <div>
                        <label class="krd-label-text">Content</label>
                        <textarea wire:model="chapterContent" class="krd-input" rows="7" placeholder="Write a chapter for your guests..."></textarea>
                    </div>
                </div>

                <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:18px;">
                    <button type="button" x-on:click="$wire.set('chapterTitle', ''); $wire.set('chapterContent', ''); $wire.set('editChapterId', null); chapterModalOpen = false; $wire.closeChapterModal()" class="krd-btn krd-btn-ghost krd-btn-sm">Cancel</button>
                    <button type="button" wire:click="saveChapter" x-on:click="chapterModalOpen = false" class="krd-btn krd-btn-primary krd-btn-sm">Save chapter</button>
                </div>
            </div>
        </div>
    </template>

    <div x-show="tab === 'gallery'" x-cloak>
        <div class="krd-card" style="padding:20px;margin-bottom:14px;">
            <input wire:model="newImages" type="file" multiple accept="image/*" class="krd-input" style="margin-bottom:10px;">
            <button wire:click="uploadImages" wire:loading.attr="disabled" wire:target="uploadImages" class="krd-btn krd-btn-primary krd-btn-sm">
                <span wire:loading.remove wire:target="uploadImages">Upload Photos</span>
                <span wire:loading wire:target="uploadImages">Uploading...</span>
            </button>
        </div>

        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:12px;">
            @forelse($images as $image)
                <div style="position:relative;border:1px solid #E7E5E4;border-radius:8px;overflow:hidden;background:#fff;">
                    <img src="{{ $image->url() }}" style="width:100%;height:140px;object-fit:cover;display:block;">
                    <button type="button" x-on:click="deleteModalOpen = true; $wire.set('deleteTargetType', 'image'); $wire.set('deleteTargetId', {{ $image->id }})" style="position:absolute;top:8px;right:8px;border:none;background:rgba(0,0,0,0.7);color:#fff;border-radius:50%;width:24px;height:24px;cursor:pointer;">×</button>
                </div>
            @empty
                <div class="krd-empty-state" style="width:100%;">No gallery photos yet.</div>
            @endforelse
        </div>
    </div>

    <div x-show="tab === 'wishes'" x-cloak>
        @forelse($wishes as $wish)
            <div class="krd-card" style="padding:16px;margin-bottom:10px;">
                <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;">
                    <strong>{{ $wish->guest_name }}</strong>
                    <span class="krd-badge {{ $wish->status === 'approved' ? 'krd-badge-green' : ($wish->status === 'rejected' ? 'krd-badge-red' : 'krd-badge-amber') }}">{{ ucfirst($wish->status) }}</span>
                </div>
                <p style="font-size:13px;color:#57534E;margin:10px 0;">{{ $wish->message }}</p>
                <div style="display:flex;flex-wrap:wrap;gap:8px;">
                    @if($wish->status === 'pending')
                        <button type="button" wire:click="approveWish({{ $wish->id }})" class="krd-btn krd-btn-sm" style="background:#D1FAE5;color:#065F46;">Approve</button>
                        <button type="button" wire:click="rejectWish({{ $wish->id }})" class="krd-btn krd-btn-sm" style="background:#FEE2E2;color:#991B1B;">Reject</button>
                    @endif
                    <button type="button" x-on:click="deleteModalOpen = true; $wire.set('deleteTargetType', 'wish'); $wire.set('deleteTargetId', {{ $wish->id }})" class="krd-btn krd-btn-ghost krd-btn-sm" style="color:#DC2626;">Delete</button>
                </div>
            </div>
        @empty
            <div class="krd-empty-state">No wishes yet.</div>
        @endforelse
    </div>

    <div x-show="tab === 'gifts'" x-cloak>
        <div class="krd-card" style="padding:20px;margin-bottom:14px;">
            <label class="krd-label-text">Note to guests</label>
            <textarea wire:model="giftNote" class="krd-input" rows="3"></textarea>
            <button wire:click="saveGiftNote" wire:loading.attr="disabled" class="krd-btn krd-btn-secondary krd-btn-sm" style="margin-top:10px;">Save Gift Details</button>
        </div>

        <div class="krd-card" style="padding:20px;margin-bottom:14px;">
            <div class="krd-label" style="margin-bottom:12px;">Bank accounts</div>
            @forelse($bankAccounts as $i => $account)
                <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;padding:8px 0;border-bottom:1px solid #F5F5F4;">
                    <span style="font-size:12.5px;">{{ $account['currency'] ?? 'NGN' }} — {{ $account['bank_name'] }} — {{ $account['account_name'] }} — {{ $account['account_number'] }}@if(!empty($account['swift_code'])) — SWIFT {{ $account['swift_code'] }}@endif</span>
                    <button type="button" x-on:click="deleteModalOpen = true; $wire.set('deleteTargetType', 'bank_account'); $wire.set('deleteTargetId', {{ $i }})" style="background:none;border:none;color:#DC2626;cursor:pointer;">×</button>
                </div>
            @empty
                <p style="font-size:12px;color:#78716C;margin:0;">No bank accounts added yet.</p>
            @endforelse

            <div style="display:flex;flex-wrap:wrap;gap:8px;margin-top:12px;align-items:center;">
                <select wire:model="newAccountCurrency" class="krd-input" style="min-width:120px;appearance:none;cursor:pointer;">
                    @foreach(['NGN','USD','GBP','EUR','CAD','GHS'] as $currency)
                        <option value="{{ $currency }}">{{ $currency }}</option>
                    @endforeach
                </select>
                <input wire:model="newBankName" type="text" class="krd-input" placeholder="Bank name" style="max-width:150px;">
                <input wire:model="newAccountName" type="text" class="krd-input" placeholder="Account name" style="max-width:150px;">
                <input wire:model="newAccountNumber" type="text" class="krd-input" placeholder="Account number" style="max-width:150px;">
                <input wire:model="newAccountSwiftCode" type="text" class="krd-input" placeholder="SWIFT/BIC" style="max-width:150px;">
                <button wire:click="addBankAccount" class="krd-btn krd-btn-secondary krd-btn-sm">+ Add</button>
            </div>
        </div>

        <div class="krd-card" style="padding:20px;">
            <div class="krd-label" style="margin-bottom:12px;">Registry links</div>
            @forelse($registryLinks as $i => $link)
                <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;padding:8px 0;border-bottom:1px solid #F5F5F4;">
                    <span style="font-size:12.5px;">{{ !empty($link['currencies']) ? implode(', ', $link['currencies']) . ' — ' : '' }}{{ $link['label'] }} — {{ $link['url'] }}</span>
                    <button type="button" x-on:click="deleteModalOpen = true; $wire.set('deleteTargetType', 'registry_link'); $wire.set('deleteTargetId', {{ $i }})" style="background:none;border:none;color:#DC2626;cursor:pointer;">×</button>
                </div>
            @empty
                <p style="font-size:12px;color:#78716C;margin:0;">No registry links added yet.</p>
            @endforelse

            <div style="margin-top:12px;">
                <div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:8px;">
                    @foreach(['NGN','USD','GBP','EUR','CAD'] as $currency)
                        <label style="display:flex;align-items:center;gap:6px;font-size:11px;color:#57534E;">
                            <input type="checkbox" value="{{ $currency }}" wire:model="newLinkCurrencies" style="accent-color:#7C3AED;"> {{ $currency }}
                        </label>
                    @endforeach
                </div>
                <div style="display:flex;flex-wrap:wrap;gap:8px;align-items:center;">
                    <input wire:model="newRegistryLabel" type="text" class="krd-input" placeholder="Label, e.g. Gift Registry" style="max-width:200px;">
                    <input wire:model="newRegistryUrl" type="text" class="krd-input" placeholder="URL" style="max-width:260px;">
                    <button wire:click="addRegistryLink" class="krd-btn krd-btn-secondary krd-btn-sm">+ Add</button>
                </div>
            </div>
        </div>
    </div>

    <div x-show="tab === 'responses'" x-cloak>
        @forelse($responses as $response)
            <div class="krd-card" style="padding:16px;margin-bottom:10px;">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;">
                    <div>
                        <strong>{{ $response->respondent_name }}</strong>
                        <p style="font-size:12px;color:#78716C;margin:6px 0 0;">{{ $response->respondent_email }} · {{ $response->status }}</p>
                    </div>
                    <span class="krd-badge {{ $response->checked_in_at ? 'krd-badge-green' : 'krd-badge-stone' }}">{{ $response->checked_in_at ? 'Checked in' : 'Not checked in' }}</span>
                </div>
                <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;margin-top:12px;">
                    <span style="font-size:12px;color:#57534E;">{{ $response->totalAttendees() }} attending</span>
                    <div style="display:flex;gap:8px;flex-wrap:wrap;justify-content:flex-end;">
                        <button wire:click="checkInResponse({{ $response->id }})" class="krd-btn krd-btn-secondary krd-btn-sm">{{ $response->checked_in_at ? 'Undo check-in' : 'Check in' }}</button>
                        <button type="button" x-on:click="deleteModalOpen = true; $wire.set('deleteTargetType', 'response'); $wire.set('deleteTargetId', {{ $response->id }})" class="krd-btn krd-btn-ghost krd-btn-sm" style="color:#DC2626;">Delete</button>
                    </div>
                </div>
            </div>
        @empty
            <div class="krd-empty-state">No RSVP responses yet.</div>
        @endforelse
    </div>

    <template x-teleport="body">
        <div x-cloak x-bind:style="deleteModalOpen ? 'position:fixed;top:0;left:0;right:0;bottom:0;display:grid;place-items:center;background:rgba(28,25,23,0.35);z-index:90;padding:24px;box-sizing:border-box;' : 'display:none;'">
            <div style="position:relative;margin:0 auto;width:min(420px, 100%);background:#fff;border:1px solid #E7E5E4;border-radius:16px;padding:20px;box-shadow:0 20px 48px rgba(28,25,23,0.15);">
                <div style="font-size:18px;font-weight:700;color:#1C1917;margin-bottom:8px;">Confirm delete</div>
                <p style="margin:0 0 18px;color:#57534E;font-size:13px;line-height:1.5;">This action cannot be undone. Are you sure you want to delete this {{ str_replace('_', ' ', $deleteTargetType) }}?</p>
                <div style="display:flex;justify-content:flex-end;gap:8px;">
                    <button type="button" x-on:click="deleteModalOpen = false" class="krd-btn krd-btn-ghost krd-btn-sm">Cancel</button>
                    <button type="button" x-on:click="deleteModalOpen = false; $wire.executeDelete('{{ $deleteTargetType }}', {{ $deleteTargetId ?? 'null' }})" class="krd-btn krd-btn-danger krd-btn-sm">Delete</button>
                </div>
            </div>
        </div>
    </template>
</div>

