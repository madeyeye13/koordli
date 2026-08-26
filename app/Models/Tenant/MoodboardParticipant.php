<?php

namespace App\Models\Tenant;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MoodboardParticipant extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'moodboard_id', 'participant_type', 'participant_id', 'added_by',
    ];

    public function moodboard(): BelongsTo
    {
        return $this->belongsTo(Moodboard::class);
    }

    public function resolveParticipant(): ?Model
    {
        return match($this->participant_type) {
            'client'         => \App\Models\Central\Client::find($this->participant_id),
            'vendor_account' => \App\Models\Central\VendorAccount::find($this->participant_id),
            default          => null,
        };
    }
}