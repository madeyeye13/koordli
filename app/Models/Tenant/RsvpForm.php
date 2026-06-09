<?php

namespace App\Models\Tenant;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RsvpForm extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'uuid',
        'tenant_id',
        'event_id',
        'title',
        'slug',
        'meta_description', 
        'og_image',
        'deadline',
        'guest_limit',
        'branding',
        'questions',
        'ticket_settings',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'branding'        => 'array',
        'questions'       => 'array',
        'ticket_settings' => 'array',
        'deadline'        => 'datetime',
        'is_active'       => 'boolean',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function responses(): HasMany
    {
        return $this->hasMany(RsvpResponse::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}