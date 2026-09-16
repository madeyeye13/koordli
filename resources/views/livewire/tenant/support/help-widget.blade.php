<div>
    {{-- Topbar Help Button --}}
    <button wire:click="openChoices"
        class="tenant-help-button"
        style="position:relative;display:flex;align-items:center;gap:6px;background:#F5F3FF;color:#7C3AED;border:1px solid #DDD6FE;padding:7px 14px;border-radius:8px;cursor:pointer;font-size:13px;font-weight:500;transition:background 150ms;"
        onmouseover="this.style.background='#EDE9FE'" onmouseout="this.style.background='#F5F3FF'">
        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 015.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/>
        </svg>
        @if($activeTicketUuid)
            Live Chat
        @else
            Help
        @endif

        @if($unreadCount > 0)
        <span style="position:absolute;top:-6px;right:-6px;background:#EF4444;color:#fff;font-size:10px;font-weight:700;width:18px;height:18px;border-radius:50%;display:flex;align-items:center;justify-content:center;">
            {{ $unreadCount > 9 ? '9+' : $unreadCount }}
        </span>
        @endif
    </button>

    {{-- Background listener — keeps the badge live even while the tenant is on any other page --}}
    @if($activeTicketUuid)
    <div wire:ignore x-data="helpWidgetListener(@js($activeTicketUuid))"></div>
    @endif

    {{-- Choice Modal (only shown when there's no active chat already) --}}
    @if($showChoiceModal)
    <template x-teleport="body">
    <div style="position:fixed;inset:0;background:rgba(0,0,0,0.4);z-index:60;display:flex;align-items:center;justify-content:center;padding:16px;" wire:click.self="$set('showChoiceModal', false)">
        <div style="background:#fff;border-radius:12px;padding:28px;max-width:380px;width:100%;box-shadow:0 20px 60px rgba(0,0,0,0.25);">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px;">
                <h3 style="font-size:17px;font-weight:700;color:#1C1917;">How can we help?</h3>
                <button wire:click="$set('showChoiceModal', false)" style="background:none;border:none;cursor:pointer;color:#A8A29E;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>
            <p style="font-size:13px;color:#78716C;margin-bottom:20px;">Choose the option that fits best.</p>

            <div style="display:flex;flex-direction:column;gap:10px;">
                <a href="{{ route('tenant.support.tickets.create') }}" wire:navigate
                    style="display:flex;align-items:center;gap:14px;padding:16px;border:1.5px solid #E7E5E4;border-radius:10px;text-decoration:none;transition:border-color 150ms;"
                    onmouseover="this.style.borderColor='#7C3AED'" onmouseout="this.style.borderColor='#E7E5E4'">
                    <span style="font-size:22px;">📋</span>
                    <div>
                        <div style="font-size:14px;font-weight:600;color:#1C1917;">Open a Ticket</div>
                        <div style="font-size:12px;color:#A8A29E;">Describe your issue, we'll get back to you</div>
                    </div>
                </a>

                <a href="{{ route('tenant.support.chat') }}" wire:navigate
                    style="display:flex;align-items:center;gap:14px;padding:16px;border:1.5px solid #E7E5E4;border-radius:10px;text-decoration:none;transition:border-color 150ms;"
                    onmouseover="this.style.borderColor='#7C3AED'" onmouseout="this.style.borderColor='#E7E5E4'">
                    <span style="font-size:22px;">💬</span>
                    <div>
                        <div style="font-size:14px;font-weight:600;color:#1C1917;">Live Support</div>
                        <div style="font-size:12px;color:#A8A29E;">Chat with our assistant, or a live agent</div>
                    </div>
                </a>

                <a href="mailto:support@koordli.com"
                    style="display:flex;align-items:center;gap:14px;padding:16px;border:1.5px solid #E7E5E4;border-radius:10px;text-decoration:none;transition:border-color 150ms;"
                    onmouseover="this.style.borderColor='#7C3AED'" onmouseout="this.style.borderColor='#E7E5E4'">
                    <span style="font-size:22px;">✉️</span>
                    <div>
                        <div style="font-size:14px;font-weight:600;color:#1C1917;">Email Koordli</div>
                        <div style="font-size:12px;color:#A8A29E;">support@koordli.com</div>
                    </div>
                </a>
            </div>

            <div style="text-align:center;margin-top:16px;">
                <a href="{{ route('tenant.support.tickets') }}" wire:navigate style="font-size:12px;color:#7C3AED;text-decoration:none;">
                    View My Tickets →
                </a>
            </div>
        </div>
    </div>
    </template>
    @endif
</div>

