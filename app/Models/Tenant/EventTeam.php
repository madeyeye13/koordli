<?php

namespace App\Models\Tenant;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventTeam extends Model
{
    use BelongsToTenant;

    protected $table = 'event_team';

    protected $fillable = [
        'tenant_id',
        'event_id',
        'user_id',
        'role_in_event',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}