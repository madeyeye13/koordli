<?php

namespace App\Models\Tenant;

use App\Enums\ChecklistPhase;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChecklistItem extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'checklist_id', 'title', 'description', 'phase',
        'days_before_event', 'is_completed', 'completed_at', 'task_id',
        'sort_order', 'created_by',
    ];

    protected $casts = [
        'is_completed' => 'boolean',
        'completed_at' => 'datetime',
    ];

    public function checklist(): BelongsTo
    {
        return $this->belongsTo(Checklist::class);
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function phaseEnum(): ChecklistPhase
    {
        return ChecklistPhase::from($this->phase);
    }

    public function isConverted(): bool
    {
        return $this->task_id !== null;
    }

    /**
     * Completion is a single source of truth: once converted, the item's
     * own is_completed should ALWAYS reflect the linked Task's real
     * status — never a second, independently-editable completion flag.
     * Call this instead of touching is_completed directly on a converted
     * item.
     */
    public function syncCompletionFromTask(): void
    {
        if (!$this->task_id || !$this->task) return;

        $isDone = $this->task->status === \App\Enums\TaskStatus::Done;
        $this->update([
            'is_completed' => $isDone,
            'completed_at' => $isDone ? now() : null,
        ]);
    }
}