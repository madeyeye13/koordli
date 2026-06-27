<?php

namespace App\Models\Tenant;

use App\Enums\RunsheetItemStatus;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RunsheetItem extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'runsheet_id',
        'title',
        'description',
        'start_time',
        'end_time',
        'assigned_to',
        'vendor_id',
        'status',
        'notes',
        'sort_order',
        'depends_on',
    ];

    protected $casts = [
        'status'     => RunsheetItemStatus::class,
        'start_time' => 'datetime',
        'end_time'   => 'datetime',
    ];

    public function runsheet(): BelongsTo
    {
        return $this->belongsTo(Runsheet::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function dependency(): BelongsTo
    {
        return $this->belongsTo(RunsheetItem::class, 'depends_on');
    }

    public function statusColor(): string
    {
        return match($this->status->value) {
            'in_progress' => '#F59E0B',
            'done'        => '#10B981',
            'delayed'     => '#EF4444',
            default       => '#78716C',
        };
    }

    public function statusLabel(): string
    {
        return match($this->status->value) {
            'in_progress' => 'In Progress',
            'done'        => 'Done',
            'delayed'     => 'Delayed',
            default       => 'Pending',
        };
    }


}