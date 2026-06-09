<?php

namespace App\Models\Tenant;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Event extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'uuid',
        'tenant_id',
        'event_type_id',
        'status_id',        // ← changed from status string to status_id FK
        'name',
        'slug',
        'date',
        'venue',
        'max_guests',
        'settings',
        'created_by',
    ];

    protected $casts = [
        'date'     => 'date',
        'settings' => 'array',
    ];

    public function eventType(): BelongsTo
    {
        return $this->belongsTo(EventType::class);
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

    public function vendors(): HasMany
    {
        return $this->hasMany(Vendor::class);
    }

    public function budget(): HasOne
    {
        return $this->hasOne(Budget::class);
    }

    public function guests(): HasMany
    {
        return $this->hasMany(Guest::class);
    }

    public function rsvpForms(): HasMany
    {
        return $this->hasMany(RsvpForm::class);
    }

    public function runsheets(): HasMany
    {
        return $this->hasMany(Runsheet::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function team(): HasMany
    {
        return $this->hasMany(EventTeam::class);
    }
}