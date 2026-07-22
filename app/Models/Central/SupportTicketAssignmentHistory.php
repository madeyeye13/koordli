<?php

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupportTicketAssignmentHistory extends Model
{
    protected $table = 'support_ticket_assignment_history';

    protected $fillable = [
        'ticket_id', 'from_agent_id', 'to_agent_id', 'reason', 'changed_by',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(SupportTicket::class, 'ticket_id');
    }

    public function fromAgent(): BelongsTo
    {
        return $this->belongsTo(SupportAgent::class, 'from_agent_id');
    }

    public function toAgent(): BelongsTo
    {
        return $this->belongsTo(SupportAgent::class, 'to_agent_id');
    }
}