<?php

namespace App\Models\Tenant;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ReminderLog extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'reminder_rule_id', 'notifiable_type', 'notifiable_id',
        'subject_type', 'subject_id', 'channel', 'delivery_status', 'is_manual', 'sent_at', 'read_at',
    ];

    protected $casts = [
        'is_manual' => 'boolean',
        'sent_at'   => 'datetime',
        'read_at'   => 'datetime',
    ];

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }
}