<div x-data="{
        selectModeLocal: false,
        selectedIds: [],
        canDeleteEveryoneFlag: false,
        recalcCanDeleteEveryone() {
            if (this.selectedIds.length === 0) { this.canDeleteEveryoneFlag = false; return; }
            var self = this;
            this.canDeleteEveryoneFlag = this.selectedIds.every(function(id) {
                var row = self.$el.querySelector('[data-message-id=\'' + id + '\']');
                return row && row.dataset.isMine === '1' && row.dataset.canDeleteEveryone === '1';
            });
        },
        toggleId(id) {
            var idx = this.selectedIds.indexOf(id);
            if (idx > -1) { this.selectedIds.splice(idx, 1); } else { this.selectedIds.push(id); }
            this.recalcCanDeleteEveryone();
        },
        init() {
            var self = this;
            window.Echo.private('conversation.{{ $conversation->uuid }}').listen('.conversation.deleted', function(e) {
                window.location.href = '/events/' + e.event_slug;
            });
            window.__convToggleId = function(id) { self.toggleId(id); };
            this.$watch('selectModeLocal', function(value) {
                window.__convSelectModeActive = value;
                document.querySelectorAll('.conv-msg-row input[type=checkbox]').forEach(function(cb) {
                    cb.style.display = value ? '' : 'none';
                });
            });
        }
    }">
    <div style="margin-bottom:20px;">
        <div style="margin-bottom:8px;">
            <a href="{{ route('tenant.events.show', $conversation->event->slug) }}" wire:navigate style="color:#A8A29E;text-decoration:none;font-size:13px;">← Back to {{ $conversation->event->name }}</a>
        </div>
        <div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:12px;">
            <div>
                <h2 class="krd-heading-3" style="color:#1C1917;">
                    {{ $conversation->name ?: ($conversation->type === 'direct' ? 'Direct Message' : 'Conversation') }}
                </h2>
                <div style="font-size:12px;color:#A8A29E;margin-top:2px;">{{ ucfirst($conversation->type) }} · {{ $conversation->participants->count() }} participant(s)</div>
            </div>
            <div style="display:flex;gap:8px;">
                <button x-on:click="selectModeLocal = !selectModeLocal; selectedIds = []; canDeleteEveryoneFlag = false;" class="krd-btn krd-btn-secondary krd-btn-sm">
                    <span x-text="selectModeLocal ? 'Cancel Select' : 'Select'"></span>
                </button>
                <button wire:click="showAddParticipant" class="krd-btn krd-btn-secondary krd-btn-sm">+ Add Participant</button>
                @if($isAdmin)
                <button wire:click="confirmDeleteConversation" class="krd-btn krd-btn-sm" style="background:#FEE2E2;color:#DC2626;">Delete Conversation</button>
                @endif
            </div>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 260px;gap:20px;align-items:start;" id="conv-grid">
        <div>
            <div class="krd-card" id="conv-scroll" style="padding:24px;margin-bottom:16px;max-height:480px;overflow-y:auto;"
                wire:ignore
                x-data="conversationWidget(@js($conversation->uuid))"
            >
                @forelse($visibleMessages as $msg)
                @php $isMe = $msg->sender_type === 'tenant_user' && $msg->sender_id === auth()->id(); @endphp
                <div style="display:flex;{{ $isMe ? 'justify-content:flex-end;' : '' }}margin-bottom:16px;gap:8px;" class="conv-msg-row" data-message-id="{{ $msg->id }}" data-is-mine="{{ $isMe ? '1' : '0' }}" data-can-delete-everyone="{{ $msg->canDeleteForEveryone('tenant_user', auth()->id()) ? '1' : '0' }}">
                    <input type="checkbox" x-show="selectModeLocal" x-cloak x-on:click="toggleId({{ $msg->id }})" style="accent-color:#7C3AED;margin-top:4px;flex-shrink:0;" />
                    <div style="max-width:75%;">
                        <div style="font-size:11px;color:#A8A29E;margin-bottom:4px;{{ $isMe ? 'text-align:right;' : '' }}">
                            {{ $msg->senderName() }} · {{ $msg->created_at->format('d M, g:i A') }}
                            @if(!$msg->deleted_at)
                            <button wire:click="replyToMessage({{ $msg->id }})" style="background:none;border:none;color:#7C3AED;cursor:pointer;font-size:10px;margin-left:6px;">↩ Reply</button>
                            @endif
                        </div>
                        @if($msg->replyTo)
                        <div style="font-size:11px;color:#78716C;background:#F5F5F4;border-left:2px solid #7C3AED;padding:4px 8px;margin-bottom:4px;border-radius:4px;{{ $isMe ? 'margin-left:auto;' : '' }}">
                            <strong>{{ $msg->replyTo->senderName() }}</strong>: {{ \Illuminate\Support\Str::limit($msg->replyTo->body, 60) }}
                        </div>
                        @endif
                        @if(trim($msg->body) !== '' || $msg->deleted_at)
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
                @empty
                <div style="text-align:center;padding:32px 0;font-size:12px;color:#A8A29E;">No messages yet — start the conversation.</div>
                @endforelse
            </div>

            <div x-show="selectModeLocal && selectedIds.length > 0" x-cloak style="position:sticky;bottom:0;background:#1C1917;border-radius:8px;padding:12px 16px;margin-bottom:16px;display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap;">
                <span style="color:#fff;font-size:13px;" x-text="selectedIds.length + ' selected'"></span>
                <div style="display:flex;gap:8px;">
                    <button x-on:click="$wire.set('selectedMessageIds', selectedIds).then(function() { $wire.call('bulkDeleteForMe'); }); selectModeLocal = false; selectedIds = []; canDeleteEveryoneFlag = false;" class="krd-btn krd-btn-sm" style="background:#57534E;color:#fff;">Delete for Me</button>
                    <button x-show="canDeleteEveryoneFlag" x-on:click="$wire.set('selectedMessageIds', selectedIds).then(function() { $wire.call('bulkDeleteForEveryone'); }); selectModeLocal = false; selectedIds = []; canDeleteEveryoneFlag = false;" class="krd-btn krd-btn-sm" style="background:#EF4444;color:#fff;">Delete for Everyone</button>
                </div>
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
                <div style="height:16px;margin-bottom:4px;">
                    <span id="conv-typing-indicator" style="display:none;font-size:11px;color:#A8A29E;font-style:italic;"></span>
                </div>
                <div style="position:relative;" x-data="{
                    showMentions: false,
                    mentionQuery: '',
                    participants: {{ Js::from($conversation->participants->map(fn($p) => $p->resolveParticipant()?->name)->filter()->values()) }},
                    checkMention(e) {
                        var val = e.target.value;
                        var cursorPos = e.target.selectionStart;
                        var textBeforeCursor = val.slice(0, cursorPos);
                        var match = textBeforeCursor.match(/@(\w*)$/);
                        if (match) {
                            this.mentionQuery = match[1].toLowerCase();
                            this.showMentions = true;
                        } else {
                            this.showMentions = false;
                        }
                    },
                    filteredParticipants() {
                        var q = this.mentionQuery;
                        return this.participants.filter(function(p) { return p.toLowerCase().includes(q); });
                    },
                    pickMention(name) {
                        var val = $wire.get('body');
                        val = val.replace(/@(\w*)$/, '@' + name + ' ');
                        $wire.set('body', val);
                        this.showMentions = false;
                    }
                }">
                    <div x-show="showMentions" x-cloak style="position:absolute;bottom:100%;left:0;background:#fff;border:1px solid #E7E5E4;border-radius:8px;box-shadow:0 8px 24px rgba(0,0,0,0.12);width:220px;max-height:160px;overflow-y:auto;z-index:50;margin-bottom:4px;">
                        <template x-for="name in filteredParticipants()" :key="name">
                            <div x-on:click="pickMention(name)" style="padding:8px 12px;font-size:13px;cursor:pointer;" x-text="'@' + name" onmouseover="this.style.background='#F5F3FF'" onmouseout="this.style.background='none'"></div>
                        </template>
                    </div>
                    <textarea spellcheck="true" lang="en" wire:model="body" x-on:input="checkMention($event)" onkeyup="window.__convWhisper && window.__convWhisper()" class="krd-input @error('body') krd-input-error @enderror" rows="3" placeholder="Type your message... use @ to mention someone"></textarea>
                </div>
                @error('body') <span class="krd-input-error-msg">{{ $message }}</span> @enderror
                <div style="display:flex;align-items:center;justify-content:space-between;margin-top:10px;flex-wrap:wrap;gap:10px;overflow-x:hidden;">
                    <div style="display:flex;align-items:center;gap:8px;position:relative;flex-wrap:wrap;max-width:100%;" x-data="emojiPicker('body')">
                        <button type="button" x-on:click="toggle()" style="background:none;border:1px solid #E7E5E4;border-radius:6px;width:34px;height:34px;cursor:pointer;font-size:16px;">😊</button>
                        <div x-show="open" x-cloak x-on:click.outside="open = false"
                            style="position:absolute;bottom:40px;left:0;background:#fff;border:1px solid #E7E5E4;border-radius:8px;box-shadow:0 8px 24px rgba(0,0,0,0.12);padding:10px;width:260px;max-height:200px;overflow-y:auto;z-index:50;display:grid;grid-template-columns:repeat(8, 1fr);gap:4px;">
                            <template x-for="emoji in emojis" :key="emoji">
                                <button type="button" x-on:click="pick(emoji)" style="background:none;border:none;cursor:pointer;font-size:18px;padding:4px;border-radius:4px;" x-text="emoji" onmouseover="this.style.background='#F5F5F4'" onmouseout="this.style.background='none'"></button>
                            </template>
                        </div>
                        <input wire:model="attachments" type="file" multiple id="conv-file-input" style="font-size:11px;max-width:110px;" />

                        <div x-data="voiceRecorder('conv-file-input')" style="display:flex;align-items:center;gap:8px;">
                            <button type="button" x-on:click="toggleRecording()" :disabled="uploading"
                                :style="recording ? 'background:#FEE2E2;border:1px solid #EF4444;color:#EF4444;' : 'background:none;border:1px solid #E7E5E4;color:#57534E;'"
                                style="border-radius:6px;width:34px;height:34px;cursor:pointer;font-size:16px;">
                                <span x-show="!uploading" x-text="recording ? '⏹' : '🎙️'"></span>
                                <span x-show="uploading" x-cloak style="display:inline-block;width:14px;height:14px;border:2px solid #7C3AED;border-top-color:transparent;border-radius:50%;animation:spin 0.6s linear infinite;"></span>
                            </button>
                            <span x-show="recording" x-cloak style="font-size:12px;color:#EF4444;font-weight:600;" x-text="formattedTime"></span>
                            <span x-show="uploading" x-cloak style="font-size:12px;color:#7C3AED;font-weight:500;">Sending voice note...</span>
                        </div>
                    </div>
                    <button wire:click="sendMessage" wire:loading.attr="disabled" class="krd-btn krd-btn-primary krd-btn-sm">
                        <span wire:loading.remove wire:target="sendMessage">Send</span>
                        <span wire:loading wire:target="sendMessage">Sending...</span>
                    </button>
                </div>
            </div>
        </div>

        <div style="display:flex;flex-direction:column;gap:12px;" id="conv-actions">
            <div class="krd-card" style="padding:20px;">
                <div class="krd-label" style="margin-bottom:10px;">Participants</div>
                @foreach($conversation->participants as $p)
                <div style="display:flex;align-items:center;justify-content:space-between;padding:6px 0;border-bottom:1px solid #F5F5F4;">
                    <div>
                        <div style="font-size:12.5px;font-weight:500;color:#1C1917;">{{ $participantNames[$p->id]['name'] ?? 'Unknown' }}</div>
                        <div style="font-size:10.5px;color:#A8A29E;">{{ str_replace('_', ' ', ucfirst($participantNames[$p->id]['type'] ?? '')) }}</div>
                    </div>
                    @if($isAdmin && !($p->participant_type === 'tenant_user' && $p->participant_id === auth()->id()))
                    <button wire:click="confirmRemoveParticipant({{ $p->id }})" style="background:none;border:none;color:#EF4444;cursor:pointer;font-size:16px;">×</button>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
    </div>

    @if($showAddParticipantForm)
    <div style="position:fixed;inset:0;background:rgba(0,0,0,0.4);z-index:60;display:flex;align-items:center;justify-content:center;padding:16px;">
        <div style="background:#fff;border-radius:8px;padding:24px;max-width:420px;width:100%;">
            <h3 style="font-size:16px;font-weight:600;color:#1C1917;margin-bottom:16px;">Add Participants</h3>
            @if($eligibleToAdd->isEmpty())
            <p style="font-size:13px;color:#A8A29E;">Everyone eligible for this event is already in this conversation.</p>
            @else
            <div style="max-height:240px;overflow-y:auto;display:flex;flex-direction:column;gap:8px;margin-bottom:16px;">
                @foreach($eligibleToAdd as $option)
                <label style="display:flex;align-items:center;gap:8px;font-size:13px;color:#1C1917;">
                    <input type="checkbox" wire:model="add_participant_keys" value="{{ $option['key'] }}" style="accent-color:#7C3AED;" />
                    {{ $option['label'] }}
                </label>
                @endforeach
            </div>
            @endif
            <div style="display:flex;gap:10px;">
                <button wire:click="addParticipants" class="krd-btn krd-btn-primary" style="flex:1;">Add Selected</button>
                <button wire:click="$set('showAddParticipantForm', false)" class="krd-btn krd-btn-secondary" style="flex:1;">Cancel</button>
            </div>
        </div>
    </div>
    @endif

    @if($showRemoveModal)
    <div style="position:fixed;inset:0;background:rgba(0,0,0,0.4);z-index:60;display:flex;align-items:center;justify-content:center;padding:16px;">
        <div style="background:#fff;border-radius:8px;padding:24px;max-width:400px;width:100%;">
            <h3 style="font-size:16px;font-weight:600;color:#1C1917;margin-bottom:8px;">Remove Participant?</h3>
            <p style="font-size:13px;color:#78716C;margin-bottom:24px;">They'll lose access to this conversation, but their message history stays intact.</p>
            <div style="display:flex;gap:10px;">
                <button wire:click="removeParticipant" class="krd-btn krd-btn-danger" style="flex:1;">Yes, Remove</button>
                <button wire:click="$set('showRemoveModal', false)" class="krd-btn krd-btn-secondary" style="flex:1;">Cancel</button>
            </div>
        </div>
    </div>
    @endif

    @if($showDeleteConvoModal)
    <div style="position:fixed;inset:0;background:rgba(0,0,0,0.4);z-index:60;display:flex;align-items:center;justify-content:center;padding:16px;">
        <div style="background:#fff;border-radius:8px;padding:24px;max-width:420px;width:100%;">
            <h3 style="font-size:16px;font-weight:600;color:#1C1917;margin-bottom:8px;">Delete This Conversation?</h3>
            <p style="font-size:13px;color:#78716C;margin-bottom:24px;">This permanently deletes the entire conversation and all its messages for every participant. This cannot be undone.</p>
            <div style="display:flex;gap:10px;">
                <button wire:click="deleteConversation" class="krd-btn krd-btn-danger" style="flex:1;">Yes, Delete Forever</button>
                <button wire:click="$set('showDeleteConvoModal', false)" class="krd-btn krd-btn-secondary" style="flex:1;">Cancel</button>
            </div>
        </div>
    </div>
    @endif
</div>

<style>
@media (max-width:768px) {
    #conv-grid { grid-template-columns:1fr !important; }
    #conv-scroll { max-width:100%; }
    #conv-scroll audio { max-width:160px !important; }
    #conv-scroll > div { max-width:88% !important; }
}
body { overflow-x:hidden; }
</style>