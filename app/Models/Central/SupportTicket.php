<?php

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class SupportTicket extends Model
{
    protected $fillable = [
        'uuid', 'tenant_id', 'created_by_user_id', 'subject', 'description',
        'priority', 'category', 'status', 'source', 'assigned_agent_id',
        'rating', 'rating_comment', 'rated_at', 'resolved_at',
    ];

    protected $casts = [
        'rated_at'     => 'datetime',
        'resolved_at'  => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (SupportTicket $ticket) {
            if (empty($ticket->uuid)) {
                $ticket->uuid = Str::uuid();
            }
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Tenant\User::class, 'created_by_user_id')->withoutGlobalScopes();
    }

    public function assignedAgent(): BelongsTo
    {
        return $this->belongsTo(SupportAgent::class, 'assigned_agent_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(SupportTicketMessage::class, 'ticket_id')->orderBy('created_at');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(SupportTicketAttachment::class, 'ticket_id');
    }

    public function assignmentHistory(): HasMany
    {
        return $this->hasMany(SupportTicketAssignmentHistory::class, 'ticket_id')->orderByDesc('created_at');
    }

    public function chatSession(): HasOne
    {
        return $this->hasOne(SupportChatSession::class, 'ticket_id');
    }

    public function priorityColor(): string
    {
        return match($this->priority) {
            'urgent' => '#EF4444',
            'high'   => '#F59E0B',
            'low'    => '#78716C',
            default  => '#3B82F6', // medium
        };
    }

    public function statusColor(): string
    {
        return match($this->status) {
            'in_progress' => '#3B82F6',
            'resolved'    => '#10B981',
            'closed'      => '#78716C',
            default       => '#F59E0B', // open
        };
    }

    public function statusLabel(): string
    {
        return match($this->status) {
            'in_progress' => 'In Progress',
            'resolved'    => 'Resolved',
            'closed'      => 'Closed',
            default       => 'Open',
        };
    }

    public function assignTo(SupportAgent $agent, ?SupportAgent $previousAgent = null, ?string $reason = null, ?int $changedBy = null): void
    {
        SupportTicketAssignmentHistory::create([
            'ticket_id'     => $this->id,
            'from_agent_id' => $previousAgent?->id,
            'to_agent_id'   => $agent->id,
            'reason'        => $reason,
            'changed_by'    => $changedBy,
        ]);

        if ($previousAgent) {
            $previousAgent->decrement('active_chat_count');
        }

        $agent->increment('active_chat_count');

        $this->update([
            'assigned_agent_id' => $agent->id,
            'status'            => 'in_progress',
        ]);
    }
}