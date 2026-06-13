<?php

namespace App\Livewire\Client;

use App\Models\Tenant\Event;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.client')]
class Dashboard extends Component
{
    public function render()
    {
        $client = auth('client')->user();

        $events = Event::withoutGlobalScope('tenant')
            ->where('tenant_id', $client->tenant_id)
            ->where('client_email', $client->email)
            ->with([
                'eventType',
                'status',
                'budget.items',
                'budget.clientPayments',
            ])
            ->orderBy('date', 'asc')
            ->get();

        return view('livewire.client.dashboard', compact('events'));
    }
}