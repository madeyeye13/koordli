<div>
<style>
    .chat-wrap { max-width: 600px; margin: 0 auto; }
    .chat-window { background:#fff; border:1px solid #E7E5E4; border-radius:12px; overflow:hidden; display:flex; flex-direction:column; height:560px; }
    .chat-header { background:#1C1917; padding:16px 20px; display:flex; align-items:center; gap:10px; }
    .chat-header-dot { width:8px; height:8px; border-radius:50%; background:#10B981; }
    .chat-body { flex:1; overflow-y:auto; padding:20px; display:flex; flex-direction:column; gap:14px; }
    .chat-msg-bot { align-self:flex-start; max-width:80%; }
    .chat-msg-tenant { align-self:flex-end; max-width:80%; }
    .chat-bubble-bot { background:#F5F5F4 !important; color:#1C1917 !important; padding:12px 16px; border-radius:12px 12px 12px 2px; font-size:13px; line-height:1.7; white-space:pre-line; }
    .chat-bubble-tenant { background:#7C3AED !important; color:#fff !important; padding:12px 16px; border-radius:12px 12px 2px 12px; font-size:13px; line-height:1.7; }
    .chat-bubble-agent { background:#EDE9FE !important; color:#4C1D95 !important; padding:12px 16px; border-radius:12px 12px 12px 2px; font-size:13px; line-height:1.7; }
    .chat-bubble-system { background:#F5F5F4 !important; color:#78716C !important; font-size:11px; padding:4px 12px; border-radius:12px; display:inline-block; }
    .chat-quick-replies { display:flex; flex-wrap:wrap; gap:8px; padding:0 20px 16px; }
    .chat-quick-btn { background:#F5F3FF; color:#7C3AED; border:1px solid #DDD6FE; padding:8px 14px; border-radius:20px; font-size:12.5px; font-weight:500; cursor:pointer; transition:background 150ms; }
    .chat-quick-btn:hover { background:#EDE9FE; }
    .chat-input-row { padding:14px 16px; border-top:1px solid #E7E5E4; display:flex; gap:8px; }
    .chat-input { flex:1; border:1px solid #E7E5E4; border-radius:8px; padding:10px 14px; font-size:13px; outline:none; color:#1C1917 !important; background:#fff !important; }
    .chat-input:focus { border-color:#7C3AED; }
    .chat-send-btn { background:#7C3AED; color:#fff; border:none; border-radius:8px; padding:10px 18px; font-size:13px; font-weight:600; cursor:pointer; }
    [x-cloak] { display:none !important; }
</style>

<div class="chat-wrap">
    <div style="margin-bottom:16px;">
        <a href="{{ route('tenant.dashboard') }}" wire:navigate style="color:#A8A29E;text-decoration:none;font-size:13px;">← Back to Dashboard</a>
    </div>

    <div class="chat-window">
        <div class="chat-header">
            <span class="chat-header-dot"></span>
            <span style="color:#fff;font-size:14px;font-weight:600;">Koordli Support</span>
        </div>

        <div class="chat-body" id="chat-scroll"
            @if($stage === 'active' || $stage === 'waiting')
            wire:ignore
            x-data="tenantChatWidget(@js($ticket->uuid))"
            @endif
        >
            @foreach($ticket->messages as $msg)
                @if($msg->sender_type === 'system')
                <div style="text-align:center;margin:4px 0;">
                    <span class="chat-bubble-system">{{ $msg->message }}</span>
                </div>
                @elseif($msg->sender_type === 'tenant')
                <div class="chat-msg-tenant">
                    <div class="chat-bubble-tenant">{{ $msg->message }}</div>
                </div>
                @elseif($msg->sender_type === 'agent')
                <div class="chat-msg-bot">
                    <div style="font-size:11px;color:#A8A29E;margin-bottom:3px;">{{ $msg->senderName() }}</div>
                    <div class="chat-bubble-agent">{!! $msg->renderedMessage(true) !!}</div>
                </div>
                @else
                <div class="chat-msg-bot">
                    <div class="chat-bubble-bot"
                        x-data="{ full: @js($msg->message), shown: '' , idx: 0 }"
                        x-init="
                            if ({{ $loop->last ? 'true' : 'false' }}) {
                                let typer = setInterval(() => {
                                    shown = full.slice(0, idx);
                                    idx++;
                                    if (idx > full.length) clearInterval(typer);
                                }, 12);
                            } else { shown = full; }
                        "
                        x-text="shown"></div>
                </div>
                @endif
            @endforeach
        </div>

        {{-- Menu options --}}
        @if($stage === 'menu')
        <div class="chat-quick-replies">
            <button wire:click="selectOption('billing')" class="chat-quick-btn">💳 Billing / Plan Question</button>
            <button wire:click="selectOption('howto')" class="chat-quick-btn">❓ How do I...?</button>
            <button wire:click="selectOption('broken')" class="chat-quick-btn">🐞 Something's broken</button>
            <button wire:click="selectOption('human')" class="chat-quick-btn">🧑‍💼 Talk to a human</button>
        </div>
        @endif

        {{-- FAQ free-text search --}}
        @if($stage === 'typing_faq')
        <div style="padding:0 20px 12px;">
            <button wire:click="selectOption('human')" class="chat-quick-btn">🧑‍💼 Talk to a human instead</button>
        </div>
        <div class="chat-input-row">
            <input wire:model="userInput" wire:keydown.enter="sendMessage" type="text" class="chat-input" placeholder="Type your question..." />
            <button wire:click="sendMessage" class="chat-send-btn">Send</button>
        </div>
        @endif

        {{-- After FAQ answer, offer menu again --}}
        @if($stage === 'typing_faq' && $ticket->messages->where('sender_type', 'bot')->count() > 1)
        <div style="padding:0 20px 12px;display:flex;gap:8px;">
            <button wire:click="backToMenu" class="chat-quick-btn">↩ Back to Menu</button>
        </div>
        @endif

        {{-- Waiting for agent --}}
        @if($stage === 'waiting')
        <div style="padding:20px;text-align:center;border-top:1px solid #E7E5E4;"
            x-data="{
                remaining: 180,
                timer: null,
                init() {
                    this.timer = setInterval(() => {
                        if (this.remaining > 0) this.remaining--;
                    }, 1000);
                },
                display() {
                    const m = Math.floor(this.remaining / 60);
                    const s = this.remaining % 60;
                    return m + ':' + s.toString().padStart(2, '0');
                }
            }">
            <div style="font-size:13px;color:#78716C;margin-bottom:8px;">⏳ Waiting for an agent to join...</div>
            <div style="font-size:24px;font-weight:700;color:#7C3AED;" x-text="display()"></div>
            <div style="font-size:11px;color:#A8A29E;margin-top:4px;">estimated wait time</div>
        </div>
        @endif

        {{-- Live chat — connected to agent --}}
        @if($stage === 'active')
        <div style="padding:6px 20px;height:18px;">
            <span id="tenant-typing-indicator" style="display:none;font-size:11px;color:#A8A29E;font-style:italic;">Agent is typing...</span>
        </div>
        <div class="chat-input-row">
            <input wire:model="userInput" wire:keydown.enter="sendLiveMessage"
                onkeyup="if(window.__tenantPresenceChannel) window.__tenantPresenceChannel.whisper('tenant-typing', {})"
                type="text" class="chat-input" placeholder="Type a message..." />
            <button wire:click="sendLiveMessage" class="chat-send-btn">Send</button>
        </div>
        <div style="padding:8px 20px 4px;text-align:center;">
            <button wire:click="endChatByTenant" style="background:none;border:none;color:#A8A29E;font-size:11px;cursor:pointer;text-decoration:underline;">End Chat</button>
        </div>
        @endif

        {{-- No agent available --}}
        @if($stage === 'no_agent')
        <div class="chat-input-row">
            <input wire:model="userInput" wire:keydown.enter="leaveMessage" type="text" class="chat-input" placeholder="Leave a message describing your issue..." />
            <button wire:click="leaveMessage" class="chat-send-btn">Send</button>
        </div>
        @endif

        {{-- Ended --}}
        @if($stage === 'ended')
        <div style="padding:20px;text-align:center;border-top:1px solid #E7E5E4;">
            <a href="{{ route('tenant.support.tickets.show', $ticket->uuid) }}" wire:navigate class="krd-btn krd-btn-primary krd-btn-sm">
                View Ticket #{{ strtoupper(substr($ticket->uuid, 0, 8)) }} →
            </a>
        </div>
        @endif
    </div>
</div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('tenantChatWidget', (ticketUuid) => ({
        init() {
            this.$el.scrollTop = this.$el.scrollHeight;

            window.Echo.private('support-ticket.' + ticketUuid).listen('.message.sent', (e) => {
                const wrap = document.createElement('div');

                if (e.sender_type === 'system') {
                    wrap.style.textAlign = 'center';
                    wrap.style.margin = '4px 0';
                    const span = document.createElement('span');
                    span.className = 'chat-bubble-system';
                    span.textContent = e.message;
                    wrap.appendChild(span);
                } else if (e.sender_type === 'tenant') {
                    wrap.className = 'chat-msg-tenant';
                    const bubble = document.createElement('div');
                    bubble.className = 'chat-bubble-tenant';
                    bubble.textContent = e.message;
                    wrap.appendChild(bubble);
                } else if (e.sender_type === 'agent') {
                    wrap.className = 'chat-msg-bot';
                    const label = document.createElement('div');
                    label.style.cssText = 'font-size:11px;color:#A8A29E;margin-bottom:3px;';
                    label.textContent = e.sender_name;
                    const bubble = document.createElement('div');
                    bubble.className = 'chat-bubble-agent';
                    bubble.innerHTML = e.rendered;
                    wrap.appendChild(label);
                    wrap.appendChild(bubble);
                } else {
                    wrap.className = 'chat-msg-bot';
                    const bubble = document.createElement('div');
                    bubble.className = 'chat-bubble-bot';
                    bubble.innerHTML = e.rendered;
                    wrap.appendChild(bubble);
                }

                this.$el.appendChild(wrap);
                this.$el.scrollTop = this.$el.scrollHeight;

                if (e.sender_type !== 'tenant') {
                    this.$wire.call('markCurrentChatRead');
                }
            });

            window.__tenantPresenceChannel = window.Echo.join('support-ticket.' + ticketUuid)
                .here((users) => {
                    if (users.some(u => u.type === 'agent')) this.$wire.call('agentJoined');
                })
                .joining((user) => {
                    if (user.type === 'agent') this.$wire.call('agentJoined');
                })
                .listenForWhisper('agent-typing', () => {
                    const el = document.getElementById('tenant-typing-indicator');
                    if (el) el.style.display = 'block';
                    clearTimeout(window.__tenantTypingTimeout);
                    window.__tenantTypingTimeout = setTimeout(() => {
                        if (el) el.style.display = 'none';
                    }, 2500);
                });
        }
    }));
});
</script>