<?php

namespace App\Models\Tenant;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class LabelAssignment extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'label_id',
        'labelable_type',
        'labelable_id',
    ];

    public function label(): BelongsTo
    {
        return $this->belongsTo(TenantLabel::class, 'label_id');
    }

    public function labelable(): MorphTo
    {
        return $this->morphTo();
    }
}