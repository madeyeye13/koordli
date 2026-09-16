<div>
    <div style="margin-bottom:20px;">
        <div style="margin-bottom:8px;">
            <a href="{{ route('platform.support.tickets') }}" wire:navigate style="color:#A8A29E;text-decoration:none;font-size:13px;">← Back to Inbox</a>
        </div>
        <div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:12px;">
            <div>
                <h2 class="krd-heading-3" style="color:#1C1917;">{{ $ticket->subject }}</h2>
                <div style="font-size:12px;color:#A8A29E;margin-top:4px;">
                    #{{ strtoupper(substr($ticket->uuid, 0, 8)) }} · {{ $ticket->tenant->name }} · Opened {{ $ticket->created_at->format('d M Y') }}
                </div>
            </div>
            <div style="display:flex;gap:8px;flex-wrap:wrap;">
                <span class="krd-badge" style="background:{{ $ticket->priorityColor() }}1a;color:{{ $ticket->priorityColor() }};">{{ ucfirst($ticket->priority) }}</span>
                <span class="krd-badge" style="background:{{ $ticket->statusColor() }}1a;color:{{ $ticket->statusColor() }};">{{ $ticket->statusLabel() }}</span>
            </div>
        </div>
    </div>

    <div class="platform-ticket-detail-grid" style="display:grid;grid-template-columns:1fr 280px;gap:20px;align-items:start;" id="platform-ticket-grid">
        <div>
            {{-- Conversation --}}
            <div class="krd-card" id="platform-chat-scroll" wire:key="platform-chat-scroll-{{ $ticket->id }}-{{ $ticket->assigned_agent_id ?? 'unassigned' }}" style="padding:24px;margin-bottom:16px;max-height:480px;overflow-y:auto;background:#fff !important;"
                @if($ticket->source === 'chat' && $ticket->assigned_agent_id)
                wire:ignore
                x-data="platformChatWidget(@js($ticket->uuid))"
                @endif
            >
                @foreach($ticket->messages as $msg)
                    @if($msg->sender_type === 'system')
                    <div style="text-align:center;margin:8px 0;">
                        <span style="font-size:11px;color:#78716C;background:#F5F5F4;padding:4px 12px;border-radius:12px;display:inline-block;">{{ $msg->message }}</span>
                    </div>
                    @continue
                    @endif
                    <div style="display:flex;{{ $msg->sender_type === 'agent' ? 'justify-content:flex-end;' : '' }}margin-bottom:16px;">
                        <div class="platform-ticket-message-bubble" style="max-width:75%;">
                            <div style="font-size:11px;color:#A8A29E !important;margin-bottom:4px;{{ $msg->sender_type === 'agent' ? 'text-align:right;' : '' }}">
                                {{ $msg->senderName() }} · {{ $msg->created_at->format('d M, g:i A') }}
                            </div>
                            <div style="padding:12px 16px;border-radius:10px;font-size:13px;line-height:1.6;{{ $msg->sender_type === 'agent' ? 'background:#7C3AED !important;color:#fff !important;' : 'background:#F5F5F4 !important;color:#1C1917 !important;' }}">
                                {!! $msg->renderedMessage($msg->sender_type === 'agent') !!}
                            </div>
                            @if($msg->attachments->isNotEmpty())
                            <div style="margin-top:6px;display:flex;flex-wrap:wrap;gap:6px;{{ $msg->sender_type === 'agent' ? 'justify-content:flex-end;' : '' }}">
                                @foreach($msg->attachments as $att)
                                <a href="{{ Storage::url($att->file_path) }}" target="_blank" style="font-size:11px;background:#F5F5F4;padding:4px 10px;border-radius:6px;color:#57534E;text-decoration:none;">📎 {{ $att->file_name }}</a>
                                @endforeach
                            </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            @if($ticket->status !== 'closed')
            @if($ticket->source === 'chat' && $ticket->chatSession && $ticket->chatSession->status === 'active')
            <div style="margin-bottom:8px;text-align:right;">
                <button wire:click="endChatByAgent" wire:confirm="End this chat with the tenant?" class="krd-btn krd-btn-sm" style="background:#FEE2E2;color:#DC2626;">End Chat</button>
            </div>
            @endif
            <div class="krd-card" style="padding:20px;">
               @if($ticket->source === 'chat')
                <div style="height:16px;margin-bottom:4px;">
                    <span id="platform-typing-indicator" style="display:none;font-size:11px;color:#A8A29E;font-style:italic;">Tenant is typing...</span>
                </div>
                @endif
                <textarea wire:model="reply" onkeyup="if(window.__platformPresenceChannel) window.__platformPresenceChannel.whisper('agent-typing', {})" class="krd-input @error('reply') krd-input-error @enderror" rows="3" placeholder="Type your reply..."></textarea>
                @error('reply') <span class="krd-input-error-msg">{{ $message }}</span> @enderror
                <div style="display:flex;align-items:center;justify-content:space-between;margin-top:10px;flex-wrap:wrap;gap:10px;">
                    <div style="display:flex;align-items:center;gap:10px;position:relative;" x-data="emojiPicker('reply')">
                        <button type="button" x-on:click="toggle()" style="background:none;border:1px solid #E7E5E4;border-radius:6px;width:34px;height:34px;cursor:pointer;font-size:16px;">😊</button>
                        <div x-show="open" x-cloak x-on:click.outside="open = false"
                            style="position:absolute;bottom:40px;left:0;background:#fff;border:1px solid #E7E5E4;border-radius:8px;box-shadow:0 8px 24px rgba(0,0,0,0.12);padding:10px;width:260px;max-height:200px;overflow-y:auto;z-index:50;display:grid;grid-template-columns:repeat(8, 1fr);gap:4px;">
                            <template x-for="emoji in emojis" :key="emoji">
                                <button type="button" x-on:click="pick(emoji)" style="background:none;border:none;cursor:pointer;font-size:18px;padding:4px;border-radius:4px;" x-text="emoji" onmouseover="this.style.background='#F5F5F4'" onmouseout="this.style.background='none'"></button>
                            </template>
                        </div>
                        <input wire:model="attachments" type="file" multiple style="font-size:12px;" />
                    </div>
                    <button wire:click="sendReply" wire:loading.attr="disabled" class="krd-btn krd-btn-primary krd-btn-sm">
                        <span wire:loading.remove wire:target="sendReply">Send Reply</span>
                        <span wire:loading wire:target="sendReply">Sending...</span>
                    </button>
                </div>
            </div>
            @endif
        </div>

        <div class="platform-ticket-actions" style="display:flex;flex-direction:column;gap:12px;position:sticky;top:80px;" id="platform-ticket-actions">
            <div class="krd-card" style="padding:20px;">
                <div class="krd-label" style="margin-bottom:12px;">Assignment</div>
                @if($ticket->assignedAgent)
                <div style="font-size:13px;font-weight:600;color:#1C1917;margin-bottom:10px;">{{ $ticket->assignedAgent->platformUser->name }}</div>
                <button wire:click="openHandoff" class="krd-btn krd-btn-secondary krd-btn-sm" style="width:100%;">Hand Off to Another Agent</button>
                @else
                <button wire:click="claimTicket" class="krd-btn krd-btn-primary krd-btn-sm" style="width:100%;">Claim This Ticket</button>
                @endif
            </div>

            <div class="krd-card" style="padding:20px;">
                <div class="krd-label" style="margin-bottom:12px;">Status</div>
                <div style="display:flex;flex-direction:column;gap:6px;">
                    @foreach(['open' => 'Open', 'in_progress' => 'In Progress', 'resolved' => 'Resolved', 'closed' => 'Closed'] as $val => $label)
                    <button wire:click="updateStatus('{{ $val }}')"
                        class="krd-btn krd-btn-sm"
                        style="{{ $ticket->status === $val ? 'background:#7C3AED;color:#fff;' : 'background:#F5F5F4;color:#57534E;' }}">
                        {{ $label }}
                    </button>
                    @endforeach
                </div>
            </div>

            <div class="krd-card" style="padding:20px;">
                <div class="krd-label" style="margin-bottom:10px;">Danger Zone</div>
                <div style="display:flex;flex-direction:column;gap:8px;">
                    <button wire:click="archiveTicket" class="krd-btn krd-btn-secondary krd-btn-sm" style="width:100%;">Archive (Close) Ticket</button>
                    <button wire:click="confirmDelete" class="krd-btn krd-btn-sm" style="width:100%;background:#FEE2E2;color:#DC2626;">Delete Ticket Permanently</button>
                </div>
            </div>

            @if($ticket->rated_at)
            <div class="krd-card" style="padding:20px;">
                <div class="krd-label" style="margin-bottom:8px;">Customer Rating</div>
                <div style="color:#F59E0B;font-size:16px;">{{ str_repeat('★', $ticket->rating) }}{{ str_repeat('☆', 5 - $ticket->rating) }}</div>
                @if($ticket->rating_comment)<div style="font-size:12px;color:#78716C;margin-top:6px;">"{{ $ticket->rating_comment }}"</div>@endif
            </div>
            @endif

            @if($ticket->assignmentHistory->isNotEmpty())
            <div class="krd-card" style="padding:20px;">
                <div class="krd-label" style="margin-bottom:10px;">History</div>
                @foreach($ticket->assignmentHistory as $h)
                <div style="font-size:11px;color:#78716C;padding:6px 0;border-bottom:1px solid #F5F5F4;">
                    → {{ $h->toAgent->platformUser->name }} <span style="color:#A8A29E;">({{ $h->created_at->diffForHumans() }})</span>
                    @if($h->reason)<div style="color:#A8A29E;">{{ $h->reason }}</div>@endif
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>

    @if($showDeleteModal)
    <div style="position:fixed;inset:0;background:rgba(0,0,0,0.4);z-index:60;display:flex;align-items:center;justify-content:center;padding:16px;">
        <div style="background:#fff;border-radius:8px;padding:24px;max-width:400px;width:100%;">
            <h3 style="font-size:16px;font-weight:600;color:#1C1917;margin-bottom:8px;">Delete This Ticket?</h3>
            <p style="font-size:13px;color:#78716C;margin-bottom:24px;">This permanently deletes the ticket and its entire conversation history. This cannot be undone.</p>
            <div style="display:flex;gap:10px;">
                <button wire:click="deleteTicket" class="krd-btn krd-btn-danger" style="flex:1;">Yes, Delete Forever</button>
                <button wire:click="$set('showDeleteModal', false)" class="krd-btn krd-btn-secondary" style="flex:1;">Cancel</button>
            </div>
        </div>
    </div>
    @endif

    @if($showHandoffModal)
    <div style="position:fixed;inset:0;background:rgba(0,0,0,0.4);z-index:60;display:flex;align-items:center;justify-content:center;padding:16px;">
        <div style="background:#fff;border-radius:8px;padding:24px;max-width:420px;width:100%;">
            <h3 style="font-size:16px;font-weight:600;color:#1C1917;margin-bottom:16px;">Hand Off Ticket</h3>
            <div class="krd-input-group">
                <label class="krd-label-text">Assign to</label>
                <x-ui.dropdown wire="handoffAgentId" placeholder="Select agent"
                    selected="{{ $handoffAgentId ? ($otherAgents->firstWhere('id', $handoffAgentId)?->platformUser?->name ?? 'Select agent') : 'Select agent' }}">
                    @foreach($otherAgents as $a)
                    <div class="krd-dropdown-option {{ $handoffAgentId == $a->id ? 'selected' : '' }}" x-on:click="select(@js($a->platformUser->name), {{ $a->id }})">{{ $a->platformUser->name }}</div>
                    @endforeach
                </x-ui.dropdown>
                @error('handoffAgentId') <span class="krd-input-error-msg">{{ $message }}</span> @enderror
            </div>
            <div class="krd-input-group" style="margin-bottom:0;">
                <label class="krd-label-text">Reason <span style="color:#A8A29E;font-weight:400;">(optional)</span></label>
                <input wire:model="handoffReason" type="text" class="krd-input" placeholder="e.g. Billing question, needs finance team" />
            </div>
            <div style="display:flex;gap:10px;margin-top:20px;">
                <button wire:click="handoff" class="krd-btn krd-btn-primary" style="flex:1;">Confirm Handoff</button>
                <button wire:click="$set('showHandoffModal', false)" class="krd-btn krd-btn-secondary" style="flex:1;">Cancel</button>
            </div>
        </div>
    </div>
    @endif
</div>

<style>
@media (max-width: 768px) { #platform-ticket-grid { grid-template-columns: 1fr !important; } #platform-ticket-actions { position: static !important; } }
</style>

