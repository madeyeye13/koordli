<div>
    {{-- Header --}}
    <div style="margin-bottom:24px;">
        <div style="margin-bottom:8px;">
            <a href="{{ route('tenant.vendors') }}" wire:navigate style="color:#A8A29E;text-decoration:none;font-size:13px;">
                ← Back to Vendor Directory
            </a>
        </div>
        <div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:12px;">
            <div style="display:flex;align-items:center;gap:12px;">
                <div style="width:48px;height:48px;border-radius:10px;background:#EDE9FE;display:flex;align-items:center;justify-content:center;font-size:20px;font-weight:700;color:#7C3AED;flex-shrink:0;">
                    {{ strtoupper(substr($vendor->name, 0, 1)) }}
                </div>
                <div>
                    <h2 class="krd-heading-3" style="color:#1C1917;">
                        {{ $vendor->name }}
                        @if($vendor->is_preferred)<span style="font-size:18px;">⭐</span>@endif
                    </h2>
                    <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-top:3px;">
                        @if($vendor->category)
                        <span class="krd-badge krd-badge-violet">{{ $vendor->category->name }}</span>
                        @endif
                        @if($vendor->rating)
                        <span style="font-size:12px;color:#F59E0B;">{{ $vendor->ratingStars() }}</span>
                        @endif
                        @if(!$vendor->is_active)
                        <span class="krd-badge krd-badge-stone">Inactive</span>
                        @endif
                    </div>
                </div>
            </div>
            <div style="display:flex;gap:8px;flex-wrap:wrap;">
                <button wire:click="$toggle('showAssignForm')" class="krd-btn krd-btn-primary krd-btn-sm">
                    {{ $showAssignForm ? '✕ Cancel' : '+ Assign to Event' }}
                </button>
                @if($vendor->email)
                <button wire:click="inviteVendor"
                    wire:loading.attr="disabled"
                    wire:target="inviteVendor"
                    class="krd-btn krd-btn-secondary krd-btn-sm">
                    <span wire:loading.remove wire:target="inviteVendor">✉️ Invite to Portal</span>
                    <span wire:loading wire:target="inviteVendor">Sending...</span>
                </button>
                @endif
                <a href="{{ route('tenant.vendors.edit', $vendor->id) }}" wire:navigate class="krd-btn krd-btn-secondary krd-btn-sm">
                    Edit Vendor
                </a>
            </div>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr;gap:16px;" id="vendor-detail-grid">

        {{-- Left: Info + Assignments --}}
        <div style="display:flex;flex-direction:column;gap:16px;">

            {{-- Assign to Event Form --}}
            @if($showAssignForm)
            <div class="krd-card" style="padding:20px;border:2px solid #7C3AED;">
                <div style="font-size:13px;font-weight:600;color:#1C1917;margin-bottom:16px;">Assign to Event</div>

                <div class="krd-input-group">
                    <label class="krd-label-text">Event <span style="color:#EF4444;">*</span></label>
                    <x-ui.dropdown wire="assign_event_id" placeholder="Select event"
                        selected="{{ $assign_event_id ? ($availableEvents->firstWhere('id', $assign_event_id)?->name ?? 'Select event') : 'Select event' }}">
                        @forelse($availableEvents as $event)
                        <div class="krd-dropdown-option {{ $assign_event_id == $event->id ? 'selected' : '' }}"
                            x-on:click="select('{{ $event->name }}', {{ $event->id }})">
                            <div>
                                <div style="font-size:13px;">{{ $event->name }}</div>
                                @if($event->date)<div style="font-size:10px;color:#A8A29E;">{{ $event->date->format('M d, Y') }}</div>@endif
                            </div>
                        </div>
                        @empty
                        <div class="krd-dropdown-empty">All events already have this vendor assigned.</div>
                        @endforelse
                    </x-ui.dropdown>
                    @error('assign_event_id') <span class="krd-input-error-msg">{{ $message }}</span> @enderror

                    @if(!empty($assignConflicts))
                    <div style="margin-top:8px;background:#FEF3C7;border:1px solid #FDE68A;border-radius:6px;padding:10px 12px;">
                        @foreach($assignConflicts as $conflict)
                        <div style="font-size:12px;color:#92400E;display:flex;gap:6px;align-items:flex-start;{{ !$loop->last ? 'margin-bottom:6px;' : '' }}">
                            <span>⚠️</span><span>{{ $conflict }}</span>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div class="krd-input-group">
                        <label class="krd-label-text">Amount Agreed</label>
                        <input wire:model="assign_amount" type="number" step="0.01" min="0" class="krd-input" placeholder="0.00" />
                    </div>

                    <div class="krd-input-group">
                        <label class="krd-label-text">Amount Paid</label>
                        <input wire:model="assign_amount_paid" type="number" step="0.01" min="0" class="krd-input" placeholder="0.00" />
                    </div>
                    <div class="krd-input-group">
                        <label class="krd-label-text">Status</label>
                        <x-ui.dropdown wire="assign_status" placeholder="Pending"
                            selected="{{ match($assign_status) { 'confirmed' => 'Confirmed', 'cancelled' => 'Cancelled', default => 'Pending' } }}">
                            <div class="krd-dropdown-option {{ $assign_status === 'pending' ? 'selected' : '' }}" x-on:click="select('Pending', 'pending')">
                                <span style="width:8px;height:8px;border-radius:50%;background:#F59E0B;flex-shrink:0;display:inline-block;"></span> Pending
                            </div>
                            <div class="krd-dropdown-option {{ $assign_status === 'confirmed' ? 'selected' : '' }}" x-on:click="select('Confirmed', 'confirmed')">
                                <span style="width:8px;height:8px;border-radius:50%;background:#10B981;flex-shrink:0;display:inline-block;"></span> Confirmed
                            </div>
                            <div class="krd-dropdown-option {{ $assign_status === 'cancelled' ? 'selected' : '' }}" x-on:click="select('Cancelled', 'cancelled')">
                                <span style="width:8px;height:8px;border-radius:50%;background:#EF4444;flex-shrink:0;display:inline-block;"></span> Cancelled
                            </div>
                        </x-ui.dropdown>
                    </div>
                </div>

                <div class="krd-input-group" style="margin-bottom:0;">
                    <label class="krd-label-text">Notes</label>
                    <input wire:model="assign_notes" type="text" class="krd-input" placeholder="e.g. 50% deposit paid, delivery at 8am..." />
                </div>

                <div style="margin-top:16px;display:flex;gap:10px;">
                    <button wire:click="assignToEvent" wire:loading.attr="disabled" class="krd-btn krd-btn-primary">
                        <span wire:loading.remove wire:target="assignToEvent">Assign Vendor</span>
                        <span wire:loading wire:target="assignToEvent">Assigning...</span>
                    </button>
                    <button wire:click="$set('showAssignForm', false)" type="button" class="krd-btn krd-btn-ghost">Cancel</button>
                </div>
            </div>
            @endif

            {{-- Event Assignments --}}
            <div class="krd-card" style="padding:0;overflow:hidden;">
                <div style="padding:16px;border-bottom:1px solid #E7E5E4;display:flex;align-items:center;justify-content:space-between;">
                    <div class="krd-label">Event Assignments</div>
                    <span style="font-size:12px;color:#A8A29E;">{{ $vendor->eventAssignments->count() }} {{ Str::plural('event', $vendor->eventAssignments->count()) }}</span>
                </div>

                @if($vendor->eventAssignments->isEmpty())
                <div class="krd-empty-state" style="padding:32px;">
                    <div class="krd-empty-state-icon" style="font-size:24px;">📅</div>
                    <div class="krd-empty-state-title">Not assigned to any events yet</div>
                    <div class="krd-empty-state-desc">Click "Assign to Event" to add this vendor to an event.</div>
                </div>
                @else
                @foreach($vendor->eventAssignments->sortByDesc('created_at') as $assignment)
                @if($editAssignId === $assignment->id)
                {{-- Edit row --}}
                <div style="padding:16px;border-bottom:1px solid #E7E5E4;background:#F5F3FF;">
                    <div style="font-size:13px;font-weight:500;color:#1C1917;margin-bottom:12px;">{{ $assignment->event->name }}</div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:12px;">
                        <div class="krd-input-group" style="margin-bottom:0;">
                            <label class="krd-label-text">Amount Agreed</label>
                            <input wire:model="editAmountAgreed" type="number" step="0.01" class="krd-input" />
                        </div>
                        <div class="krd-input-group" style="margin-bottom:0;">
                            <label class="krd-label-text">Amount Paid</label>
                            <input wire:model="editAmountPaid" type="number" step="0.01" class="krd-input" />
                        </div>
                    </div>
                    <div style="display:flex;gap:8px;flex-wrap:wrap;">
                        @foreach(['pending' => 'Pending', 'confirmed' => 'Confirmed', 'cancelled' => 'Cancelled'] as $val => $label)
                        <button type="button"
                            wire:click="$set('editStatus', '{{ $val }}')"
                            style="padding:5px 12px;font-size:12px;border-radius:4px;cursor:pointer;border:1px solid {{ $editStatus === $val ? '#7C3AED' : '#E7E5E4' }};background:{{ $editStatus === $val ? '#EDE9FE' : '#fff' }};color:{{ $editStatus === $val ? '#7C3AED' : '#57534E' }};">
                            {{ $label }}
                        </button>
                        @endforeach
                    </div>
                    <div style="display:flex;gap:8px;margin-top:12px;">
                        <button wire:click="saveEditAssignment" class="krd-btn krd-btn-primary krd-btn-sm">Save</button>
                        <button wire:click="cancelEditAssignment" class="krd-btn krd-btn-ghost krd-btn-sm">Cancel</button>
                    </div>
                </div>
                @else
                {{-- View row --}}
                <div style="padding:14px 16px;border-bottom:1px solid #E7E5E4;display:flex;align-items:flex-start;justify-content:space-between;gap:12px;flex-wrap:wrap;">
                    <div style="flex:1;min-width:0;">
                        <a href="{{ route('tenant.events.show', $assignment->event->slug) }}" wire:navigate
                            style="font-size:13px;font-weight:500;color:#1C1917;text-decoration:none;">
                            {{ $assignment->event->name }}
                        </a>
                        <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:4px;">
                            <span class="krd-badge {{ $assignment->statusBadge() }}" style="font-size:10px;">
                                {{ ucfirst($assignment->status) }}
                            </span>
                            @if($assignment->event->date)
                            <span style="font-size:11px;color:#A8A29E;">{{ $assignment->event->date->format('M d, Y') }}</span>
                            @endif
                        </div>
                        @if((float)$assignment->amount_agreed > 0)
                        <div style="display:flex;gap:12px;margin-top:6px;flex-wrap:wrap;align-items:center;">
                            <span style="font-size:11px;color:#57534E;">Agreed: <strong>₦{{ number_format($assignment->amount_agreed, 2) }}</strong></span>
                            <span style="font-size:11px;color:#10B981;">Paid: <strong>₦{{ number_format($assignment->amount_paid, 2) }}</strong></span>
                            @if($assignment->balance() > 0)
                            <span style="font-size:11px;color:#EF4444;">Balance: <strong>₦{{ number_format($assignment->balance(), 2) }}</strong></span>
                            @endif
                            @php $linkedInvoice = $assignment->invoices()->latest()->first(); @endphp
                            @if($linkedInvoice)
                            <a href="{{ route('tenant.invoices.show', $linkedInvoice->uuid) }}" wire:navigate
                                style="font-size:11px;color:#7C3AED;text-decoration:underline;">
                                View Full Invoice →
                            </a>
                            @endif
                        </div>
                        @endif
                        @if($assignment->notes)
                        <div style="font-size:11px;color:#A8A29E;margin-top:4px;">{{ $assignment->notes }}</div>
                        @endif
                    </div>
                    <div style="display:flex;gap:6px;flex-shrink:0;flex-wrap:wrap;">
                        @if($assignment->eventHasEnded())
                        @php $existingReview = $assignment->plannerReview(); @endphp
                        <button wire:click="showReview({{ $assignment->id }})"
                            class="krd-btn krd-btn-sm"
                            style="background:{{ $existingReview ? '#F5F3FF' : '#EDE9FE' }};color:#7C3AED;border-color:#DDD6FE;">
                            {{ $existingReview ? '★ Edit Review' : '★ Rate Vendor' }}
                        </button>
                        @endif
                        <button wire:click="startEditAssignment({{ $assignment->id }})" class="krd-btn krd-btn-secondary krd-btn-sm">Edit</button>
                        <button wire:click="confirmDeleteAssignment({{ $assignment->id }})" class="krd-btn krd-btn-sm" style="background:#FEE2E2;color:#DC2626;border-color:#FECACA;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6M14 11v6M9 6V4h6v2"/></svg>
                        </button>
                    </div>

                    @if($assignment->plannerReview() || $assignment->clientReview())
                    <div style="width:100%;margin-top:10px;padding-top:10px;border-top:1px dashed #E7E5E4;display:flex;gap:16px;flex-wrap:wrap;">
                        @if($rev = $assignment->plannerReview())
                        <div style="font-size:11px;color:#78716C;">
                            <span style="color:#7C3AED;font-weight:600;">Planner:</span>
                            <span style="color:#F59E0B;">{{ str_repeat('★', round($rev->averageScore())) }}{{ str_repeat('☆', 5 - round($rev->averageScore())) }}</span>
                            ({{ $rev->averageScore() }})
                        </div>
                        @endif
                        @if($rev = $assignment->clientReview())
                        <div style="font-size:11px;color:#78716C;">
                            <span style="color:#3B82F6;font-weight:600;">Client:</span>
                            <span style="color:#F59E0B;">{{ str_repeat('★', round($rev->averageScore())) }}{{ str_repeat('☆', 5 - round($rev->averageScore())) }}</span>
                            ({{ $rev->averageScore() }})
                        </div>
                        @endif
                    </div>
                    @endif
                </div>
                @endif
                @endforeach
                @endif
            </div>
        </div>

        {{-- Right: Vendor Info --}}
        <div style="display:flex;flex-direction:column;gap:12px;" id="vendor-info-panel">
            <div class="krd-card" style="padding:20px;">
                <div class="krd-label" style="margin-bottom:12px;">Contact Information</div>
                @foreach([
                    ['label' => 'Contact',   'value' => $vendor->contact_name ?? '—'],
                    ['label' => 'Phone',     'value' => $vendor->phone ?? '—'],
                    ['label' => 'Email',     'value' => $vendor->email ?? '—'],
                    ['label' => 'Instagram', 'value' => $vendor->instagram ?? '—'],
                    ['label' => 'Website',   'value' => $vendor->website ?? '—'],
                ] as $info)
                <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid #E7E5E4;">
                    <span style="font-size:12px;color:#78716C;">{{ $info['label'] }}</span>
                    <span style="font-size:12px;font-weight:500;color:#1C1917;text-align:right;max-width:60%;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                        @if($info['label'] === 'Website' && $vendor->website)
                            <a href="{{ $vendor->website }}" target="_blank" style="color:#7C3AED;text-decoration:none;">{{ $vendor->website }}</a>
                        @else
                            {{ $info['value'] }}
                        @endif
                    </span>
                </div>
                @endforeach
            </div>

            @if($vendor->description || $vendor->notes)
            <div class="krd-card" style="padding:20px;">
                @if($vendor->description)
                <div class="krd-label" style="margin-bottom:8px;">About</div>
                <p style="font-size:12px;color:#57534E;line-height:1.7;margin-bottom:{{ $vendor->notes ? '16px' : '0' }};">{{ $vendor->description }}</p>
                @endif
                @if($vendor->notes)
                <div class="krd-label" style="margin-bottom:8px;">Internal Notes</div>
                <p style="font-size:12px;color:#78716C;line-height:1.7;">{{ $vendor->notes }}</p>
                @endif
            </div>
            @endif

            {{-- Stats --}}
            <div class="krd-card" style="padding:20px;">
                <div class="krd-label" style="margin-bottom:12px;">Stats</div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                    <div style="text-align:center;padding:12px;background:#F5F5F4;border-radius:6px;">
                        <div style="font-size:24px;font-weight:700;color:#7C3AED;">{{ $vendor->eventAssignments->count() }}</div>
                        <div style="font-size:10px;color:#A8A29E;margin-top:2px;">Total Events</div>
                    </div>
                    <div style="text-align:center;padding:12px;background:#F5F5F4;border-radius:6px;">
                        <div style="font-size:24px;font-weight:700;color:#10B981;">{{ $vendor->eventAssignments->where('status', 'confirmed')->count() }}</div>
                        <div style="font-size:10px;color:#A8A29E;margin-top:2px;">Confirmed</div>
                    </div>
                </div>
            </div>

            {{-- Performance History --}}
            @if($vendor->reviews_count > 0)
            <div class="krd-card" style="padding:20px;">
                <div class="krd-label" style="margin-bottom:12px;">Performance History</div>

                <div style="text-align:center;padding:16px 0;border-bottom:1px solid #E7E5E4;margin-bottom:14px;">
                    <div style="font-size:32px;font-weight:700;color:#7C3AED;">{{ $vendor->weighted_rating }}</div>
                    <div style="color:#F59E0B;font-size:16px;margin:4px 0;">
                        {{ str_repeat('★', round($vendor->weighted_rating)) }}{{ str_repeat('☆', 5 - round($vendor->weighted_rating)) }}
                    </div>
                    <div style="font-size:11px;color:#A8A29E;">Based on {{ $vendor->reviews_count }} {{ Str::plural('review', $vendor->reviews_count) }}</div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:14px;">
                    <div style="text-align:center;padding:10px;background:#F5F3FF;border-radius:6px;">
                        <div style="font-size:18px;font-weight:700;color:#7C3AED;">{{ $vendor->planner_rating_avg ?? '—' }}</div>
                        <div style="font-size:10px;color:#A8A29E;">Planner Avg (80%)</div>
                    </div>
                    <div style="text-align:center;padding:10px;background:#EFF6FF;border-radius:6px;">
                        <div style="font-size:18px;font-weight:700;color:#3B82F6;">{{ $vendor->client_rating_avg ?? '—' }}</div>
                        <div style="font-size:10px;color:#A8A29E;">Client Avg (20%)</div>
                    </div>
                </div>

                @php $catAvgs = $vendor->categoryAverages(); @endphp
                @if(!empty($catAvgs))
                <div style="display:flex;flex-direction:column;gap:6px;">
                    @foreach([
                        'professionalism' => 'Professionalism',
                        'communication' => 'Communication',
                        'punctuality' => 'Punctuality',
                        'quality_of_service' => 'Quality of Service',
                        'reliability' => 'Reliability',
                        'overall_experience' => 'Overall Experience',
                    ] as $key => $label)
                    <div style="display:flex;justify-content:space-between;align-items:center;">
                        <span style="font-size:11px;color:#78716C;">{{ $label }}</span>
                        <span style="font-size:11px;font-weight:600;color:#1C1917;">{{ $catAvgs[$key] }}/5</span>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
            @endif

           {{-- Availability (read-only — managed by vendor) --}}
            <div class="krd-card" style="padding:20px;">
                <div class="krd-label" style="margin-bottom:12px;">Unavailable Dates</div>
                <p style="font-size:11px;color:#A8A29E;margin-bottom:12px;line-height:1.5;">Managed by the vendor from their portal.</p>

                @forelse($vendor->unavailableDates as $unavail)
                <div style="padding:10px 0;border-bottom:1px solid #E7E5E4;">
                    <div style="display:flex;align-items:center;gap:6px;">
                        <span style="width:8px;height:8px;border-radius:50%;background:{{ $unavail->reasonColor() }};flex-shrink:0;"></span>
                        <span style="font-size:12px;font-weight:500;color:#1C1917;">{{ $unavail->reasonLabel() }}</span>
                    </div>
                    <div style="font-size:11px;color:#78716C;margin-top:2px;">
                        {{ $unavail->date_from->format('M d') }}
                        @if($unavail->date_from->ne($unavail->date_to))
                            — {{ $unavail->date_to->format('M d, Y') }}
                        @else
                            , {{ $unavail->date_from->format('Y') }}
                        @endif
                    </div>
                    @if($unavail->notes)
                    <div style="font-size:11px;color:#A8A29E;margin-top:2px;">{{ $unavail->notes }}</div>
                    @endif
                </div>
                @empty
                <div style="font-size:12px;color:#A8A29E;text-align:center;padding:12px 0;">No unavailable dates recorded.</div>
                @endforelse
            </div>
        </div>

    </div>


    {{-- Review Modal --}}
    @if($showReviewForm)
    <div style="position:fixed;inset:0;background:rgba(0,0,0,0.4);z-index:60;display:flex;align-items:center;justify-content:center;padding:16px;overflow-y:auto;">
        <div style="background:#fff;border-radius:8px;padding:28px;max-width:480px;width:100%;max-height:90vh;overflow-y:auto;">
            <h3 style="font-size:17px;font-weight:600;color:#1C1917;margin-bottom:4px;">Rate This Vendor</h3>
            <p style="font-size:12px;color:#78716C;margin-bottom:20px;">Reviews can be edited within 7 days of submission.</p>

            @php
                $categories = [
                    'r_professionalism'    => 'Professionalism',
                    'r_communication'      => 'Communication',
                    'r_punctuality'        => 'Punctuality',
                    'r_quality_of_service' => 'Quality of Service',
                    'r_reliability'        => 'Reliability',
                    'r_overall_experience' => 'Overall Experience',
                ];
            @endphp

            @foreach($categories as $field => $label)
            <div style="margin-bottom:16px;">
                <div style="font-size:13px;font-weight:500;color:#1C1917;margin-bottom:6px;">{{ $label }}</div>
                <div style="display:flex;gap:4px;" x-data="{ val: {{ $this->{$field} }} }">
                    @for($i = 1; $i <= 5; $i++)
                    <button type="button"
                        x-on:click="val = {{ $i }}; $wire.setRating('{{ $field }}', {{ $i }})"
                        style="background:none;border:none;cursor:pointer;font-size:24px;line-height:1;padding:0;"
                        :style="val >= {{ $i }} ? 'color:#F59E0B;' : 'color:#D6D3D1;'">
                        ★
                    </button>
                    @endfor
                </div>
            </div>
            @endforeach

            <div class="krd-input-group" style="margin-bottom:0;">
                <label class="krd-label-text">Comment <span style="color:#A8A29E;font-weight:400;">(optional)</span></label>
                <textarea wire:model="r_comment" class="krd-input" rows="3" placeholder="Share your experience working with this vendor..."></textarea>
            </div>

            <div style="display:flex;gap:10px;margin-top:20px;">
                <button wire:click="saveReview" wire:loading.attr="disabled" class="krd-btn krd-btn-primary" style="flex:1;">
                    <span wire:loading.remove wire:target="saveReview">{{ $editingReviewId ? 'Update Review' : 'Submit Review' }}</span>
                    <span wire:loading wire:target="saveReview">Saving...</span>
                </button>
                <button wire:click="$set('showReviewForm', false)" class="krd-btn krd-btn-secondary" style="flex:1;">Cancel</button>
            </div>
        </div>
    </div>
    @endif

    {{-- Delete Assignment Modal --}}
    @if($showDeleteAssign)
    <div style="position:fixed;inset:0;background:rgba(0,0,0,0.4);z-index:60;display:flex;align-items:center;justify-content:center;padding:16px;">
        <div style="background:#fff;border-radius:8px;padding:24px;max-width:400px;width:100%;">
            <h3 style="font-size:16px;font-weight:600;color:#1C1917;margin-bottom:8px;">Remove Assignment?</h3>
            <p style="font-size:13px;color:#78716C;margin-bottom:24px;line-height:1.6;">This will remove the vendor from this event. The vendor will remain in your directory.</p>
            <div style="display:flex;gap:10px;">
                <button wire:click="deleteAssignment" class="krd-btn krd-btn-danger" style="flex:1;">Yes, Remove</button>
                <button wire:click="cancelEditAssignment" class="krd-btn krd-btn-secondary" style="flex:1;">Cancel</button>
            </div>
        </div>
    </div>
    @endif

</div>

<style>
@media (min-width: 768px) {
    #vendor-detail-grid {
        grid-template-columns: 1fr 280px !important;
    }
}
</style>