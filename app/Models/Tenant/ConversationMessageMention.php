<?php

namespace App\Models\Tenant;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConversationMessageMention extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'message_id', 'participant_type', 'participant_id'];

    public function message(): BelongsTo
    {
        return $this->belongsTo(ConversationMessage::class, 'message_id');
    }

    public function resolveParticipant(): ?\Illuminate\Database\Eloquent\Model
    {
        return match($this->participant_type) {
            'tenant_user'    => User::withoutGlobalScope('tenant')->find($this->participant_id),
            'client'         => \App\Models\Central\Client::find($this->participant_id),
            'vendor_account' => \App\Models\Central\VendorAccount::find($this->participant_id),
            default          => null,
        };
    }
}