<?php

namespace App\Livewire\Tenant\Support;

use App\Models\Central\SupportTicket;
use Livewire\Attributes\On;
use Livewire\Component;

class HelpWidget extends Component
{
    use \App\Traits\WithToast;

    public bool $showChoiceModal = false;
    public ?string $activeTicketUuid = null;
    public int $unreadCount = 0;

    public function mount(): void
    {
        $this->refreshActiveChat();
    }

    public function refreshActiveChat(): void
    {
        $ticket = SupportTicket::where('tenant_id', auth()->user()->tenant_id)
            ->where('created_by_user_id', auth()->id())
            ->where('source', 'chat')
            ->whereHas('chatSession', fn($q) => $q->whereIn('status', ['bot', 'waiting', 'active']))
            ->latest()
            ->first();

        if ($ticket) {
            $this->activeTicketUuid = $ticket->uuid;
            $this->unreadCount = $ticket->unreadForTenant();
        } else {
            $this->activeTicketUuid = null;
            $this->unreadCount = 0;
        }
    }

    public function openChoices(): void
    {
        // If there's already an active/waiting live chat, skip the menu and go straight there
        if ($this->activeTicketUuid) {
            $this->redirect(route('tenant.support.chat'), navigate: true);
            return;
        }

        $this->showChoiceModal = true;
    }

    public function liveSupportComingSoon(): void
    {
        $this->showChoiceModal = false;
        $this->toastWarning('Live chat is launching soon! For now, please open a ticket and we\'ll respond quickly.');
    }

    public function render()
    {
        return view('livewire.tenant.support.help-widget');
    }
}