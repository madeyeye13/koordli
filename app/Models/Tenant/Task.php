<?php

namespace App\Models\Tenant;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Central\VendorAccount;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Task extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'event_id', 'task_category_id',
        'assigned_to', 'vendor_account_id', 'created_by',
        'title', 'description', 'priority', 'status',
        'due_date', 'completed_at', 'sort_order', 'depends_on',
    ];

    protected $casts = [
        'priority'     => TaskPriority::class,
        'status'       => TaskStatus::class,
        'due_date'     => 'date',
        'completed_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Task $task) {
            if (empty($task->created_by)) {
                $task->created_by = auth()->id();
            }
            if (empty($task->tenant_id)) {
                $task->tenant_id = auth()->user()->tenant_id;
            }
        });

        static::updating(function (Task $task) {
            if ($task->isDirty('status') && $task->status === TaskStatus::Done) {
                $task->completed_at = now();
            }
            if ($task->isDirty('status') && $task->status !== TaskStatus::Done) {
                $task->completed_at = null;
            }
        });
    }

    // Scopes
    public function scopeEventTasks($query)
    {
        return $query->whereNotNull('event_id');
    }

    public function scopeCompanyTasks($query)
    {
        return $query->whereNull('event_id');
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    public function scopeForVendor($query, int $vendorAccountId)
    {
        return $query->where('vendor_account_id', $vendorAccountId);
    }

    public function scopePending($query)
    {
        return $query->whereNotIn('status', [
            TaskStatus::Done->value,
            TaskStatus::Cancelled->value,
        ]);
    }

    public function scopeOverdue($query)
    {
        return $query->pending()
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', today());
    }

    // Relationships
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(TenantTaskCategory::class, 'task_category_id');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function assignedVendor(): BelongsTo
    {
        return $this->belongsTo(VendorAccount::class, 'vendor_account_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function dependency(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'depends_on');
    }

    public function dependents(): HasMany
    {
        return $this->hasMany(Task::class, 'depends_on');
    }

    // Helpers
    public function isOverdue(): bool
    {
        return $this->due_date
            && $this->due_date->isPast()
            && !in_array($this->status, [TaskStatus::Done, TaskStatus::Cancelled]);
    }

    public function isCompleted(): bool
    {
        return $this->status === TaskStatus::Done;
    }

    public function assigneeName(): string
    {
        if ($this->assignedTo) return $this->assignedTo->name;
        if ($this->assignedVendor) return $this->assignedVendor->business_name ?? $this->assignedVendor->name;
        return '—';
    }
}