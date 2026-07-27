<?php

namespace App\Events;

use App\Models\Central\SupportTicket;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SupportChatWaiting implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public SupportTicket $ticket) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('support-queue')];
    }

    public function broadcastAs(): string
    {
        return 'chat.waiting';
    }

    public function broadcastWith(): array
    {
        return [
            'ticket_uuid' => $this->ticket->uuid,
            'tenant_name' => $this->ticket->tenant->name,
            'subject'     => $this->ticket->subject,
            'waiting_since' => $this->ticket->chatSession->escalated_at?->diffForHumans(),
        ];
    }
}