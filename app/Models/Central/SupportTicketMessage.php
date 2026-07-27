<?php

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupportTicketMessage extends Model
{
    protected $fillable = [
        'ticket_id', 'sender_type', 'sender_id', 'message',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(SupportTicket::class, 'ticket_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(SupportTicketAttachment::class, 'message_id');
    }

    public function senderName(): string
    {
        return match($this->sender_type) {
            'bot'    => 'Koordli Assistant',
            'system' => 'System',
            'agent'  => SupportAgent::find($this->sender_id)?->platformUser?->name ?? 'Agent',
            'tenant' => \App\Models\Tenant\User::withoutGlobalScopes()->find($this->sender_id)?->name ?? 'You',
            default  => 'Unknown',
        };
    }

    /**
     * Linkify plain-text URLs for safe display. Message is stored as plain text;
     * this is the ONLY place raw URLs become clickable <a> tags.
     */
    public function renderedMessage(bool $onDarkBubble = false): string
    {
        $escaped = e($this->message);
        $pattern = '/(https?:\/\/[^\s<]+)/i';
        $linkColor = $onDarkBubble ? '#DDD6FE' : '#7C3AED';

        return preg_replace_callback($pattern, function ($matches) use ($linkColor) {
            $url = $matches[1];
            return '<a href="' . $url . '" target="_blank" rel="noopener noreferrer" style="color:' . $linkColor . ';text-decoration:underline;">' . $url . '</a>';
        }, $escaped);
    }
}