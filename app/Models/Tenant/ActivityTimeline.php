<?php

namespace App\Models\Tenant;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ActivityTimeline extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'subject_type', 'subject_id', 'event_type', 'description', 'actor_type', 'actor_id'];

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public static function log(\Illuminate\Database\Eloquent\Model $subject, string $eventType, string $description, ?string $actorType = null, ?int $actorId = null): self
    {
        return static::create([
            'tenant_id'   => $subject->tenant_id,
            'subject_type' => get_class($subject),
            'subject_id'   => $subject->id,
            'event_type'   => $eventType,
            'description'  => $description,
            'actor_type'   => $actorType,
            'actor_id'     => $actorId,
        ]);
    }
}