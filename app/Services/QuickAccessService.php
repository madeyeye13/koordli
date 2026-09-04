<?php

namespace App\Services;

use App\Models\Central\VendorAccount;
use App\Models\Tenant\ActivityTimeline;
use App\Models\Tenant\Event;
use App\Models\Tenant\EventTeam;
use App\Models\Tenant\QuickAccessLink;
use App\Models\Tenant\RunsheetItem;
use App\Models\Tenant\Task;
use App\Models\Tenant\User;
use App\Models\Tenant\VendorEventAssignment;
use Illuminate\Support\Collection;

class QuickAccessService
{
    public function findValidLink(string $token): ?QuickAccessLink
    {
        return QuickAccessLink::withoutGlobalScope('tenant')
            ->where('token', $token)
            ->where('is_active', true)
            ->first();
    }

    public function personName(QuickAccessLink $link): string
    {
        return $link->person()?->name ?? 'there';
    }

    /**
     * Menu content differs by person type, matching what's actually
     * update-able today (Rule: never invent a permission that doesn't
     * reflect real app behavior — vendor runsheet access is ownership-
     * gated in this app, not permission-gated, so it's checked that way
     * here too, consistent with VendorRunsheet::updateStatus()).
     */
    public function menuFor(QuickAccessLink $link): array
    {
        $menu = [];

        if ($link->person_type === User::class) {
            $person = $link->person();
            if (!$person) return [];

            if (app(PermissionService::class)->userCan($person, 'tasks.edit')) {
                $menu[] = ['key' => 'update_task', 'label' => 'Update a Task'];
            }
            if (app(PermissionService::class)->userCan($person, 'runsheet.manage')) {
                $menu[] = ['key' => 'update_runsheet', 'label' => 'Update Runsheet Item'];
            }
            if (app(PermissionService::class)->userCan($person, 'checklists.manage')) {
                $menu[] = ['key' => 'update_checklist', 'label' => 'Complete Checklist Item'];
            }
        } elseif ($link->person_type === VendorAccount::class) {
            // Ownership-gated, not permission-gated — matches real vendor
            // access today (see note in the prior message).
            $menu[] = ['key' => 'update_task', 'label' => 'Update a Task'];
            $menu[] = ['key' => 'update_runsheet', 'label' => 'Update Runsheet Item'];
        }

        return $menu;
    }

    /**
     * Events this person can act on, plus a "General / Company Tasks"
     * pseudo-option when relevant — the graceful path for tasks with no
     * event attached, per spec. Only offered for the update_task action,
     * since runsheet items are inherently event-bound (a runsheet always
     * belongs to one event).
     */
    public function eventsFor(QuickAccessLink $link, string $actionKey): Collection
    {
        $events = collect();

        if ($link->person_type === User::class) {
            $eventIds = EventTeam::where('user_id', $link->person_id)
                ->where('tenant_id', $link->tenant_id)
                ->pluck('event_id');

            $events = Event::whereIn('id', $eventIds)->orderByDesc('date')->get(['id', 'name', 'slug']);
        } elseif ($link->person_type === VendorAccount::class) {
            $vendorAccount = VendorAccount::find($link->person_id);
            $vendorId = $vendorAccount?->vendor_id;

            if ($vendorId) {
                $eventIds = VendorEventAssignment::where('vendor_id', $vendorId)
                    ->where('tenant_id', $link->tenant_id)
                    ->pluck('event_id');

                $events = Event::whereIn('id', $eventIds)->orderByDesc('date')->get(['id', 'name', 'slug']);
            }
        }

        if ($actionKey === 'update_task' && $link->person_type === User::class) {
            $hasGeneralTasks = Task::where('tenant_id', $link->tenant_id)
                ->where('assigned_to', $link->person_id)
                ->whereNull('event_id')
                ->exists();

            if ($hasGeneralTasks) {
                $events->prepend((object) ['id' => null, 'name' => 'General / Company Tasks', 'slug' => null]);
            }
        }

        return $events;
    }

    /**
     * $eventId === null means "General / Company Tasks" — only reachable
     * for staff, per eventsFor()'s gating above.
     */
    public function tasksFor(QuickAccessLink $link, ?int $eventId): Collection
    {
        if ($link->person_type === User::class) {
            return Task::where('tenant_id', $link->tenant_id)
                ->where('assigned_to', $link->person_id)
                ->where('event_id', $eventId)
                ->orderBy('due_date')
                ->get();
        }

        if ($link->person_type === VendorAccount::class) {
            return Task::where('tenant_id', $link->tenant_id)
                ->where('vendor_account_id', $link->person_id)
                ->where('event_id', $eventId)
                ->orderBy('due_date')
                ->get();
        }

        return collect();
    }

    /**
     * Only ever the STAFF path — Checklist has no vendor involvement
     * anywhere in its design, matching the menuFor() gating above.
     * Excludes items already converted to a Task: a converted item's
     * completion is driven entirely by its linked Task's own status
     * (see ChecklistItem::syncCompletionFromTask()), so allowing a
     * direct toggle here would create two competing sources of truth
     * for the same completion state.
     */
    public function checklistItemsFor(QuickAccessLink $link, int $eventId): Collection
    {
        $checklist = \App\Models\Tenant\Checklist::where('event_id', $eventId)
            ->where('tenant_id', $link->tenant_id)
            ->first();

        if (!$checklist) return collect();

        return \App\Models\Tenant\ChecklistItem::where('checklist_id', $checklist->id)
            ->whereNull('task_id')
            ->orderBy('phase')
            ->orderBy('sort_order')
            ->get();
    }

    public function toggleChecklistItem(QuickAccessLink $link, int $itemId): array
    {
        $item = \App\Models\Tenant\ChecklistItem::where('id', $itemId)
            ->where('tenant_id', $link->tenant_id)
            ->whereNull('task_id')
            ->first();

        if (!$item) {
            return ['ok' => false, 'error' => 'Checklist item not found.'];
        }

        $item->update([
            'is_completed' => !$item->is_completed,
            'completed_at' => !$item->is_completed ? now() : null,
        ]);

        $this->logAction($link, $item, 'checklist_item_toggled', ($item->is_completed ? 'Marked complete' : 'Marked incomplete') . ' via quick access');

        return ['ok' => true];
    }

    public function runsheetItemsFor(QuickAccessLink $link, int $eventId): Collection
    {
        $filterColumn = $link->person_type === VendorAccount::class ? 'vendor_id' : 'assigned_to';
        $filterValue  = $link->person_type === VendorAccount::class
            ? VendorAccount::find($link->person_id)?->vendor_id
            : $link->person_id;

        if (!$filterValue) return collect();

        return RunsheetItem::where('tenant_id', $link->tenant_id)
            ->where($filterColumn, $filterValue)
            ->whereHas('runsheet', fn($q) => $q->where('event_id', $eventId))
            ->with('runsheet')
            ->orderBy('start_time')
            ->get();
    }

    public function updateTaskStatus(QuickAccessLink $link, int $taskId, string $status): array
    {
        $task = $link->person_type === User::class
            ? Task::where('id', $taskId)->where('tenant_id', $link->tenant_id)->where('assigned_to', $link->person_id)->first()
            : Task::where('id', $taskId)->where('tenant_id', $link->tenant_id)->where('vendor_account_id', $link->person_id)->first();

        if (!$task) {
            return ['ok' => false, 'error' => 'Task not found.'];
        }

        // Real Eloquent update — Task's own updated() hook (the client
        // milestone notification) fires automatically here, no extra
        // integration code needed.
        $task->update(['status' => $status]);

        $this->logAction($link, $task, 'task_status_updated', "Status changed to {$status} via quick access");

        return ['ok' => true];
    }

    public function updateRunsheetStatus(QuickAccessLink $link, int $itemId, string $status, ?string $delayNote = null): array
    {
        $filterColumn = $link->person_type === VendorAccount::class ? 'vendor_id' : 'assigned_to';
        $filterValue  = $link->person_type === VendorAccount::class
            ? VendorAccount::find($link->person_id)?->vendor_id
            : $link->person_id;

        $item = RunsheetItem::where('id', $itemId)
            ->where('tenant_id', $link->tenant_id)
            ->where($filterColumn, $filterValue)
            ->first();

        if (!$item) {
            return ['ok' => false, 'error' => 'Runsheet item not found.'];
        }

        $updateData = ['status' => $status];
        if ($status === 'delayed' && $delayNote) {
            $updateData['notes'] = $delayNote;
        }

        $item->update($updateData);

        if ($status === 'delayed') {
            if ($link->person_type === VendorAccount::class) {
                app(\App\Services\Notifications\VendorNotificationService::class)
                    ->notifyRunsheetDelayedByVendor($item->fresh(['runsheet.event']));
            } else {
                event(new \App\Events\RunsheetItemDelayed($item->fresh(['dependents.assignedTo', 'runsheet.event'])));
            }
        }

        $this->logAction($link, $item, 'runsheet_status_updated', "Status changed to {$status} via quick access");

        return ['ok' => true];
    }

    private function logAction(QuickAccessLink $link, \Illuminate\Database\Eloquent\Model $subject, string $eventType, string $description): void
    {
        $link->update(['last_used_at' => now()]);

        ActivityTimeline::log(
            subject: $subject,
            eventType: $eventType,
            description: $description,
            actorType: $link->person_type,
            actorId: $link->person_id,
        );
    }
}