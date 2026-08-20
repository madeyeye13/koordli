<?php

namespace App\Models\Tenant;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ConversationMessage extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'conversation_id', 'reply_to_message_id', 'sender_type', 'sender_id',
        'body', 'edited_at', 'deleted_at',
    ];

    protected $casts = [
        'edited_at'  => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(ConversationMessageAttachment::class, 'message_id');
    }

    public function replyTo(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ConversationMessage::class, 'reply_to_message_id');
    }

    public function mentions(): HasMany
    {
        return $this->hasMany(\App\Models\Tenant\ConversationMessageMention::class, 'message_id');
    }

    
    public function isHiddenFor(string $type, int $id): bool
    {
        return \App\Models\Tenant\ConversationMessageDeletion::where('message_id', $this->id)
            ->where('participant_type', $type)
            ->where('participant_id', $id)
            ->exists();
    }

        /**
     * Names of participants (excluding the sender) who have read up to at
     * least this message's timestamp — the read-receipt display data.
     */
    public function seenBy(): \Illuminate\Support\Collection
    {
        return $this->conversation->participants
            ->reject(fn($p) => $p->participant_type === $this->sender_type && $p->participant_id === $this->sender_id)
            ->filter(fn($p) => $p->last_read_at && $p->last_read_at->gte($this->created_at))
            ->map(fn($p) => $p->resolveParticipant()?->name)
            ->filter()
            ->values();
    }

    public function canDeleteForEveryone(string $senderType, int $senderId): bool
    {
        return $this->sender_type === $senderType
            && $this->sender_id === $senderId
            && !$this->deleted_at
            && abs($this->created_at->diffInMinutes(now())) <= 30;
    }

    public function senderName(): string
    {
        if ($this->sender_type === 'system') return 'System';

        $sender = match($this->sender_type) {
            'tenant_user'    => \App\Models\Tenant\User::withoutGlobalScope('tenant')->find($this->sender_id),
            'client'         => \App\Models\Central\Client::find($this->sender_id),
            'vendor_account' => \App\Models\Central\VendorAccount::find($this->sender_id),
            default          => null,
        };

        return $sender?->name ?? 'Unknown';
    }

    /**
     * Plain text stored, linkified + @mentions highlighted at render —
     * same safe pattern as Support System messages (never render raw HTML
     * from user input; only ever wrap already-escaped text in known-safe tags).
     */
    public function renderedBody(): string
    {
        if ($this->deleted_at) {
            return '<em style="color:#A8A29E;">This message was deleted.</em>';
        }

        $escaped = e($this->body);

        $urlPattern = '/(https?:\/\/[^\s<]+)/i';
        $escaped = preg_replace_callback($urlPattern, function ($matches) {
            return '<a href="' . $matches[1] . '" target="_blank" rel="noopener noreferrer" style="color:#7C3AED;text-decoration:underline;">' . $matches[1] . '</a>';
        }, $escaped);

        // Highlight @Name mentions — matches against names already resolved
        // and stored via ConversationMessageMention at send time, so this
        // only highlights CONFIRMED mentions, never arbitrary @text a user typed
        foreach ($this->mentions as $mention) {
            $model = $mention->resolveParticipant();
            if (!$model) continue;
            $name = e($model->name);
            $escaped = str_replace(
                '@' . $name,
                '<strong style="background:#F5F3FF;color:#7C3AED;padding:1px 4px;border-radius:4px;">@' . $name . '</strong>',
                $escaped
            );
        }

        return $escaped;
    }
}