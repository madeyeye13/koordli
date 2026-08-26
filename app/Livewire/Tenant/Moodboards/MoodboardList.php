<?php

namespace App\Livewire\Tenant\Moodboards;

use App\Models\Tenant\Event;
use App\Models\Tenant\Moodboard;
use App\Services\PermissionService;
use App\Traits\WithToast;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.tenant')]
class MoodboardList extends Component
{
    use WithToast;

    public Event $event;

    public bool $showCreateModal = false;
    public string $newTitle = '';
    public string $newDescription = '';
    public ?int $fromTemplateId = null;

    public function mount(string $slug): void
    {
        abort_unless(app(PermissionService::class)->userCan(auth()->user(), 'moodboards.view'), 403);
        $this->event = Event::where('slug', $slug)->firstOrFail();
    }

    /**
     * -1 (or 0, matching this app's existing "unset = unlimited" convention
     * from hasStorageAvailable()) means unlimited. Counts ALL real
     * moodboards tenant-wide (not per-event), since a plan limit is a
     * tenant-level resource cap, not scoped to any one event.
     */
    private function moodboardLimitReached(): bool
    {
        $limit = app(\App\Services\FeatureGateService::class)->getLimit(auth()->user()->tenant, 'max_moodboards');
        if ($limit <= 0) return false;

        $currentCount = Moodboard::where('tenant_id', auth()->user()->tenant_id)
            ->where('is_template', false)
            ->count();

        return $currentCount >= $limit;
    }

    private function requireManage(): bool
    {
        if (!app(PermissionService::class)->userCan(auth()->user(), 'moodboards.manage')) {
            $this->toastError('You do not have permission to manage moodboards.');
            return false;
        }
        return true;
    }

    public function create(): void
    {
        if (!$this->requireManage()) return;

        if ($this->moodboardLimitReached()) {
            $this->toastError('You\'ve reached your plan\'s moodboard limit. Upgrade your plan to create more.');
            return;
        }

        $this->validate(['newTitle' => 'required|string|min:2|max:150']);

        $moodboard = Moodboard::create([
            'tenant_id'   => auth()->user()->tenant_id,
            'event_id'    => $this->event->id,
            'title'       => $this->newTitle,
            'description' => $this->newDescription ?: null,
            'status'      => 'draft',
            'created_by'  => auth()->id(),
        ]);

        if ($this->fromTemplateId) {
            $this->cloneFromTemplate($this->fromTemplateId, $moodboard);
        }

        $this->showCreateModal = false;
        $this->redirect(route('tenant.moodboards.edit', $moodboard->id), navigate: true);
    }

    private function cloneFromTemplate(int $templateId, Moodboard $target): void
    {
        $template = Moodboard::where('id', $templateId)->where('is_template', true)->first();
        if (!$template) return;

        foreach ($template->sections as $section) {
            $newSection = \App\Models\Tenant\MoodboardSection::create([
                'tenant_id'   => $target->tenant_id,
                'moodboard_id'=> $target->id,
                'title'       => $section->title,
                'pos_x'       => $section->pos_x,
                'pos_y'       => $section->pos_y,
                'width'       => $section->width,
                'height'      => $section->height,
                'sort_order'  => $section->sort_order,
            ]);
            $sectionMap[$section->id] = $newSection->id;
        }

        foreach ($template->items as $item) {
            \App\Models\Tenant\MoodboardItem::create([
                'tenant_id'   => $target->tenant_id,
                'moodboard_id'=> $target->id,
                'section_id'  => isset($sectionMap[$item->section_id]) ? $sectionMap[$item->section_id] : null,
                'type'        => $item->type,
                'pos_x'       => $item->pos_x,
                'pos_y'       => $item->pos_y,
                'width'       => $item->width,
                'height'      => $item->height,
                'sort_order'  => $item->sort_order,
                'data'        => $item->data,
                // document_id intentionally NOT copied — a template's
                // uploaded image belongs to the template, not duplicated
                // storage for every board created from it. Image items
                // from a template start empty and must be re-uploaded.
                'created_by'  => auth()->id(),
            ]);
        }
    }

    public function delete(int $id): void
    {
        if (!$this->requireManage()) return;
        Moodboard::find($id)?->delete();
        $this->toastSuccess('Moodboard deleted.');
    }

    public function render()
    {
        $moodboards = Moodboard::where('event_id', $this->event->id)
            ->where('is_template', false)
            ->orderByDesc('created_at')
            ->get();

        $templates = Moodboard::where('tenant_id', auth()->user()->tenant_id)
            ->where('is_template', true)
            ->where(function ($q) {
                $q->whereNull('event_type_id')->orWhere('event_type_id', $this->event->event_type_id);
            })
            ->get(['id', 'title']);

        $moodboardLimit = app(\App\Services\FeatureGateService::class)->getLimit(auth()->user()->tenant, 'max_moodboards');
        $moodboardCount = Moodboard::where('tenant_id', auth()->user()->tenant_id)->where('is_template', false)->count();

        return view('livewire.tenant.moodboards.moodboard-list', [
            'moodboards'     => $moodboards,
            'templates'      => $templates,
            'canManage'      => app(PermissionService::class)->userCan(auth()->user(), 'moodboards.manage'),
            'moodboardLimit' => $moodboardLimit,
            'moodboardCount' => $moodboardCount,
            'limitReached'   => $this->moodboardLimitReached(),
        ]);
    }
}