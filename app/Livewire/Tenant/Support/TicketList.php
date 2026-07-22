<?php

namespace App\Livewire\Tenant\Support;

use App\Models\Central\SupportTicket;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.tenant')]
class TicketList extends Component
{
    #[Url] public string $statusFilter = '';

    public function render()
    {
        $tickets = SupportTicket::where('tenant_id', auth()->user()->tenant_id)
            ->when($this->statusFilter, fn($q) => $q->where('status', $this->statusFilter))
            ->withCount('messages')
            ->orderByDesc('created_at')
            ->get();

        return view('livewire.tenant.support.ticket-list', compact('tickets'));
    }
}