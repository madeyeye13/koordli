<?php

namespace App\Models\Central;

use App\Models\Tenant\RsvpForm;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PublicDomain extends Model
{
    protected $fillable = [
        'tenant_id',
        'rsvp_form_id',
        'domain',
        'domain_type',
        'status',
        'verification_token',
        'verified_at',
        'last_checked_at',
        'failure_code',
        'failure_message',
        'observed_dns_value',
        'expected_dns_value',
        'last_notified_at',
        'notification_fingerprint',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
        'last_checked_at' => 'datetime',
        'last_notified_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function rsvpForm(): BelongsTo
    {
        return $this->belongsTo(RsvpForm::class);
    }

    public function isVerified(): bool
    {
        return $this->status === 'verified';
    }
}
