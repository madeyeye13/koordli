<?php
// app/Models/Tenant/EventMicrositeSettings.php
namespace App\Models\Tenant;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventMicrositeSettings extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'event_id', 'tenant_id',
        'story_enabled', 'gallery_enabled', 'wishes_enabled', 'gifts_enabled',
        'dress_code_enabled', 'countdown_enabled', 'wishes_require_approval',
        'dress_code_colors', 'dress_code_note', 'gate_venue_address',
    ];

    protected $casts = [
        'story_enabled'            => 'boolean',
        'gallery_enabled'          => 'boolean',
        'wishes_enabled'           => 'boolean',
        'gifts_enabled'            => 'boolean',
        'dress_code_enabled'       => 'boolean',
        'countdown_enabled'        => 'boolean',
        'wishes_require_approval'  => 'boolean',
        'gate_venue_address'       => 'boolean',
        'dress_code_colors'        => 'array',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function anySectionEnabled(): bool
    {
        return $this->story_enabled || $this->gallery_enabled || $this->wishes_enabled || $this->gifts_enabled;
    }
}