<?php

namespace App\Livewire\Tenant\Checklists;

use App\Models\Tenant\Event;
use App\Services\PermissionService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.tenant')]
class ChecklistsHub extends Component
{
    public function mount(): void
    {
        abort_unless(app(PermissionService::class)->userCan(auth()->user(), 'checklists.view'), 403);
    }

    public function render()
    {
        $events = Event::where('tenant_id', auth()->user()->tenant_id)
            ->with('checklist.items')
            ->orderByDesc('date')
            ->get();

        return view('livewire.tenant.checklists.checklists-hub', compact('events'));
    }
}