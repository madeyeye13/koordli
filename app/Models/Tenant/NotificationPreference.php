<?php

namespace App\Models\Tenant;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class NotificationPreference extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'notifiable_type', 'notifiable_id', 'category', 'channels'];

    protected $casts = ['channels' => 'array'];

    public static function channelsFor(\Illuminate\Database\Eloquent\Model $notifiable, string $category): array
    {
        $pref = static::where('notifiable_type', get_class($notifiable))
            ->where('notifiable_id', $notifiable->id)
            ->where('category', $category)
            ->first();

        // Default: all channels enabled if no preference row exists yet
        $channels = $pref?->channels ?? ['database', 'mail', 'broadcast'];

        // Push has no per-category toggle in the existing preferences UI —
        // rather than requiring a new UI, it automatically rides along
        // wherever in-app ('database') notifications are already enabled,
        // but ONLY for a notifiable who has actually granted browser push
        // permission (i.e. has a real subscription on file). No subscription
        // means push is silently a no-op regardless of this check.
        if (in_array('database', $channels) && !in_array('push', $channels)) {
            $hasPushSubscription = \App\Models\Tenant\PushSubscription::withoutGlobalScope('tenant')
                ->where('notifiable_type', get_class($notifiable))
                ->where('notifiable_id', $notifiable->id)
                ->exists();

            if ($hasPushSubscription) {
                $channels[] = 'push';
            }
        }

        return $channels;
    }
}