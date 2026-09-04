<?php

namespace App\Livewire\Client\Checklists;

use App\Models\Tenant\Checklist;
use App\Models\Tenant\ClientEventAccess;
use App\Models\Tenant\Event;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.client')]
class ChecklistView extends Component
{
    public Event $event;
    public ?Checklist $checklist = null;

    public function mount(string $slug): void
    {
        $this->event = Event::withoutGlobalScope('tenant')->where('slug', $slug)->firstOrFail();

        $hasAccess = ClientEventAccess::withoutGlobalScope('tenant')
            ->where('client_id', auth('client')->id())
            ->where('event_id', $this->event->id)
            ->exists();

        abort_unless($hasAccess, 403);

        $checklist = Checklist::withoutGlobalScope('tenant')
            ->where('event_id', $this->event->id)
            ->where('is_client_visible', true)
            ->first();

        // Deliberately not aborting when null — an event with no
        // checklist yet, or one the planner hasn't shared, should show a
        // clean "nothing shared yet" state, not a 403. A 403 here would
        // read as broken access, when it's really just "not ready yet."
        $this->checklist = $checklist;
    }

    public function render()
    {
        if (!$this->checklist) {
            return view('livewire.client.checklists.checklist-view');
        }

        $this->checklist->load('items');

        return view('livewire.client.checklists.checklist-view', [
            'phaseProgress' => $this->checklist->phaseProgress(),
            'overall'       => $this->checklist->overallProgress(),
        ]);
    }
}