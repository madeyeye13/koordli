<?php

namespace App\Models\Tenant;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class PushSubscription extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'notifiable_type', 'notifiable_id',
        'endpoint', 'endpoint_hash', 'p256dh_key', 'auth_token', 'user_agent',
    ];

    protected static function booted(): void
    {
        static::creating(function (PushSubscription $sub) {
            $sub->endpoint_hash = hash('sha256', $sub->endpoint);
        });
    }
}