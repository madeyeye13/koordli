<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ConversationParticipantsChanged implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public function __construct(public string $conversationUuid) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('conversation.' . $this->conversationUuid)];
    }

    public function broadcastAs(): string
    {
        return 'participants.changed';
    }
}