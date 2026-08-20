<?php

namespace App\Livewire\Client\Conversations;

use App\Models\Tenant\ConversationMessage;
use App\Models\Tenant\ConversationParticipant;
use Livewire\Component;

class UnreadBadge extends Component
{
    public function render()
    {
        $client = auth('client')->user();

        $rows = ConversationParticipant::where('participant_type', 'client')
            ->where('participant_id', $client->id)
            ->whereNull('left_at')
            ->get();

        $total = 0;
        foreach ($rows as $row) {
            $total += ConversationMessage::where('conversation_id', $row->conversation_id)
                ->when($row->last_read_at, fn($q) => $q->where('created_at', '>', $row->last_read_at))
                ->where(function ($q) use ($client) {
                    $q->where('sender_type', '!=', 'client')->orWhere('sender_id', '!=', $client->id);
                })
                ->count();
        }

        return view('livewire.client.conversations.unread-badge', ['count' => $total]);
    }
}