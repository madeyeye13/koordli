<?php

namespace App\Models\Tenant;

use App\Enums\RSVPStatus;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RsvpResponse extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'uuid',
        'tenant_id',
        'event_id',
        'guest_id',
        'rsvp_form_id',
        'status',
        'plus_one_count',
        'qr_token',
        'response_data',
        'checked_in_at',
    ];

    protected $casts = [
        'status'        => RSVPStatus::class,
        'response_data' => 'array',
        'checked_in_at' => 'datetime',
    ];

    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function rsvpForm(): BelongsTo
    {
        return $this->belongsTo(RsvpForm::class);
    }
}