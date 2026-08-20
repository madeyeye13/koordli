<?php

namespace App\Livewire\Client\Conversations;

use App\Models\Tenant\Conversation;
use App\Models\Tenant\ConversationMessage;
use App\Models\Tenant\ConversationParticipant;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.client')]
class ConversationList extends Component
{
    public function render()
    {
        $client = auth('client')->user();

        $participantRows = ConversationParticipant::where('participant_type', 'client')
            ->where('participant_id', $client->id)
            ->whereNull('left_at')
            ->pluck('conversation_id');

        $conversations = Conversation::whereIn('id', $participantRows)
            ->with('event')
            ->orderByDesc('updated_at')
            ->get()
            ->map(function ($conv) use ($client) {
                $myRow = ConversationParticipant::where('conversation_id', $conv->id)
                    ->where('participant_type', 'client')
                    ->where('participant_id', $client->id)
                    ->first();

                $conv->unread_count = ConversationMessage::where('conversation_id', $conv->id)
                    ->when($myRow?->last_read_at, fn($q) => $q->where('created_at', '>', $myRow->last_read_at))
                    ->where(function ($q) {
                        $q->where('sender_type', '!=', 'client')->orWhere('sender_id', '!=', auth('client')->id());
                    })
                    ->count();

                $lastMessage = ConversationMessage::where('conversation_id', $conv->id)->latest()->first();
                $conv->preview = $lastMessage ? \Illuminate\Support\Str::limit($lastMessage->body ?: '📎 Attachment', 50) : 'No messages yet';

                return $conv;
            });

        return view('livewire.client.conversations.conversation-list', compact('conversations'));
    }
}