<?php

namespace App\Console\Commands;

use App\Events\SupportChatMessageSent;
use App\Models\Central\SupportChatSession;
use App\Models\Central\SupportTicketMessage;
use Illuminate\Console\Command;

class CloseInactiveChatSessions extends Command
{
    protected $signature   = 'koordli:close-inactive-chats';
    protected $description = 'Warn then auto-close live chat sessions with no tenant activity';

    // Configurable thresholds
    private int $warnAfterMinutes = 5;
    private int $closeAfterMinutes = 10;

    public function handle(): void
    {
        $sessions = SupportChatSession::where('status', 'active')
            ->with('ticket.messages')
            ->get();

        foreach ($sessions as $session) {
            $lastMessage = $session->ticket->messages()->latest()->first();
            if (!$lastMessage) continue;

            $minutesSinceLastMessage = now()->diffInMinutes($lastMessage->created_at);

            // Already warned? Check for a system warning message sent after the last real message
            $alreadyWarned = $session->ticket->messages()
                ->where('sender_type', 'system')
                ->where('created_at', '>', $lastMessage->created_at)
                ->exists();

            if ($minutesSinceLastMessage >= $this->closeAfterMinutes && $alreadyWarned) {
                $session->update(['status' => 'ended', 'ended_by' => 'system', 'ended_at' => now()]);
                $session->ticket->update(['status' => 'resolved', 'resolved_at' => now()]);

                $msg = SupportTicketMessage::create([
                    'ticket_id'   => $session->ticket_id,
                    'sender_type' => 'system',
                    'message'     => 'This chat has been closed due to inactivity. Feel free to start a new conversation anytime!',
                ]);
                broadcast(new SupportChatMessageSent($msg, $session->ticket->uuid));

                $this->info("Closed ticket #{$session->ticket_id} due to inactivity.");

            } elseif ($minutesSinceLastMessage >= $this->warnAfterMinutes && !$alreadyWarned) {
                $msg = SupportTicketMessage::create([
                    'ticket_id'   => $session->ticket_id,
                    'sender_type' => 'system',
                    'message'     => "This chat will close in " . ($this->closeAfterMinutes - $this->warnAfterMinutes) . " minutes due to inactivity. Reply anytime to keep it open.",
                ]);
                broadcast(new SupportChatMessageSent($msg, $session->ticket->uuid));

                $this->info("Warned ticket #{$session->ticket_id} of upcoming closure.");
            }
        }
    }
}