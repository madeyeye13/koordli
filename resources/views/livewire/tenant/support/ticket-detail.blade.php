<div>
    <div style="margin-bottom:20px;">
        <div style="margin-bottom:8px;">
            <a href="{{ route('tenant.support.tickets') }}" wire:navigate style="color:#A8A29E;text-decoration:none;font-size:13px;">← Back to My Tickets</a>
        </div>
        <div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:12px;">
            <div>
                <h2 class="krd-heading-3" style="color:#1C1917;">{{ $ticket->subject }}</h2>
                <div style="font-size:12px;color:#A8A29E;margin-top:4px;">#{{ strtoupper(substr($ticket->uuid, 0, 8)) }} · Opened {{ $ticket->created_at->format('d M Y') }}</div>
            </div>
            <div style="display:flex;gap:8px;">
                <span class="krd-badge" style="background:{{ $ticket->priorityColor() }}1a;color:{{ $ticket->priorityColor() }};">{{ ucfirst($ticket->priority) }}</span>
                <span class="krd-badge" style="background:{{ $ticket->statusColor() }}1a;color:{{ $ticket->statusColor() }};">{{ $ticket->statusLabel() }}</span>
            </div>
        </div>
    </div>

    {{-- Conversation --}}
    <div class="krd-card" style="padding:24px;margin-bottom:16px;max-height:520px;overflow-y:auto;">
        @foreach($ticket->messages as $msg)
        <div style="display:flex;{{ $msg->sender_type === 'tenant' ? 'justify-content:flex-end;' : '' }}margin-bottom:16px;">
            <div style="max-width:75%;">
                <div style="font-size:11px;color:#A8A29E;margin-bottom:4px;{{ $msg->sender_type === 'tenant' ? 'text-align:right;' : '' }}">
                    {{ $msg->senderName() }} · {{ $msg->created_at->format('d M, g:i A') }}
                </div>
                <div style="padding:12px 16px;border-radius:10px;font-size:13px;line-height:1.6;{{ $msg->sender_type === 'tenant' ? 'background:#1C1917;color:#fff;' : 'background:#F5F5F4;color:#1C1917;' }}">
                    {!! $msg->renderedMessage() !!}
                </div>
                @if($msg->attachments->isNotEmpty())
                <div style="margin-top:6px;display:flex;flex-wrap:wrap;gap:6px;{{ $msg->sender_type === 'tenant' ? 'justify-content:flex-end;' : '' }}">
                    @foreach($msg->attachments as $att)
                    <a href="{{ Storage::url($att->file_path) }}" target="_blank" style="font-size:11px;background:#F5F5F4;padding:4px 10px;border-radius:6px;color:#57534E;text-decoration:none;">
                        📎 {{ $att->file_name }} ({{ $att->humanSize() }})
                    </a>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
        @endforeach
    </div>

    {{-- Reply box --}}
    @if($ticket->status !== 'closed')
    <div class="krd-card" style="padding:20px;">
        <textarea wire:model="reply" class="krd-input @error('reply') krd-input-error @enderror" rows="3" placeholder="Type your reply..."></textarea>
        @error('reply') <span class="krd-input-error-msg">{{ $message }}</span> @enderror
        <div style="display:flex;align-items:center;justify-content:space-between;margin-top:10px;flex-wrap:wrap;gap:10px;">
            <input wire:model="attachments" type="file" multiple style="font-size:12px;" />
            <button wire:click="sendReply" wire:loading.attr="disabled" class="krd-btn krd-btn-primary krd-btn-sm">
                <span wire:loading.remove wire:target="sendReply">Send Reply</span>
                <span wire:loading wire:target="sendReply">Sending...</span>
            </button>
        </div>
    </div>
    @endif

    {{-- Rating --}}
    @if(in_array($ticket->status, ['resolved', 'closed']) && !$ticket->rated_at)
    <div class="krd-card" style="padding:20px;margin-top:16px;background:#F5F3FF;border-color:#DDD6FE;">
        @if(!$showRatingForm)
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;">
            <div style="font-size:13px;color:#5B21B6;font-weight:500;">How was your support experience?</div>
            <button wire:click="showRating" class="krd-btn krd-btn-primary krd-btn-sm">Rate This Ticket</button>
        </div>
        @else
        <div style="font-size:13px;font-weight:600;color:#1C1917;margin-bottom:12px;">Rate your experience</div>
        <div style="display:flex;gap:6px;margin-bottom:14px;" x-data="{ val: {{ $ratingValue }} }">
            @for($i = 1; $i <= 5; $i++)
            <button type="button" x-on:click="val = {{ $i }}; $wire.set('ratingValue', {{ $i }})"
                style="background:none;border:none;cursor:pointer;font-size:28px;line-height:1;"
                :style="val >= {{ $i }} ? 'color:#F59E0B;' : 'color:#D6D3D1;'">★</button>
            @endfor
        </div>
        <textarea wire:model="ratingComment" class="krd-input" rows="2" placeholder="Any additional feedback? (optional)"></textarea>
        <button wire:click="submitRating" class="krd-btn krd-btn-primary krd-btn-sm" style="margin-top:10px;">Submit Rating</button>
        @endif
    </div>
    @endif

    @if($ticket->rated_at)
    <div class="krd-card" style="padding:16px 20px;margin-top:16px;">
        <div style="font-size:12px;color:#78716C;">Your rating: <span style="color:#F59E0B;">{{ str_repeat('★', $ticket->rating) }}{{ str_repeat('☆', 5 - $ticket->rating) }}</span></div>
        @if($ticket->rating_comment)<div style="font-size:12px;color:#57534E;margin-top:4px;">"{{ $ticket->rating_comment }}"</div>@endif
    </div>
    @endif
</div>