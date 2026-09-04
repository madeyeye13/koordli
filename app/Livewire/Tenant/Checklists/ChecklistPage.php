<?php

namespace App\Livewire\Tenant\Checklists;

use App\Enums\ChecklistPhase;
use App\Enums\TaskStatus;
use App\Models\Tenant\Checklist;
use App\Models\Tenant\ChecklistItem;
use App\Models\Tenant\ChecklistTemplate;
use App\Models\Tenant\Event;
use App\Models\Tenant\Task;
use App\Services\PermissionService;
use App\Traits\WithToast;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.tenant')]
class ChecklistPage extends Component
{
    use WithToast;

    public Event $event;
    public Checklist $checklist;

    public bool $showAddModal = false;
    public string $newTitle = '';
    public string $newDescription = '';
    public string $newPhase = '12_plus_months';

    public ?int $editingItemId = null;
    public string $editTitle = '';
    public string $editDescription = '';
    public string $editPhase = '';

    public ?int $convertingItemId = null;
    public string $convertDueDate = '';
    public ?int $convertAssignee = null;

    public array $selectedItemIds = [];
    public bool $bulkConverting = false;

    public bool $showTemplateModal = false;
    public ?int $applyTemplateId = null;

    public function mount(string $slug): void
    {
        abort_unless(app(PermissionService::class)->userCan(auth()->user(), 'checklists.view'), 403);

        $this->event = Event::where('slug', $slug)->firstOrFail();

        // Auto-create on first visit — no "+ New Checklist" button exists
        // anywhere, per the confirmed one-per-event design.
        $this->checklist = Checklist::firstOrCreate(
            ['event_id' => $this->event->id],
            ['tenant_id' => auth()->user()->tenant_id, 'created_by' => auth()->id()]
        );
    }

    private function requireManage(): bool
    {
        if (!app(PermissionService::class)->userCan(auth()->user(), 'checklists.manage')) {
            $this->toastError('You do not have permission to manage this checklist.');
            return false;
        }
        return true;
    }

    public function toggleClientVisible(): void
    {
        if (!$this->requireManage()) return;
        $this->checklist->update(['is_client_visible' => !$this->checklist->is_client_visible]);
        $this->toastSuccess($this->checklist->is_client_visible ? 'Now visible to client.' : 'Hidden from client.');
    }

    public function addItem(): void
    {
        if (!$this->requireManage()) return;
        $this->validate(['newTitle' => 'required|string|min:2|max:200']);

        $maxOrder = ChecklistItem::where('checklist_id', $this->checklist->id)
            ->where('phase', $this->newPhase)->max('sort_order') ?? 0;

        ChecklistItem::create([
            'tenant_id'    => $this->checklist->tenant_id,
            'checklist_id' => $this->checklist->id,
            'title'        => $this->newTitle,
            'description'  => $this->newDescription ?: null,
            'phase'        => $this->newPhase,
            'sort_order'   => $maxOrder + 1,
            'created_by'   => auth()->id(),
        ]);

        $this->reset(['newTitle', 'newDescription', 'showAddModal']);
        $this->toastSuccess('Item added.');
    }

    public function toggleComplete(int $itemId): void
    {
        if (!$this->requireManage()) return;
        $item = ChecklistItem::find($itemId);
        if (!$item || $item->isConverted()) return; // converted items follow the Task, never toggled directly

        $item->update([
            'is_completed' => !$item->is_completed,
            'completed_at' => !$item->is_completed ? now() : null,
        ]);
    }

    public function startEdit(int $itemId): void
    {
        $item = ChecklistItem::find($itemId);
        if (!$item) return;
        $this->editingItemId = $itemId;
        $this->editTitle = $item->title;
        $this->editDescription = $item->description ?? '';
        $this->editPhase = $item->phase;
    }

    public function saveEdit(): void
    {
        if (!$this->requireManage()) return;
        $this->validate(['editTitle' => 'required|string|min:2|max:200']);

        ChecklistItem::where('id', $this->editingItemId)->update([
            'title'       => $this->editTitle,
            'description' => $this->editDescription ?: null,
            'phase'       => $this->editPhase,
        ]);

        $this->editingItemId = null;
        $this->toastSuccess('Saved.');
    }

    public function deleteItem(int $itemId): void
    {
        if (!$this->requireManage()) return;
        ChecklistItem::find($itemId)?->delete();
        $this->toastSuccess('Item removed.');
    }

    public function openConvert(int $itemId): void
    {
        $this->convertingItemId = $itemId;
        $this->convertDueDate = '';
        $this->convertAssignee = null;
    }

    public function confirmConvert(): void
    {
        if (!$this->requireManage()) return;

        // Shared by both single-item and bulk conversion — bulk mode sets
        // selectedItemIds and leaves convertingItemId null, so this method
        // handles either case from one place rather than duplicating the
        // Task::create() logic twice.
        $itemIds = $this->bulkConverting ? $this->selectedItemIds : [$this->convertingItemId];
        $count = 0;

        foreach ($itemIds as $itemId) {
            $item = ChecklistItem::find($itemId);
            if (!$item || $item->isConverted()) continue;

            $task = Task::create([
                'tenant_id'   => $item->tenant_id,
                'event_id'    => $this->event->id,
                'title'       => $item->title,
                'description' => $item->description,
                'priority'    => 'normal',
                'status'      => 'todo',
                'due_date'    => $this->convertDueDate ?: null,
                'assigned_to' => $this->convertAssignee,
            ]);

            $item->update(['task_id' => $task->id]);
            $count++;
        }

        $this->convertingItemId = null;
        $this->bulkConverting = false;
        $this->selectedItemIds = [];
        $this->toastSuccess($count === 1 ? 'Converted to a task.' : "Converted {$count} items to tasks.");
    }

    public function openBulkConvert(): void
    {
        if (empty($this->selectedItemIds)) return;
        $this->bulkConverting = true;
        $this->convertDueDate = '';
        $this->convertAssignee = null;
    }

    public function cancelBulkConvert(): void
    {
        $this->bulkConverting = false;
    }

    public function openApplyTemplate(): void
    {
        $this->applyTemplateId = null;
        $this->showTemplateModal = true;
    }

    public function applyTemplate(): void
    {
        if (!$this->requireManage() || !$this->applyTemplateId) return;

        $template = ChecklistTemplate::with('items')->find($this->applyTemplateId);
        if (!$template) return;

        foreach ($template->items as $templateItem) {
            $maxOrder = ChecklistItem::where('checklist_id', $this->checklist->id)
                ->where('phase', $templateItem->phase)->max('sort_order') ?? 0;

            ChecklistItem::create([
                'tenant_id'         => $this->checklist->tenant_id,
                'checklist_id'      => $this->checklist->id,
                'title'             => $templateItem->title,
                'description'       => $templateItem->description,
                'phase'             => $templateItem->phase,
                'days_before_event' => $templateItem->days_before_event,
                'sort_order'        => $maxOrder + 1,
                'created_by'        => auth()->id(),
            ]);
        }

        $this->showTemplateModal = false;
        $this->toastSuccess('Template applied — items added to your checklist.');
    }

    public function render()
    {
        $this->checklist->load('items.task');

        // Pull-based sync — Task never knows Checklist exists, per the
        // light-coupling principle. Cheap: only runs for items that are
        // actually converted, on this one page's own render.
        foreach ($this->checklist->items->whereNotNull('task_id') as $item) {
            $item->syncCompletionFromTask();
        }
        $this->checklist->refresh()->load('items.task');

        $itemsByPhase = [];
        foreach (ChecklistPhase::cases() as $phase) {
            $itemsByPhase[$phase->value] = $this->checklist->items
                ->where('phase', $phase->value)
                ->sortBy('sort_order');
        }

        $tenantProfileId = auth()->user()->tenant->industry_profile_id;

        // A "Full-Service" tenant explicitly does NOT want narrowing —
        // treat this exactly like "no profile set" (show every real
        // category's templates), since full_service itself will never
        // have its own tagged content.
        $isFullService = \App\Models\Central\IndustryProfile::find($tenantProfileId)?->key === 'full_service';

        $templates = ChecklistTemplate::where('tenant_id', auth()->user()->tenant_id)
            ->when($tenantProfileId && !$isFullService, function ($q) use ($tenantProfileId) {
                $q->where(function ($sub) use ($tenantProfileId) {
                    $sub->whereNull('industry_profile_id')->orWhere('industry_profile_id', $tenantProfileId);
                });
            })
            ->get(['id', 'title']);

        $eligibleStaff = \App\Models\Tenant\EventTeam::where('event_id', $this->event->id)
            ->with('user')->get()->pluck('user')->filter();

        return view('livewire.tenant.checklists.checklist-page', [
            'canManage'      => app(PermissionService::class)->userCan(auth()->user(), 'checklists.manage'),
            'itemsByPhase'   => $itemsByPhase,
            'phases'         => ChecklistPhase::cases(),
            'overall'        => $this->checklist->overallProgress(),
            'templates'      => $templates,
            'eligibleStaff'  => $eligibleStaff,
        ]);
    }
}