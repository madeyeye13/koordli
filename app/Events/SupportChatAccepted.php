<?php

namespace App\Events;

use App\Models\Central\SupportAgent;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SupportChatAccepted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public string $ticketUuid, public SupportAgent $agent) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('support-ticket.' . $this->ticketUuid),
            new PrivateChannel('support-queue'), // so other agents remove it from their queue view
        ];
    }

    public function broadcastAs(): string
    {
        return 'chat.accepted';
    }

    public function broadcastWith(): array
    {
        return [
            'ticket_uuid' => $this->ticketUuid,
            'agent_name'  => $this->agent->platformUser->name,
        ];
    }
}