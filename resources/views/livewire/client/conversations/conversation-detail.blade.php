<div>
    <div style="margin-bottom:20px;">
        <a href="{{ route('client.conversations') }}" wire:navigate style="color:#A8A29E;text-decoration:none;font-size:13px;">← Back to Messages</a>
        <h2 style="font-size:18px;font-weight:600;color:#1C1917;margin-top:8px;">{{ $conversation->name ?: 'Direct Message' }}</h2>
        <div style="font-size:12px;color:#A8A29E;">{{ $conversation->event->name ?? '' }}</div>
    </div>

    <div class="krd-card" id="conv-scroll" style="padding:24px;margin-bottom:16px;max-height:500px;overflow-y:auto;"
        wire:ignore
        x-data="clientConversationWidget(@js($conversation->uuid))"
    >
        @foreach($conversation->messages as $msg)
        @php $isMe = $msg->sender_type === 'client' && $msg->sender_id === auth('client')->id(); @endphp
        <div style="display:flex;{{ $isMe ? 'justify-content:flex-end;' : '' }}margin-bottom:16px;">
            <div style="max-width:75%;">
                <div style="font-size:11px;color:#A8A29E;margin-bottom:4px;{{ $isMe ? 'text-align:right;' : '' }}">
                    {{ $msg->senderName() }} · {{ $msg->created_at->format('d M, g:i A') }}
                    <button wire:click="replyToMessage({{ $msg->id }})" style="background:none;border:none;color:#7C3AED;cursor:pointer;font-size:10px;margin-left:6px;">↩ Reply</button>
                </div>
                @if($msg->replyTo)
                <div style="font-size:11px;color:#78716C;background:#F5F5F4;border-left:2px solid #7C3AED;padding:4px 8px;margin-bottom:4px;border-radius:4px;{{ $isMe ? 'margin-left:auto;' : '' }}">
                    <strong>{{ $msg->replyTo->senderName() }}</strong>: {{ \Illuminate\Support\Str::limit($msg->replyTo->body, 60) }}
                </div>
                @endif
                @if(trim($msg->body) !== '')
                <div style="padding:12px 16px;border-radius:10px;font-size:13px;line-height:1.6;{{ $isMe ? 'background:#7C3AED;color:#fff;' : 'background:#F5F5F4;color:#1C1917;' }}">
                    {!! $msg->renderedBody() !!}
                </div>
                @endif
                @if($msg->attachments->isNotEmpty())
                <div style="margin-top:6px;display:flex;flex-direction:column;gap:6px;{{ $isMe ? 'align-items:flex-end;' : '' }}">
                    @foreach($msg->attachments as $att)
                        @if(str_starts_with($att->mime_type ?? '', 'audio/') || str_starts_with($att->file_name, 'voice-note-'))
                        <div style="display:flex;align-items:center;gap:8px;padding:10px 14px;border-radius:18px;{{ $isMe ? 'background:#7C3AED;' : 'background:#F5F5F4;' }}max-width:240px;">
                            <span style="font-size:16px;">🎙️</span>
                            <audio controls style="height:32px;flex:1;{{ $isMe ? 'filter:invert(1) hue-rotate(180deg);' : '' }}">
                                <source src="{{ Storage::url($att->file_path) }}" type="{{ $att->mime_type }}">
                            </audio>
                        </div>
                        @else
                        <a href="{{ Storage::url($att->file_path) }}" target="_blank" style="font-size:11px;background:#F5F5F4;padding:4px 10px;border-radius:6px;color:#57534E;text-decoration:none;">📎 {{ $att->file_name }}</a>
                        @endif
                    @endforeach
                </div>
                @endif
            </div>
        </div>
        @endforeach
    </div>

    <div class="krd-card" style="padding:20px;">
        @if($replyingToId)
        @php $replyMsg = \App\Models\Tenant\ConversationMessage::find($replyingToId); @endphp
        @if($replyMsg)
        <div style="display:flex;align-items:center;justify-content:space-between;background:#F5F3FF;border-left:3px solid #7C3AED;padding:8px 12px;border-radius:6px;margin-bottom:10px;">
            <div style="font-size:12px;color:#57534E;">Replying to <strong>{{ $replyMsg->senderName() }}</strong>: {{ \Illuminate\Support\Str::limit($replyMsg->body, 50) }}</div>
            <button wire:click="cancelReply" style="background:none;border:none;color:#A8A29E;cursor:pointer;font-size:16px;">×</button>
        </div>
        @endif
        @endif
        <textarea spellcheck="true" wire:model="body" class="krd-input @error('body') krd-input-error @enderror" rows="3" placeholder="Type your message..."></textarea>
        @error('body') <span class="krd-input-error-msg">{{ $message }}</span> @enderror
        <div style="display:flex;align-items:center;justify-content:space-between;margin-top:10px;flex-wrap:wrap;gap:10px;">
            <input wire:model="attachments" type="file" multiple style="font-size:12px;" />
            <button wire:click="sendMessage" wire:loading.attr="disabled" class="krd-btn krd-btn-primary krd-btn-sm">
                <span wire:loading.remove wire:target="sendMessage">Send</span>
                <span wire:loading wire:target="sendMessage">Sending...</span>
            </button>
        </div>
    </div>
</div>