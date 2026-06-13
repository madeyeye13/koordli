<?php

namespace App\Models\Central;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class VendorAccount extends Authenticatable
{
    use Notifiable;

    protected $table = 'vendor_accounts';

    protected $fillable = [
        'uuid', 'tenant_id', 'vendor_id', 'vendor_application_id',
        'name', 'email', 'password', 'phone', 'business_name',
        'is_active', 'password_changed', 'last_login_at',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'is_active'        => 'boolean',
        'password_changed' => 'boolean',
        'last_login_at'    => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (VendorAccount $account) {
            if (empty($account->uuid)) {
                $account->uuid = Str::uuid();
            }
        });
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function vendor()
    {
        return $this->belongsTo(\App\Models\Tenant\Vendor::class);
    }
}