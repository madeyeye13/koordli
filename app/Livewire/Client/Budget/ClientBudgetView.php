<?php

namespace App\Livewire\Client\Budget;

use App\Models\Tenant\Budget;
use App\Models\Tenant\ClientEventAccess;
use App\Models\Tenant\Event;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.client')]
class ClientBudgetView extends Component
{
    public Event $event;
    public ?Budget $budget = null;

    public function mount(string $slug): void
    {
        $this->event = Event::withoutGlobalScope('tenant')->where('slug', $slug)->firstOrFail();

        $hasAccess = ClientEventAccess::withoutGlobalScope('tenant')
            ->where('client_id', auth('client')->id())
            ->where('event_id', $this->event->id)
            ->exists();

        abort_unless($hasAccess, 403);

        $this->budget = Budget::withoutGlobalScope('tenant')
            ->with(['items', 'clientPayments'])
            ->where('event_id', $this->event->id)
            ->first();
    }

    public function render()
    {
        $tenant = \App\Models\Central\Tenant::find($this->event->tenant_id);

        return view('livewire.client.budget.client-budget-view', [
            'canSeeBalance'   => $tenant->clientFinancialVisible('balance'),
            'canSeeBreakdown' => $tenant->clientFinancialVisible('breakdown'),
            'canSeeVendors'   => $tenant->clientFinancialVisible('vendors'),
            'canSeeFee'       => $tenant->clientFinancialVisible('fee'),
        ]);
    }
}