<?php

namespace App\Models\Tenant;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Guest extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'uuid', 'tenant_id', 'event_id',
        'name', 'email', 'phone', 'category', 'notes',
        'rsvp_status', 'checked_in', 'checked_in_at',
    ];

    protected $casts = [
        'checked_in'    => 'boolean',
        'checked_in_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Guest $guest) {
            if (empty($guest->uuid)) {
                $guest->uuid = Str::uuid();
            }
        });
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function rsvpResponses(): HasMany
    {
        return $this->hasMany(RsvpResponse::class);
    }

    public function statusColor(): string
    {
        return match($this->rsvp_status) {
            'confirmed' => '#10B981',
            'declined'  => '#EF4444',
            default     => '#F59E0B',
        };
    }

    public function statusBadgeClass(): string
    {
        return match($this->rsvp_status) {
            'confirmed' => 'krd-badge-green',
            'declined'  => 'krd-badge-red',
            default     => 'krd-badge-amber',
        };
    }
}