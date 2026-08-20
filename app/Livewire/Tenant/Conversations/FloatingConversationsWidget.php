<?php

namespace App\Livewire\Tenant\Conversations;

use App\Models\Tenant\Conversation;
use App\Models\Tenant\ConversationMessage;
use App\Models\Tenant\ConversationParticipant;
use Livewire\Component;

class FloatingConversationsWidget extends Component
{
    public bool $panelOpen = false;
    public ?string $activeConversationUuid = null;
    

    public function togglePanel(): void
    {
        $this->panelOpen = !$this->panelOpen;
        if (!$this->panelOpen) {
            $this->activeConversationUuid = null;
        }
    }

    public function openMiniChat(string $uuid): array
    {
        $this->activeConversationUuid = $uuid;
        $this->markActiveRead();

        $conversation = Conversation::where('uuid', $uuid)
            ->with(['messages' => fn($q) => $q->latest()->limit(20)])
            ->first();

        if (!$conversation) return [];

        return $conversation->messages->reverse()->values()->map(fn($msg) => [
            'id'          => $msg->id,
            'sender_type' => $msg->sender_type,
            'sender_id'   => $msg->sender_id,
            'body'        => $msg->body,
        ])->toArray();
    }

    public function backToList(): void
    {
        $this->activeConversationUuid = null;
    }

    private function markActiveRead(): void
    {
        if (!$this->activeConversationUuid) return;

        $conversation = Conversation::where('uuid', $this->activeConversationUuid)->first();
        if (!$conversation) return;

        ConversationParticipant::where('conversation_id', $conversation->id)
            ->where('participant_type', 'tenant_user')
            ->where('participant_id', auth()->id())
            ->update(['last_read_at' => now()]);
    }

    public function sendMiniMessage(string $body): void
    {
        if (empty(trim($body)) || !$this->activeConversationUuid) return;

        $conversation = Conversation::where('uuid', $this->activeConversationUuid)->first();
        if (!$conversation) return;

        $message = ConversationMessage::create([
            'tenant_id'       => auth()->user()->tenant_id,
            'conversation_id' => $conversation->id,
            'sender_type'     => 'tenant_user',
            'sender_id'       => auth()->id(),
            'body'            => $body,
        ]);

        $conversation->touch();

        broadcast(new \App\Events\ConversationMessageSent($message, $conversation->uuid))->toOthers();

        $this->markActiveRead();
    }

        public function getConversationList(): array
    {
        return $this->buildConversationList();
    }

    private function buildConversationList(): array
    {
        $participantRows = ConversationParticipant::where('participant_type', 'tenant_user')
            ->where('participant_id', auth()->id())
            ->whereNull('left_at')
            ->pluck('conversation_id');

        $conversations = Conversation::whereIn('id', $participantRows)
            ->with('event')
            ->orderByDesc('updated_at')
            ->get();

        return $conversations->map(function ($conv) {
            $myRow = ConversationParticipant::where('conversation_id', $conv->id)
                ->where('participant_type', 'tenant_user')
                ->where('participant_id', auth()->id())
                ->first();

            $unread = ConversationMessage::where('conversation_id', $conv->id)
                ->when($myRow?->last_read_at, fn($q) => $q->where('created_at', '>', $myRow->last_read_at))
                ->where(function ($q) {
                    $q->where('sender_type', '!=', 'tenant_user')
                      ->orWhere('sender_id', '!=', auth()->id());
                })
                ->count();

            $lastMessage = ConversationMessage::where('conversation_id', $conv->id)->latest()->first();

            return [
                'uuid'         => $conv->uuid,
                'name'         => $conv->name ?: ($conv->type === 'direct' ? 'Direct Message' : 'Conversation'),
                'event_name'   => $conv->event->name ?? '',
                'unread_count' => $unread,
                'preview'      => $lastMessage ? \Illuminate\Support\Str::limit($lastMessage->body ?: '📎 Attachment', 40) : 'No messages yet',
            ];
        })->toArray();
    }

    public function render()
    {
        $initialList = $this->buildConversationList();

        return view('livewire.tenant.conversations.floating-conversations-widget', [
            'hasAnyConversation' => !empty($initialList),
            'initialList'        => $initialList,
        ]);
    }
}