<div>
<style>
    .chat-wrap { max-width: 600px; margin: 0 auto; }
    .chat-window { background:#fff; border:1px solid #E7E5E4; border-radius:12px; overflow:hidden; display:flex; flex-direction:column; height:560px; }
    .chat-header { background:#1C1917; padding:16px 20px; display:flex; align-items:center; gap:10px; }
    .chat-header-dot { width:8px; height:8px; border-radius:50%; background:#10B981; }
    .chat-body { flex:1; overflow-y:auto; padding:20px; display:flex; flex-direction:column; gap:14px; }
    .chat-msg-bot { align-self:flex-start; max-width:80%; }
    .chat-msg-tenant { align-self:flex-end; max-width:80%; }
    .chat-bubble-bot { background:#F5F5F4; color:#1C1917; padding:12px 16px; border-radius:12px 12px 12px 2px; font-size:13px; line-height:1.7; white-space:pre-line; }
    .chat-bubble-tenant { background:#7C3AED; color:#fff; padding:12px 16px; border-radius:12px 12px 2px 12px; font-size:13px; line-height:1.7; }
    .chat-quick-replies { display:flex; flex-wrap:wrap; gap:8px; padding:0 20px 16px; }
    .chat-quick-btn { background:#F5F3FF; color:#7C3AED; border:1px solid #DDD6FE; padding:8px 14px; border-radius:20px; font-size:12.5px; font-weight:500; cursor:pointer; transition:background 150ms; }
    .chat-quick-btn:hover { background:#EDE9FE; }
    .chat-input-row { padding:14px 16px; border-top:1px solid #E7E5E4; display:flex; gap:8px; }
    .chat-input { flex:1; border:1px solid #E7E5E4; border-radius:8px; padding:10px 14px; font-size:13px; outline:none; }
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

        <div class="chat-body" id="chat-scroll" x-data="{
                init() {
                    this.$nextTick(() => { this.$el.scrollTop = this.$el.scrollHeight; });
                }
            }"
            x-init="init()"
            x-on:livewire:updated="$nextTick(() => { $el.scrollTop = $el.scrollHeight; })">
            @foreach($ticket->messages as $msg)
                @if($msg->sender_type === 'bot')
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
                @else
                <div class="chat-msg-tenant">
                    <div class="chat-bubble-tenant">{{ $msg->message }}</div>
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

        {{-- After an answer: help / escalate --}}
        @if(in_array($stage, ['menu']) && $ticket->messages->count() > 1)
        {{-- (kept minimal; quick replies above cover re-asking too) --}}
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
        <div style="padding:20px;text-align:center;border-top:1px solid #E7E5E4;">
            <div style="font-size:13px;color:#78716C;margin-bottom:8px;">⏳ Waiting for an agent to join...</div>
            <div style="font-size:24px;font-weight:700;color:#7C3AED;">~3 min</div>
            <div style="font-size:11px;color:#A8A29E;margin-top:4px;">estimated wait time</div>
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