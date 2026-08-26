<?php

namespace App\Models\Tenant;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MoodboardStatusHistory extends Model
{
    use BelongsToTenant;

    public $timestamps = false;

    protected $table = 'moodboard_status_history';

    protected $fillable = [
        'tenant_id', 'moodboard_id', 'status', 'note', 'changed_by', 'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function moodboard(): BelongsTo
    {
        return $this->belongsTo(Moodboard::class);
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}