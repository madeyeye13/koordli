<?php

namespace App\Models\Tenant;

use App\Enums\UserType;
use App\Traits\BelongsToTenant;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use Notifiable, HasRoles, BelongsToTenant;

    protected $fillable = [
        'uuid',
        'tenant_id',
        'name',
        'email',
        'password',
        'type',
        'is_active',
        'is_self_registered',        // ← add
        'onboarding_completed',      // ← add
        'onboarding_completed_at',   // ← add
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
        'type'              => UserType::class,
        'is_active'         => 'boolean',
        'is_self_registered'      => 'boolean',   // ← add
        'onboarding_completed'    => 'boolean',   // ← add
        'onboarding_completed_at' => 'datetime',  // ← add
        'last_login_at'     => 'datetime',
    ];

    public function tenant(): \Illuminate\Database\Eloquent\Relations\BelongsTo
{
    return $this->belongsTo(\App\Models\Central\Tenant::class, 'tenant_id')
                ->withoutGlobalScopes();
}
}