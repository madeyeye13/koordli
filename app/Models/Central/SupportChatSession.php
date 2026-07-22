<?php

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupportChatSession extends Model
{
    protected $fillable = [
        'ticket_id', 'status', 'bot_engaged_at', 'escalated_at',
        'agent_joined_at', 'ended_at', 'ended_by',
    ];

    protected $casts = [
        'bot_engaged_at'  => 'datetime',
        'escalated_at'    => 'datetime',
        'agent_joined_at' => 'datetime',
        'ended_at'        => 'datetime',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(SupportTicket::class, 'ticket_id');
    }

    public function isLive(): bool
    {
        return in_array($this->status, ['waiting', 'active']);
    }
}