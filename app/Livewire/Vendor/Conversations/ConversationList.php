<?php

namespace App\Livewire\Vendor\Conversations;

use App\Models\Tenant\Conversation;
use App\Models\Tenant\ConversationMessage;
use App\Models\Tenant\ConversationParticipant;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.vendor')]
class ConversationList extends Component
{
    public function render()
    {
        $vendorAccount = auth('vendor')->user();

        $participantRows = ConversationParticipant::where('participant_type', 'vendor_account')
            ->where('participant_id', $vendorAccount->id)
            ->whereNull('left_at')
            ->pluck('conversation_id');

        $conversations = Conversation::whereIn('id', $participantRows)
            ->with('event')
            ->orderByDesc('updated_at')
            ->get()
            ->map(function ($conv) use ($vendorAccount) {
                $myRow = ConversationParticipant::where('conversation_id', $conv->id)
                    ->where('participant_type', 'vendor_account')
                    ->where('participant_id', $vendorAccount->id)
                    ->first();

                $conv->unread_count = ConversationMessage::where('conversation_id', $conv->id)
                    ->when($myRow?->last_read_at, fn($q) => $q->where('created_at', '>', $myRow->last_read_at))
                    ->where(function ($q) use ($vendorAccount) {
                        $q->where('sender_type', '!=', 'vendor_account')->orWhere('sender_id', '!=', $vendorAccount->id);
                    })
                    ->count();

                $lastMessage = ConversationMessage::where('conversation_id', $conv->id)->latest()->first();
                $conv->preview = $lastMessage ? \Illuminate\Support\Str::limit($lastMessage->body ?: '📎 Attachment', 50) : 'No messages yet';

                return $conv;
            });

        return view('livewire.vendor.conversations.conversation-list', compact('conversations'));
    }
}