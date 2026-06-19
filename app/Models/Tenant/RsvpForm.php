<?php

namespace App\Models\Tenant;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class RsvpForm extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'uuid', 'tenant_id', 'event_id', 'title', 'slug',
        'meta_description', 'og_image', 'deadline', 'guest_limit',
        'branding', 'questions', 'ticket_settings',
        'is_active', 'created_by',
    ];

    protected $casts = [
        'branding'        => 'array',
        'questions'       => 'array',
        'ticket_settings' => 'array',
        'deadline'        => 'datetime',
        'is_active'       => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (RsvpForm $form) {
            if (empty($form->uuid)) {
                $form->uuid = Str::uuid();
            }
        });
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function responses(): HasMany
    {
        return $this->hasMany(RsvpResponse::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(RsvpQuestion::class)->orderBy('sort_order');
    }

    public function confirmedResponses(): HasMany
    {
        return $this->hasMany(RsvpResponse::class)->where('status', 'confirmed');
    }

    public function totalAttendees(): int
    {
        return $this->confirmedResponses()->sum('plus_one_count') +
               $this->confirmedResponses()->count();
    }

    public function isDeadlinePassed(): bool
    {
        return $this->deadline && now()->isAfter($this->deadline);
    }

    public function isAtCapacity(): bool
    {
        if (!$this->guest_limit) return false;
        return $this->totalAttendees() >= $this->guest_limit;
    }

    public function publicUrl(): string
    {
        return url('/rsvp/' . $this->slug);
    }
}