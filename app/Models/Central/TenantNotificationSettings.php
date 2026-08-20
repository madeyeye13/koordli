<?php

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Model;

class TenantNotificationSettings extends Model
{
    protected $fillable = [
        'tenant_id', 'reminders_enabled', 'business_hours_only', 'business_hours_start', 'business_hours_end',
        'weekend_reminders', 'dnd_start', 'dnd_end', 'default_escalation_hours', 'digest_mode',
    ];

    protected $casts = [
        'reminders_enabled'   => 'boolean',
        'business_hours_only' => 'boolean',
        'weekend_reminders'   => 'boolean',
    ];

    public static function forTenant(int $tenantId): self
    {
        return static::firstOrCreate(['tenant_id' => $tenantId]);
    }

    /**
     * Is `$time` (defaults to now) inside quiet hours? Critical priority always bypasses this.
     */
    public function isWithinQuietHours(?\Carbon\Carbon $time = null): bool
    {
        $time = $time ?? now();

        if (!$this->dnd_start || !$this->dnd_end) {
            return false;
        }

        $start = \Carbon\Carbon::parse($this->dnd_start)->setDateFrom($time);
        $end   = \Carbon\Carbon::parse($this->dnd_end)->setDateFrom($time);

        if ($end->lessThan($start)) {
            return $time->greaterThanOrEqualTo($start) || $time->lessThanOrEqualTo($end);
        }

        return $time->between($start, $end);
    }

    public function isWithinBusinessHours(?\Carbon\Carbon $time = null): bool
    {
        if (!$this->business_hours_only) {
            return true;
        }

        $time = $time ?? now();

        if (!$this->weekend_reminders && $time->isWeekend()) {
            return false;
        }

        if (!$this->business_hours_start || !$this->business_hours_end) {
            return true;
        }

        $start = \Carbon\Carbon::parse($this->business_hours_start)->setDateFrom($time);
        $end   = \Carbon\Carbon::parse($this->business_hours_end)->setDateFrom($time);

        return $time->between($start, $end);
    }
}