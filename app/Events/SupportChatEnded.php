<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SupportChatEnded implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public function __construct(public string $ticketUuid) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('support-ticket.' . $this->ticketUuid)];
    }

    public function broadcastAs(): string
    {
        return 'chat.ended';
    }
}