<?php

namespace App\Livewire\Tenant\Moodboards;

use App\Models\Tenant\Event;
use App\Models\Tenant\Moodboard;
use App\Services\PermissionService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.tenant')]
class MoodboardsHub extends Component
{
    public function mount(): void
    {
        abort_unless(app(PermissionService::class)->userCan(auth()->user(), 'moodboards.view'), 403);
    }

    public function render()
    {
        $tenantId = auth()->user()->tenant_id;

        // Every event, annotated with its own moodboard count — lets
        // someone jump straight to the right event's boards without
        // detouring through the general Events list first.
        $events = Event::where('tenant_id', $tenantId)
            ->withCount(['moodboards' => fn($q) => $q->where('is_template', false)])
            ->orderByDesc('date')
            ->get();

        return view('livewire.tenant.moodboards.moodboards-hub', compact('events'));
    }
}