<?php

namespace App\Livewire\Vendor\Conversations;

use App\Models\Tenant\ConversationMessage;
use App\Models\Tenant\ConversationParticipant;
use Livewire\Component;

class UnreadBadge extends Component
{
    public function render()
    {
        $vendorAccount = auth('vendor')->user();

        $rows = ConversationParticipant::where('participant_type', 'vendor_account')
            ->where('participant_id', $vendorAccount->id)
            ->whereNull('left_at')
            ->get();

        $total = 0;
        foreach ($rows as $row) {
            $total += ConversationMessage::where('conversation_id', $row->conversation_id)
                ->when($row->last_read_at, fn($q) => $q->where('created_at', '>', $row->last_read_at))
                ->where(function ($q) use ($vendorAccount) {
                    $q->where('sender_type', '!=', 'vendor_account')->orWhere('sender_id', '!=', $vendorAccount->id);
                })
                ->count();
        }

        return view('livewire.vendor.conversations.unread-badge', ['count' => $total]);
    }
}