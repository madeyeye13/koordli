<?php

namespace App\Livewire\Vendor\Conversations;

use App\Models\Tenant\Conversation;
use App\Models\Tenant\ConversationMessage;
use App\Models\Tenant\ConversationParticipant;
use Livewire\Component;

class FloatingConversationsWidget extends Component
{
    private function buildList(): array
    {
        $vendorAccount = auth('vendor')->user();

        $rows = ConversationParticipant::where('participant_type', 'vendor_account')
            ->where('participant_id', $vendorAccount->id)
            ->whereNull('left_at')
            ->pluck('conversation_id');

        $conversations = Conversation::whereIn('id', $rows)->with('event')->orderByDesc('updated_at')->get();

        return $conversations->map(function ($conv) use ($vendorAccount) {
            $myRow = ConversationParticipant::where('conversation_id', $conv->id)
                ->where('participant_type', 'vendor_account')->where('participant_id', $vendorAccount->id)->first();

            $unread = ConversationMessage::where('conversation_id', $conv->id)
                ->when($myRow?->last_read_at, fn($q) => $q->where('created_at', '>', $myRow->last_read_at))
                ->where(fn($q) => $q->where('sender_type', '!=', 'vendor_account')->orWhere('sender_id', '!=', $vendorAccount->id))
                ->count();

            $last = ConversationMessage::where('conversation_id', $conv->id)->latest()->first();

            return [
                'uuid'         => $conv->uuid,
                'name'         => $conv->name ?: 'Direct Message',
                'event_name'   => $conv->event->name ?? '',
                'unread_count' => $unread,
                'preview'      => $last ? \Illuminate\Support\Str::limit($last->body ?: '📎 Attachment', 50) : 'No messages yet',
            ];
        })->toArray();
    }

    public function render()
    {
        $initialList = $this->buildList();

        return view('livewire.vendor.conversations.floating-conversations-widget', [
            'hasAnyConversation' => !empty($initialList),
            'initialList'        => $initialList,
        ]);
    }
}