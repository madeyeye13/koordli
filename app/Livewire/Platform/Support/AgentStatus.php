<?php

namespace App\Livewire\Platform\Support;

use App\Models\Central\SupportAgent;
use App\Traits\WithToast;
use Livewire\Attributes\Renderless;
use Livewire\Component;

class AgentStatus extends Component
{
    use WithToast;

    public ?SupportAgent $agent = null;

    public function mount(): void
    {
        $this->agent = SupportAgent::where('platform_user_id', auth('platform')->id())->first();
    }

    #[Renderless]
    public function toggleAvailability(): void
    {
        if (!$this->agent) {
            $this->agent = SupportAgent::create([
                'platform_user_id'     => auth('platform')->id(),
                'is_available'         => true,
                'status'               => 'online',
                'max_concurrent_chats' => 3,
                'last_seen_at'         => now(),
            ]);
        } else {
            $newAvailable = !$this->agent->is_available;
            $this->agent->update([
                'is_available' => $newAvailable,
                'status'       => $newAvailable ? 'online' : 'offline',
                'last_seen_at' => now(),
            ]);
        }
    }

    public function render()
    {
        return view('livewire.platform.support.agent-status');
    }
}