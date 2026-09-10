<?php

namespace App\Models\Central;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class PlatformUser extends Authenticatable
{
    use Notifiable, \Spatie\Permission\Traits\HasRoles;

    protected $guard_name = 'platform';

    protected $table = 'platform_users';

    protected $fillable = [
        'uuid',
        'name',
        'email',
        'password',
        'role',
        'is_active',
        'invite_token',
        'invited_at',
        'invite_accepted_at',
        'invited_by',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at'  => 'datetime',
        'password'           => 'hashed',
        'is_active'          => 'boolean',
        'invited_at'         => 'datetime',
        'invite_accepted_at' => 'datetime',
    ];

    public function supportAgent(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(SupportAgent::class, 'platform_user_id');
    }

    public function isSupportAgent(): bool
    {
        return $this->supportAgent()->exists();
    }

    /**
     * Matches the exact same convention already used by the tenant User
     * model — see the comment in routes/channels.php referencing
     * User::receivesBroadcastNotificationsOn().
     */
    public function receivesBroadcastNotificationsOn(): string
    {
        return 'notifications.platform-user.' . $this->id;
    }
}