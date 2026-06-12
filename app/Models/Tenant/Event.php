<?php

namespace App\Models\Tenant;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Event extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'uuid',
        'tenant_id',
        'event_type_id',
        'status_id',
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
                $event->slug = static::generateSlug($event->name, $event->tenant_id);
            }
            if (empty($event->created_by)) {
                $event->created_by = auth()->id();
            }
        });
    }

    public static function generateSlug(string $name, int $tenantId): string
    {
        $slug  = Str::slug($name);
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

    public function guests(): HasMany
    {
        return $this->hasMany(Guest::class);
    }

    public function budget(): HasMany
    {
        return $this->hasMany(Budget::class);
    }

    public function team(): HasMany
    {
        return $this->hasMany(EventTeam::class);
    }
}