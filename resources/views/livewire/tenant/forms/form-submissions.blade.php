<div>
    {{-- Header --}}
    <div style="margin-bottom:24px;">
        <div style="margin-bottom:8px;">
            <a href="{{ route('tenant.forms') }}" wire:navigate
                style="color:#A8A29E;text-decoration:none;font-size:13px;">← Back to Forms</a>
        </div>
        <div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:12px;">
            <div>
                <div class="krd-label" style="margin-bottom:4px;">Forms & Bookings</div>
                <h2 class="krd-heading-3" style="color:#1C1917;">{{ $form->name }}</h2>
                <div style="margin-top:6px;display:flex;gap:8px;flex-wrap:wrap;">
                    <span class="krd-badge {{ $form->type === 'consultation' ? 'krd-badge-violet' : 'krd-badge-blue' }}">
                        {{ ucfirst($form->type) }}
                    </span>
                    <span class="krd-badge {{ $form->status === 'active' ? 'krd-badge-green' : 'krd-badge-stone' }}">
                        {{ ucfirst($form->status) }}
                    </span>
                </div>
            </div>
            <a href="{{ route('tenant.forms.edit', $form->id) }}" wire:navigate
                class="krd-btn krd-btn-secondary krd-btn-sm">Edit Form</a>
        </div>
    </div>

    {{-- Stats --}}
    <div class="krd-grid-4" style="margin-bottom:20px;gap:10px;">
        <div class="krd-card" style="padding:14px;border-left:3px solid #7C3AED;">
            <div class="krd-label" style="margin-bottom:4px;">Total</div>
            <div style="font-size:24px;font-weight:700;color:#7C3AED;">{{ $stats['total'] }}</div>
        </div>
        <div class="krd-card" style="padding:14px;border-left:3px solid #F59E0B;">
            <div class="krd-label" style="margin-bottom:4px;">New</div>
            <div style="font-size:24px;font-weight:700;color:#F59E0B;">{{ $stats['new'] }}</div>
        </div>
        @if($form->type === 'consultation')
        <div class="krd-card" style="padding:14px;border-left:3px solid #10B981;">
            <div class="krd-label" style="margin-bottom:4px;">Confirmed</div>
            <div style="font-size:24px;font-weight:700;color:#10B981;">{{ $stats['confirmed'] }}</div>
        </div>
        <div class="krd-card" style="padding:14px;border-left:3px solid #A8A29E;">
            <div class="krd-label" style="margin-bottom:4px;">Pending</div>
            <div style="font-size:24px;font-weight:700;color:#A8A29E;">{{ $stats['pending'] }}</div>
        </div>
        @endif
    </div>

    {{-- Consultation Bookings --}}
    @if($form->type === 'consultation' && $bookings->isNotEmpty())
    <div style="margin-bottom:24px;">
        <div class="krd-label" style="margin-bottom:12px;">Upcoming Bookings</div>
        <div class="krd-card" style="padding:0;overflow:hidden;" id="bookings-desktop">
            <div class="krd-table-wrap">
                <table class="krd-table">
                    <thead>
                        <tr>
                            <th>Guest</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($bookings as $booking)
                        <tr>
                            <td>
                                <div style="font-size:13px;font-weight:500;color:#1C1917;">{{ $booking->guest_name }}</div>
                                @if($booking->guest_email)
                                <div style="font-size:11px;color:#A8A29E;">{{ $booking->guest_email }}</div>
                                @endif
                            </td>
                            <td style="font-size:13px;color:#57534E;">{{ $booking->booking_date->format('D, d M Y') }}</td>
                            <td style="font-size:13px;color:#57534E;">{{ \Carbon\Carbon::parse($booking->booking_time)->format('g:i A') }}</td>
                            <td>
                                <span class="krd-badge {{ $booking->consultation_type === 'virtual' ? 'krd-badge-blue' : 'krd-badge-amber' }}">
                                    {{ ucfirst($booking->consultation_type) }}
                                </span>
                            </td>
                            <td>
                                <div x-data="{ open: false }" style="position:relative;">
                                    <button type="button" x-on:click="open = !open" x-on:click.outside="open = false"
                                        class="krd-badge {{ $booking->status === 'confirmed' ? 'krd-badge-green' : ($booking->status === 'cancelled' ? 'krd-badge-red' : 'krd-badge-amber') }}"
                                        style="cursor:pointer;border:none;">
                                        {{ ucfirst($booking->status) }} ▾
                                    </button>
                                    <div x-show="open" x-cloak
                                        style="position:absolute;top:calc(100% + 4px);left:0;background:#fff;border:1px solid #E7E5E4;border-radius:6px;z-index:50;min-width:130px;overflow:hidden;">
                                        @foreach(['pending' => 'Pending', 'confirmed' => 'Confirmed', 'cancelled' => 'Cancelled'] as $val => $label)
                                        <button type="button"
                                            wire:click="updateBookingStatus({{ $booking->id }}, '{{ $val }}')"
                                            x-on:click="open = false"
                                            style="display:block;width:100%;padding:8px 12px;font-size:12px;text-align:left;border:none;background:{{ $booking->status === $val ? '#F5F3FF' : '#fff' }};color:{{ $booking->status === $val ? '#7C3AED' : '#57534E' }};cursor:pointer;">
                                            {{ $label }}
                                        </button>
                                        @endforeach
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($booking->guest_phone)
                                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $booking->guest_phone) }}"
                                    target="_blank" class="krd-btn krd-btn-ghost krd-btn-sm">WhatsApp</a>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    {{-- Submissions --}}
    <div class="krd-label" style="margin-bottom:12px;">All Submissions</div>

    @if($submissions->isEmpty())
    <div class="krd-card">
        <div class="krd-empty-state">
            <div class="krd-empty-state-icon">📋</div>
            <div class="krd-empty-state-title">No submissions yet</div>
            <div class="krd-empty-state-desc">Share the public link or embed the form to start collecting submissions.</div>
        </div>
    </div>
    @else

    <div style="display:flex;flex-direction:column;gap:12px;">
        @foreach($submissions as $submission)
        <div class="krd-card" style="padding:16px;">
            <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:12px;flex-wrap:wrap;">
                <div>
                    <div style="font-size:12px;color:#A8A29E;">
                        {{ $submission->submitted_at?->format('D, d M Y · g:i A') }}
                        @if($submission->source)
                        · <span class="krd-badge krd-badge-stone" style="font-size:10px;">{{ ucfirst($submission->source) }}</span>
                        @endif
                    </div>
                </div>
                <button wire:click="confirmDelete({{ $submission->id }})"
                    class="krd-btn krd-btn-sm"
                    style="background:#FEE2E2;color:#DC2626;border-color:#FECACA;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/>
                    </svg>
                </button>
            </div>

            <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(200px, 1fr));gap:10px;">
                @foreach($submission->values as $value)
                <div style="background:#F5F5F4;border-radius:6px;padding:10px 12px;">
                    <div style="font-size:10px;font-weight:600;letter-spacing:0.06em;text-transform:uppercase;color:#A8A29E;margin-bottom:4px;">
                        {{ $value->field?->label ?? 'Field' }}
                    </div>
                    <div style="font-size:13px;color:#1C1917;font-weight:500;word-break:break-word;">
                        {{ $value->value ?: '—' }}
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endforeach
    </div>
    @endif

    {{-- Delete Modal --}}
    @if($showDeleteModal)
    <div style="position:fixed;inset:0;background:rgba(0,0,0,0.4);z-index:60;display:flex;align-items:center;justify-content:center;padding:16px;">
        <div style="background:#fff;border-radius:8px;padding:24px;max-width:400px;width:100%;">
            <h3 style="font-size:16px;font-weight:600;color:#1C1917;margin-bottom:8px;">Delete Submission?</h3>
            <p style="font-size:13px;color:#78716C;margin-bottom:24px;line-height:1.6;">
                This will permanently delete this submission and cannot be undone.
            </p>
            <div style="display:flex;gap:10px;">
                <button wire:click="deleteSubmission" class="krd-btn krd-btn-danger" style="flex:1;">Yes, Delete</button>
                <button wire:click="$set('showDeleteModal', false)" class="krd-btn krd-btn-secondary" style="flex:1;">Cancel</button>
            </div>
        </div>
    </div>
    @endif

</div>