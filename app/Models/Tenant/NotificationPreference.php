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
        return $pref?->channels ?? ['database', 'mail', 'broadcast'];
    }
}