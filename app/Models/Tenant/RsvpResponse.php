<?php

namespace App\Models\Tenant;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class RsvpResponse extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'uuid', 'tenant_id', 'event_id', 'rsvp_form_id', 'guest_id',
        'respondent_name', 'respondent_email', 'respondent_phone',
        'status', 'plus_one_count', 'qr_token', 'edit_token',
        'response_data', 'checked_in_at',
    ];

    protected $casts = [
        'response_data'  => 'array',
        'checked_in_at'  => 'datetime',
        'plus_one_count' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (RsvpResponse $response) {
            if (empty($response->uuid)) {
                $response->uuid = Str::uuid();
            }
            if (empty($response->edit_token)) {
                $response->edit_token = Str::random(32);
            }
        });
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function rsvpForm(): BelongsTo
    {
        return $this->belongsTo(RsvpForm::class);
    }

    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(RsvpResponseAnswer::class);
    }

    public function totalAttendees(): int
    {
        return 1 + $this->plus_one_count;
    }

    public function isConfirmed(): bool { return $this->status === 'confirmed'; }
    public function isDeclined(): bool  { return $this->status === 'declined'; }
    public function isPending(): bool   { return $this->status === 'pending'; }

    public function editUrl(): string
    {
        return url('/rsvp/' . $this->rsvpForm->slug . '/edit/' . $this->edit_token);
    }

    public function generateQrToken(): string
    {
        return 'RSVP-' . strtoupper(Str::random(10));
    }
}