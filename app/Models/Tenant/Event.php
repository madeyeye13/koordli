<?php

namespace App\Models\Tenant;

use App\Traits\BelongsToTenant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Event extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'uuid', 'tenant_id', 'event_type_id', 'status_id',
        'name', 'slug', 'client_name', 'client_phone', 'client_email',
        'date', 'start_time', 'end_date', 'end_time',
        'venue', 'location', 'max_guests', 'agreed_budget',
        'notes', 'settings', 'created_by', 'rsvp_enabled',
    ];

    protected $casts = [
        'date'          => 'date',
        'end_date'      => 'date',
        'agreed_budget' => 'decimal:2',
        'settings'      => 'array',
        'rsvp_enabled'  => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (Event $event) {
            if (empty($event->uuid)) {
                $event->uuid = Str::uuid();
            }
            if (empty($event->tenant_id)) {
                $event->tenant_id = auth()->user()->tenant_id;
            }
            if (empty($event->slug)) {
                $event->slug = static::generateSlug(
                    $event->name,
                    $event->tenant_id,
                    $event->date
                );
            }
            if (empty($event->created_by)) {
                $event->created_by = auth()->id();
            }
        });
    }

    public static function generateSlug(string $name, int $tenantId, mixed $date = null): string
    {
        $base = Str::slug($name);

        if ($date) {
            $parsed = $date instanceof \Carbon\Carbon
                ? $date
                : Carbon::parse($date);
            $base = $base . '-' . strtolower($parsed->format('M-Y'));
        }

        $slug  = $base;
        $count = static::withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)
            ->where('slug', 'like', $slug . '%')
            ->count();

        return $count > 0 ? $slug . '-' . ($count + 1) : $slug;
    }

    public function eventType(): BelongsTo
    {
        return $this->belongsTo(EventType::class, 'event_type_id');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(TenantEventStatus::class, 'status_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function rsvpForm(): HasOne
    {
        return $this->hasOne(RsvpForm::class);
    }

    public function rsvpResponses(): HasMany
    {
        return $this->hasMany(RsvpResponse::class);
    }

    public function budget(): HasOne
    {
        return $this->hasOne(Budget::class);
    }

    public function team(): HasMany
    {
        return $this->hasMany(EventTeam::class);
    }

    public function vendorAssignments(): HasMany
    {
        return $this->hasMany(VendorEventAssignment::class);
    }

    
}