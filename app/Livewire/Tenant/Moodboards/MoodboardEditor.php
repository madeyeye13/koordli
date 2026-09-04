<?php

namespace App\Livewire\Tenant\Moodboards;

use App\Models\Tenant\Moodboard;
use App\Models\Tenant\MoodboardItem;
use App\Models\Tenant\MoodboardSection;
use App\Services\PermissionService;
use App\Traits\WithToast;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.tenant')]
class MoodboardEditor extends Component
{
    use WithToast;

    public Moodboard $moodboard;

    public bool $showAddItemModal = false;
    public string $newItemType = 'text';
    public array $newItemData = [];

    public ?int $fillingSlotId = null;
    public string $fillingType = '';

    public ?int $editingItemId = null;

    public bool $showSectionModal = false;
    public string $newSectionTitle = '';

    public bool $showSaveTemplateModal = false;
    public string $templateTitle = '';

    public bool $showStatusModal = false;
    public string $pendingStatus = '';
    public string $statusNote = '';

    public function mount(int $id): void
    {
        abort_unless(app(PermissionService::class)->userCan(auth()->user(), 'moodboards.view'), 403);
        $this->moodboard = Moodboard::findOrFail($id);
    }

    private function requireManage(): bool
    {
        if (!app(PermissionService::class)->userCan(auth()->user(), 'moodboards.manage')) {
            $this->toastError('You do not have permission to edit this moodboard.');
            return false;
        }
        return true;
    }

    public function toggleClientVisible(): void
    {
        if (!$this->requireManage()) return;

        $wasVisible = $this->moodboard->is_client_visible;
        $this->moodboard->update(['is_client_visible' => !$wasVisible]);

        // Only notify on the transition INTO visible — toggling it off
        // and back on repeatedly shouldn't spam the client each time.
        if (!$wasVisible) {
            app(\App\Services\Notifications\ClientNotificationService::class)->notifyMoodboardShared($this->moodboard);
        }

        $this->toastSuccess($this->moodboard->is_client_visible ? 'Now visible to client.' : 'Hidden from client.');
    }

    public function openStatusChange(string $status): void
    {
        $this->pendingStatus = $status;
        $this->statusNote = '';
        $this->showStatusModal = true;
    }

    public function confirmStatusChange(): void
    {
        if (!$this->requireManage()) return;
        $this->moodboard->changeStatus($this->pendingStatus, $this->statusNote ?: null);
        $this->showStatusModal = false;
        $this->toastSuccess('Status updated.');
    }

    public function addItem(): void
    {
        if (!$this->requireManage()) return;

        $maxOrder = MoodboardItem::where('moodboard_id', $this->moodboard->id)->max('sort_order') ?? 0;

        MoodboardItem::create([
            'tenant_id'    => $this->moodboard->tenant_id,
            'moodboard_id' => $this->moodboard->id,
            'type'         => $this->newItemType,
            'data'         => $this->newItemData,
            'pos_x'        => rand(20, 200),
            'pos_y'        => rand(20, 200),
            'sort_order'   => $maxOrder + 1,
            'created_by'   => auth()->id(),
        ]);

        $this->showAddItemModal = false;
        $this->newItemData = [];
        $this->toastSuccess('Item added.');
    }

    /**
     * Uploads either create a brand new item (no slot targeted) or FILL
     * an existing empty slot in place (fillingSlotId set) — the latter is
     * the new Milanote-style "click a slot, choose what goes here" flow.
     */
    public function attachUploadedDocument(string $type, int $documentId, string $name): void
    {
        if (!$this->requireManage()) return;

        $data = $type === 'file' ? ['name' => $name, 'description' => ''] : ['caption' => $name, 'note' => '', 'source_url' => ''];

        // Sets the board's own cover thumbnail from the first image ever
        // uploaded to it — never overwritten automatically afterward, so
        // it doesn't keep changing every time a new photo is added.
        if ($type === 'image' && !$this->moodboard->cover_document_id) {
            $this->moodboard->update(['cover_document_id' => $documentId]);
        }

        if ($this->fillingSlotId) {
            MoodboardItem::where('id', $this->fillingSlotId)->where('moodboard_id', $this->moodboard->id)
                ->update(['type' => $type, 'document_id' => $documentId, 'data' => $data]);
            $this->fillingSlotId = null;
            $this->fillingType = '';
            $this->toastSuccess('Added.');
            return;
        }

        $maxOrder = MoodboardItem::where('moodboard_id', $this->moodboard->id)->max('sort_order') ?? 0;

        MoodboardItem::create([
            'tenant_id'    => $this->moodboard->tenant_id,
            'moodboard_id' => $this->moodboard->id,
            'type'         => $type,
            'document_id'  => $documentId,
            'data'         => $data,
            'pos_x'        => rand(20, 200),
            'pos_y'        => rand(20, 200),
            'sort_order'   => $maxOrder + 1,
            'created_by'   => auth()->id(),
        ]);

        $this->toastSuccess('Added to board.');
    }

    public function openSlotFill(int $itemId, string $type): void
    {
        $this->fillingSlotId = $itemId;
        $this->fillingType = $type;

        if (in_array($type, ['text', 'color', 'link'])) {
            $this->newItemData = [];
        }
    }

    public function fillTextColorOrLinkSlot(): void
    {
        if (!$this->requireManage() || !$this->fillingSlotId) return;

        MoodboardItem::where('id', $this->fillingSlotId)->where('moodboard_id', $this->moodboard->id)
            ->update(['type' => $this->fillingType, 'data' => $this->newItemData]);

        $this->fillingSlotId = null;
        $this->fillingType = '';
        $this->newItemData = [];
        $this->toastSuccess('Added.');
    }

    public function cancelSlotFill(): void
    {
        $this->fillingSlotId = null;
        $this->fillingType = '';
    }

    /**
     * Adds a set of empty, pre-positioned slots in an appealing collage
     * arrangement — the Milanote-style starting layout. A small, fixed
     * set of hand-tuned layouts by item count, not a random scatter.
     */
    public function addPlaceholderLayout(string $layout): void
    {
        if (!$this->requireManage()) return;

        $layouts = [
            'grid_3' => [
                ['pos_x'=>20,'pos_y'=>20,'width'=>220,'height'=>280],
                ['pos_x'=>260,'pos_y'=>20,'width'=>220,'height'=>280],
                ['pos_x'=>500,'pos_y'=>20,'width'=>220,'height'=>280],
            ],
            'grid_6' => [
                ['pos_x'=>20,'pos_y'=>20,'width'=>260,'height'=>340],
                ['pos_x'=>300,'pos_y'=>20,'width'=>180,'height'=>160],
                ['pos_x'=>300,'pos_y'=>200,'width'=>180,'height'=>160],
                ['pos_x'=>500,'pos_y'=>20,'width'=>200,'height'=>340],
                ['pos_x'=>720,'pos_y'=>20,'width'=>160,'height'=>200],
                ['pos_x'=>720,'pos_y'=>240,'width'=>320,'height'=>120],
            ],
            'grid_7' => [
                ['pos_x'=>20,'pos_y'=>20,'width'=>300,'height'=>240],
                ['pos_x'=>340,'pos_y'=>20,'width'=>160,'height'=>240],
                ['pos_x'=>520,'pos_y'=>20,'width'=>160,'height'=>115],
                ['pos_x'=>520,'pos_y'=>145,'width'=>160,'height'=>115],
                ['pos_x'=>20,'pos_y'=>280,'width'=>200,'height'=>160],
                ['pos_x'=>240,'pos_y'=>280,'width'=>200,'height'=>160],
                ['pos_x'=>460,'pos_y'=>280,'width'=>220,'height'=>160],
            ],
            'grid_9' => [
                ['pos_x'=>20,'pos_y'=>20,'width'=>180,'height'=>180],
                ['pos_x'=>220,'pos_y'=>20,'width'=>180,'height'=>180],
                ['pos_x'=>420,'pos_y'=>20,'width'=>180,'height'=>180],
                ['pos_x'=>620,'pos_y'=>20,'width'=>180,'height'=>180],
                ['pos_x'=>20,'pos_y'=>220,'width'=>180,'height'=>180],
                ['pos_x'=>220,'pos_y'=>220,'width'=>180,'height'=>180],
                ['pos_x'=>420,'pos_y'=>220,'width'=>180,'height'=>180],
                ['pos_x'=>620,'pos_y'=>220,'width'=>180,'height'=>180],
                ['pos_x'=>20,'pos_y'=>420,'width'=>780,'height'=>100],
            ],
            'grid_10' => [
                ['pos_x'=>20,'pos_y'=>20,'width'=>240,'height'=>300],
                ['pos_x'=>280,'pos_y'=>20,'width'=>140,'height'=>140],
                ['pos_x'=>280,'pos_y'=>180,'width'=>140,'height'=>140],
                ['pos_x'=>440,'pos_y'=>20,'width'=>140,'height'=>140],
                ['pos_x'=>440,'pos_y'=>180,'width'=>140,'height'=>140],
                ['pos_x'=>600,'pos_y'=>20,'width'=>200,'height'=>300],
                ['pos_x'=>820,'pos_y'=>20,'width'=>160,'height'=>145],
                ['pos_x'=>820,'pos_y'=>175,'width'=>160,'height'=>145],
                ['pos_x'=>20,'pos_y'=>340,'width'=>380,'height'=>140],
                ['pos_x'=>420,'pos_y'=>340,'width'=>380,'height'=>140],
            ],
            'grid_12' => [
                ['pos_x'=>20,'pos_y'=>20,'width'=>150,'height'=>150],
                ['pos_x'=>190,'pos_y'=>20,'width'=>150,'height'=>150],
                ['pos_x'=>360,'pos_y'=>20,'width'=>150,'height'=>150],
                ['pos_x'=>530,'pos_y'=>20,'width'=>150,'height'=>150],
                ['pos_x'=>700,'pos_y'=>20,'width'=>150,'height'=>150],
                ['pos_x'=>20,'pos_y'=>190,'width'=>150,'height'=>150],
                ['pos_x'=>190,'pos_y'=>190,'width'=>150,'height'=>150],
                ['pos_x'=>360,'pos_y'=>190,'width'=>150,'height'=>150],
                ['pos_x'=>530,'pos_y'=>190,'width'=>150,'height'=>150],
                ['pos_x'=>700,'pos_y'=>190,'width'=>150,'height'=>150],
                ['pos_x'=>20,'pos_y'=>360,'width'=>410,'height'=>110],
                ['pos_x'=>450,'pos_y'=>360,'width'=>400,'height'=>110],
            ],
        ];

        $slots = $layouts[$layout] ?? $layouts['grid_3'];
        $maxOrder = MoodboardItem::where('moodboard_id', $this->moodboard->id)->max('sort_order') ?? 0;

        foreach ($slots as $i => $slot) {
            MoodboardItem::create([
                'tenant_id'    => $this->moodboard->tenant_id,
                'moodboard_id' => $this->moodboard->id,
                'type'         => 'empty',
                'pos_x'        => $slot['pos_x'],
                'pos_y'        => $slot['pos_y'],
                'width'        => $slot['width'],
                'height'       => $slot['height'],
                'sort_order'   => $maxOrder + $i + 1,
                'created_by'   => auth()->id(),
            ]);
        }

        $this->toastSuccess('Layout added — click any box to fill it in.');
    }

    public function updateItemPosition(int $itemId, int $x, int $y): void
    {
        if (!$this->requireManage()) return;
        MoodboardItem::where('id', $itemId)->where('moodboard_id', $this->moodboard->id)
            ->update(['pos_x' => $x, 'pos_y' => $y]);
    }

    public function updateItemSize(int $itemId, int $width, int $height): void
    {
        if (!$this->requireManage()) return;
        MoodboardItem::where('id', $itemId)->where('moodboard_id', $this->moodboard->id)
            ->update(['width' => max(80, $width), 'height' => max(80, $height)]);
    }

    public function moveItemUp(int $itemId): void
    {
        if (!$this->requireManage()) return;
        $this->swapOrder($itemId, -1);
    }

    public function moveItemDown(int $itemId): void
    {
        if (!$this->requireManage()) return;
        $this->swapOrder($itemId, 1);
    }

    private function swapOrder(int $itemId, int $direction): void
    {
        $items = MoodboardItem::where('moodboard_id', $this->moodboard->id)->orderBy('sort_order')->get();
        $index = $items->search(fn($i) => $i->id === $itemId);
        if ($index === false) return;

        $swapIndex = $index + $direction;
        if ($swapIndex < 0 || $swapIndex >= $items->count()) return;

        $a = $items[$index];
        $b = $items[$swapIndex];
        $tmp = $a->sort_order;
        $a->update(['sort_order' => $b->sort_order]);
        $b->update(['sort_order' => $tmp]);
    }

    public string $editingItemType = '';

    public function startEditItem(int $itemId): void
    {
        $item = MoodboardItem::find($itemId);
        if (!$item) return;
        $this->editingItemId = $itemId;
        $this->editingItemType = $item->type;
        $this->newItemData = $item->data ?? [];
    }

    /**
     * Replaces the underlying media on an EXISTING item — used from the
     * Edit modal's "Replace Image/File" action. Unlike attachUploadedDocument()
     * (which fills an empty slot or creates a new item), this updates the
     * SAME item's document_id in place, so its position/size/section
     * assignment are all preserved.
     */
    public function replaceItemMedia(int $itemId, string $type, int $documentId, string $name): void
    {
        if (!$this->requireManage()) return;

        $item = MoodboardItem::where('id', $itemId)->where('moodboard_id', $this->moodboard->id)->first();
        if (!$item) return;

        $data = $type === 'file' ? ['name' => $name, 'description' => $item->data['description'] ?? ''] : ['caption' => $item->data['caption'] ?? $name, 'note' => $item->data['note'] ?? '', 'source_url' => $item->data['source_url'] ?? ''];

        $item->update(['type' => $type, 'document_id' => $documentId, 'data' => $data]);
        $this->toastSuccess('Replaced.');
    }

    public function saveItemEdit(): void
    {
        if (!$this->requireManage()) return;
        MoodboardItem::where('id', $this->editingItemId)->update(['data' => $this->newItemData]);
        $this->editingItemId = null;
        $this->toastSuccess('Saved.');
    }

    public function updateColorItem(int $itemId, string $value, ?string $name): void
    {
        if (!$this->requireManage()) return;

        $item = MoodboardItem::where('id', $itemId)->where('moodboard_id', $this->moodboard->id)->first();
        if (!$item) return;

        $data = $item->data ?? [];
        $data['value'] = $value;
        $data['name'] = $name ?: null;

        $item->update(['data' => $data]);
    }

    public function assignSection(int $itemId, ?int $sectionId): void
    {
        if (!$this->requireManage()) return;
        MoodboardItem::where('id', $itemId)->where('moodboard_id', $this->moodboard->id)
            ->update(['section_id' => $sectionId]);
    }

    public function duplicateItem(int $itemId): void
    {
        if (!$this->requireManage()) return;
        $item = MoodboardItem::find($itemId);
        if (!$item) return;

        $maxOrder = MoodboardItem::where('moodboard_id', $this->moodboard->id)->max('sort_order') ?? 0;

        // document_id is shared with the original, never re-uploaded —
        // no duplicate storage cost. deleteItem() only ever removes the
        // MoodboardItem row, never the underlying Document, so two items
        // safely sharing one document_id is not a conflict.
        MoodboardItem::create([
            'tenant_id'    => $item->tenant_id,
            'moodboard_id' => $item->moodboard_id,
            'section_id'   => $item->section_id,
            'type'         => $item->type,
            'pos_x'        => $item->pos_x + 20,
            'pos_y'        => $item->pos_y + 20,
            'width'        => $item->width,
            'height'       => $item->height,
            'sort_order'   => $maxOrder + 1,
            'data'         => $item->data,
            'document_id'  => $item->document_id,
            'linked_type'  => $item->linked_type,
            'linked_id'    => $item->linked_id,
            'created_by'   => auth()->id(),
        ]);

        $this->toastSuccess('Duplicated.');
    }

    public function addNote(): void
    {
        if (!$this->requireManage()) return;

        $maxOrder = MoodboardItem::where('moodboard_id', $this->moodboard->id)->max('sort_order') ?? 0;

        MoodboardItem::create([
            'tenant_id'    => $this->moodboard->tenant_id,
            'moodboard_id' => $this->moodboard->id,
            'type'         => 'note',
            'data'         => ['content' => ''],
            'pos_x'        => rand(40, 300),
            'pos_y'        => rand(40, 300),
            'width'        => 200,
            'height'       => 160,
            'sort_order'   => $maxOrder + 1,
            'created_by'   => auth()->id(),
        ]);
    }

    public function updateNoteContent(int $itemId, string $content): void
    {
        if (!$this->requireManage()) return;

        $item = MoodboardItem::where('id', $itemId)->where('moodboard_id', $this->moodboard->id)->first();
        if (!$item) return;

        $data = $item->data ?? [];
        $data['content'] = $content;
        $item->update(['data' => $data]);
    }

    public function deleteItem(int $itemId): void
    {
        if (!$this->requireManage()) return;
        MoodboardItem::find($itemId)?->delete();
        $this->toastSuccess('Item removed.');
    }

    public function createSection(): void
    {
        if (!$this->requireManage()) return;
        $this->validate(['newSectionTitle' => 'required|string|max:100']);

        $maxOrder = MoodboardSection::where('moodboard_id', $this->moodboard->id)->max('sort_order') ?? 0;

        MoodboardSection::create([
            'tenant_id'    => $this->moodboard->tenant_id,
            'moodboard_id' => $this->moodboard->id,
            'title'        => $this->newSectionTitle,
            'pos_x'        => 20,
            'pos_y'        => 20,
            'sort_order'   => $maxOrder + 1,
        ]);

        $this->showSectionModal = false;
        $this->newSectionTitle = '';
        $this->toastSuccess('Section added.');
    }

    public function updateSectionPosition(int $sectionId, int $x, int $y): void
    {
        if (!$this->requireManage()) return;
        MoodboardSection::where('id', $sectionId)->update(['pos_x' => $x, 'pos_y' => $y]);
    }

    /**
     * Deletes the section itself only — items that were inside it are
     * NOT deleted, just unassigned (section_id set to null), so nothing
     * a planner added gets silently destroyed by removing its grouping.
     */
    public function deleteSection(int $sectionId): void
    {
        if (!$this->requireManage()) return;

        MoodboardItem::where('section_id', $sectionId)->update(['section_id' => null]);
        MoodboardSection::find($sectionId)?->delete();

        $this->toastSuccess('Section removed.');
    }

    public function restoreItem(int $itemId): void
    {
        if (!$this->requireManage()) return;
        MoodboardItem::withTrashed()->where('id', $itemId)->restore();
    }

    public function restoreSection(int $sectionId): void
    {
        if (!$this->requireManage()) return;
        MoodboardSection::withTrashed()->where('id', $sectionId)->restore();
    }

    public function saveAsTemplate(): void
    {
        if (!$this->requireManage()) return;
        $this->validate(['templateTitle' => 'required|string|max:150']);

        $template = Moodboard::create([
            'tenant_id'            => $this->moodboard->tenant_id,
            'event_id'             => null,
            'title'                => $this->templateTitle,
            'description'          => $this->moodboard->description,
            'status'               => 'draft',
            'is_template'          => true,
            'industry_profile_id'  => auth()->user()->tenant->industry_profile_id,
            'created_by'           => auth()->id(),
        ]);

        $sectionMap = [];
        foreach ($this->moodboard->sections as $section) {
            $newSection = MoodboardSection::create([
                'tenant_id'    => $template->tenant_id,
                'moodboard_id' => $template->id,
                'title'        => $section->title,
                'pos_x'        => $section->pos_x,
                'pos_y'        => $section->pos_y,
                'width'        => $section->width,
                'height'       => $section->height,
                'sort_order'   => $section->sort_order,
            ]);
            $sectionMap[$section->id] = $newSection->id;
        }

        foreach ($this->moodboard->items as $item) {
            MoodboardItem::create([
                'tenant_id'    => $template->tenant_id,
                'moodboard_id' => $template->id,
                'section_id'   => $item->section_id ? ($sectionMap[$item->section_id] ?? null) : null,
                'type'         => $item->type,
                'pos_x'        => $item->pos_x,
                'pos_y'        => $item->pos_y,
                'width'        => $item->width,
                'height'       => $item->height,
                'sort_order'   => $item->sort_order,
                'data'         => $item->data,
                'created_by'   => auth()->id(),
            ]);
        }

        $this->showSaveTemplateModal = false;
        $this->toastSuccess('Template saved.');
    }

    public bool $showHistoryModal = false;

    public function render()
    {
        $this->moodboard->load(['items.section', 'sections', 'statusHistory.changedBy']);

        return view('livewire.tenant.moodboards.moodboard-editor', [
            'canManage' => app(PermissionService::class)->userCan(auth()->user(), 'moodboards.manage'),
        ]);
    }
}