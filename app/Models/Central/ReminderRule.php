<?php

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Model;

class ReminderRule extends Model
{
    protected $fillable = [
        'key', 'category', 'notification_type', 'pattern', 'custom_interval_minutes',
        'offsets', 'escalate_after_hours', 'escalate_to', 'priority', 'template_key', 'is_active',
    ];

    protected $casts = [
        'offsets'  => 'array',
        'is_active' => 'boolean',
    ];

    public function isRecurring(): bool
    {
        return in_array($this->pattern, ['every_12h', 'every_6h', 'hourly', 'custom']);
    }

    public function intervalMinutes(): int
    {
        return match($this->pattern) {
            'every_12h' => 720,
            'every_6h'  => 360,
            'hourly'    => 60,
            'custom'    => $this->custom_interval_minutes ?? 60,
            default     => 0,
        };
    }
}