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
        $sessions = SupportChatSession::where('status', 'active')->get();

        foreach ($sessions as $session) {
            $lastRealMessage = SupportTicketMessage::where('ticket_id', $session->ticket_id)
                ->whereIn('sender_type', ['tenant', 'agent', 'bot'])
                ->orderByDesc('created_at')
                ->first();

            if (!$lastRealMessage) continue;

            $warningMessage = SupportTicketMessage::where('ticket_id', $session->ticket_id)
                ->where('sender_type', 'system')
                ->where('created_at', '>=', $lastRealMessage->created_at)
                ->orderByDesc('created_at')
                ->first();

            if ($warningMessage) {
                // Already warned — check if enough time has passed since the WARNING itself
                $minutesSinceWarning = abs(now()->diffInMinutes($warningMessage->created_at));

                if ($minutesSinceWarning >= ($this->closeAfterMinutes - $this->warnAfterMinutes)) {
                    $session->update(['status' => 'ended', 'ended_by' => 'system', 'ended_at' => now()]);
                    $session->ticket->update(['status' => 'closed', 'resolved_at' => now()]);

                    if ($session->ticket->assignedAgent) {
                        $session->ticket->assignedAgent->recalculateActiveChatCount();
                    }

                    $msg = SupportTicketMessage::create([
                        'ticket_id'   => $session->ticket_id,
                        'sender_type' => 'system',
                        'message'     => "This chat has been closed due to inactivity. Should you wish to continue this conversation, feel free to reach out to us again or raise a support ticket.",
                    ]);
                    broadcast(new SupportChatMessageSent($msg, $session->ticket->uuid));
                    broadcast(new \App\Events\SupportChatEnded($session->ticket->uuid));

                    $this->info("Closed ticket #{$session->ticket_id} due to inactivity.");
                }
            } else {
                $minutesSinceLastMessage = abs(now()->diffInMinutes($lastRealMessage->created_at));

                if ($minutesSinceLastMessage >= $this->warnAfterMinutes) {
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
}