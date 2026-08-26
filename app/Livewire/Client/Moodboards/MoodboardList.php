<?php

namespace App\Livewire\Client\Moodboards;

use App\Models\Tenant\ClientEventAccess;
use App\Models\Tenant\Event;
use App\Models\Tenant\Moodboard;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.client')]
class MoodboardList extends Component
{
    public Event $event;

    public function mount(string $slug): void
    {
        $this->event = Event::withoutGlobalScope('tenant')->where('slug', $slug)->firstOrFail();

        $hasAccess = ClientEventAccess::withoutGlobalScope('tenant')
            ->where('client_id', auth('client')->id())
            ->where('event_id', $this->event->id)
            ->exists();

        abort_unless($hasAccess, 403);
    }

    public function render()
    {
        // Only boards this client is explicitly allowed to see — never a
        // default-visible list, per the original spec's explicit
        // requirement that client visibility is always opt-in per board.
        $moodboards = Moodboard::withoutGlobalScope('tenant')
            ->where('event_id', $this->event->id)
            ->where('is_client_visible', true)
            ->where('is_template', false)
            ->orderByDesc('updated_at')
            ->get();

        return view('livewire.client.moodboards.moodboard-list', compact('moodboards'));
    }
}