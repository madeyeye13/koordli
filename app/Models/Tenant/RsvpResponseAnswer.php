<?php

namespace App\Models\Tenant;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RsvpResponseAnswer extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'rsvp_response_id', 'rsvp_question_id', 'answer',
    ];

    public function response(): BelongsTo
    {
        return $this->belongsTo(RsvpResponse::class, 'rsvp_response_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(RsvpQuestion::class, 'rsvp_question_id');
    }
}