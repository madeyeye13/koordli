<?php

namespace App\Livewire\Platform\Support;

use App\Models\Central\SupportAgent;
use App\Models\Central\SupportTicket;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.platform')]
class TicketInbox extends Component
{
    #[Url] public string $statusFilter = '';
    #[Url] public string $viewFilter   = 'mine'; // mine|unassigned|all

    public function mount(): void
    {
        abort_unless(auth('platform')->user()?->can('support.tickets.view'), 403);
    }

    public function refreshList(): void
    {
        // no-op body — calling this just forces Livewire to re-render, which re-runs render() below
    }

    public function render()
    {
        $agent = SupportAgent::where('platform_user_id', auth('platform')->id())->first();

        $tickets = SupportTicket::with(['tenant', 'assignedAgent.platformUser'])
            ->when($this->statusFilter, fn($q) => $q->where('status', $this->statusFilter))
            ->when($this->viewFilter === 'mine' && $agent, fn($q) => $q->where('assigned_agent_id', $agent->id))
            ->when($this->viewFilter === 'unassigned', fn($q) => $q->whereNull('assigned_agent_id'))
            ->orderByRaw("FIELD(priority, 'urgent', 'high', 'medium', 'low')")
            ->orderByDesc('created_at')
            ->get();

        $counts = [
            'mine'       => $agent ? SupportTicket::where('assigned_agent_id', $agent->id)->whereNotIn('status', ['closed'])->count() : 0,
            'unassigned' => SupportTicket::whereNull('assigned_agent_id')->whereNotIn('status', ['closed'])->count(),
            'all'        => SupportTicket::whereNotIn('status', ['closed'])->count(),
        ];

        return view('livewire.platform.support.ticket-inbox', compact('tickets', 'counts'));
    }
}