<?php

namespace App\Models\Central;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class Client extends Authenticatable
{
    use Notifiable, BelongsToTenant;

    protected $fillable = [
        'uuid', 'tenant_id', 'name', 'email', 'password',
        'phone', 'is_active', 'password_changed',
        'email_verified_at', 'last_login_at',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'is_active'         => 'boolean',
        'password_changed'  => 'boolean',
        'email_verified_at' => 'datetime',
        'last_login_at'     => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Client $client) {
            if (empty($client->uuid)) {
                $client->uuid = Str::uuid();
            }
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}