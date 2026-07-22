<?php

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupportAgent extends Model
{
    protected $fillable = [
        'platform_user_id', 'is_available', 'status',
        'max_concurrent_chats', 'active_chat_count', 'last_seen_at',
    ];

    protected $casts = [
        'is_available'  => 'boolean',
        'last_seen_at'  => 'datetime',
    ];

    public function platformUser(): BelongsTo
    {
        return $this->belongsTo(PlatformUser::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(SupportTicket::class, 'assigned_agent_id');
    }

    public function canAcceptMoreChats(): bool
    {
        return $this->is_available
            && $this->status === 'online'
            && $this->active_chat_count < $this->max_concurrent_chats;
    }

    public static function nextAvailable(): ?self
    {
        return static::where('is_available', true)
            ->where('status', 'online')
            ->whereColumn('active_chat_count', '<', 'max_concurrent_chats')
            ->orderBy('active_chat_count') // load-balance: least busy agent first
            ->first();
    }
}