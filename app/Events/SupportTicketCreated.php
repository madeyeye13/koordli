<?php
namespace App\Events;

use App\Models\Central\SupportTicket;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SupportTicketCreated
{
    use Dispatchable, SerializesModels;
    public function __construct(public SupportTicket $ticket) {}
}