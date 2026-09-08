<?php

namespace App\Models\Tenant;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RsvpCompanion extends Model
{
    use BelongsToTenant;

    protected $fillable = ['rsvp_response_id', 'tenant_id', 'name', 'relation', 'sort_order'];

    public function response(): BelongsTo
    {
        return $this->belongsTo(RsvpResponse::class);
    }
}