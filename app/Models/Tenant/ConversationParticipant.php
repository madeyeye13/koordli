<?php

namespace App\Models\Tenant;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConversationParticipant extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'conversation_id', 'participant_type', 'participant_id',
        'added_by', 'joined_at', 'left_at', 'last_read_at',
    ];

    protected $casts = [
        'joined_at'    => 'datetime',
        'left_at'      => 'datetime',
        'last_read_at' => 'datetime',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    /**
     * Resolves the actual notifiable model instance behind this participant
     * row (a tenant User, Client, or VendorAccount) — used for display names
     * and for sending notifications.
     */
    public function resolveParticipant(): \Illuminate\Database\Eloquent\Model|null
    {
        return match($this->participant_type) {
            'tenant_user'   => User::withoutGlobalScope('tenant')->find($this->participant_id),
            'client'        => \App\Models\Central\Client::find($this->participant_id),
            'vendor_account' => \App\Models\Central\VendorAccount::find($this->participant_id),
            default         => null,
        };
    }

    public function unreadCount(): int
    {
        return ConversationMessage::where('conversation_id', $this->conversation_id)
            ->when($this->last_read_at, fn($q) => $q->where('created_at', '>', $this->last_read_at))
            ->count();
    }
}