<?php

namespace App\Models\Tenant;

use App\Enums\NotificationChannel;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationPreference extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'channel',
        'event_type',
        'is_enabled',
    ];

    protected $casts = [
        'channel'    => NotificationChannel::class,
        'is_enabled' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}