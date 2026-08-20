<?php

namespace App\Models\Tenant;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientEventAccess extends Model
{
    use BelongsToTenant;

    protected $table = 'client_event_access';

    protected $fillable = ['tenant_id', 'client_id', 'event_id', 'granted_by'];

    public function client(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Central\Client::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}