<?php

namespace App\Livewire\Client\Moodboards;

use App\Models\Central\Client;
use App\Models\Tenant\ClientEventAccess;
use App\Models\Tenant\Moodboard;
use App\Traits\WithToast;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.client')]
class MoodboardView extends Component
{
    use WithToast;

    public Moodboard $moodboard;

    public bool $showRequestChangesModal = false;
    public string $changesNote = '';

    public function mount(int $id): void
    {
        $this->moodboard = Moodboard::withoutGlobalScope('tenant')->findOrFail($id);

        $hasAccess = ClientEventAccess::withoutGlobalScope('tenant')
            ->where('client_id', auth('client')->id())
            ->where('event_id', $this->moodboard->event_id)
            ->exists();

        // Both checks required: the client must have access to the EVENT,
        // AND this specific board must be explicitly marked client-visible
        // — matches the same layered-gating principle used elsewhere in
        // this app (e.g. Vendor Selection's is_client_visible flag).
        abort_unless($hasAccess && $this->moodboard->is_client_visible, 403);
    }

    public function approve(): void
    {
        $this->moodboard->changeStatus('approved', 'Approved by client.');
        $this->notifyStaff('approved', null);
        $this->toastSuccess('Approved. Thank you!');
    }

    public function openRequestChanges(): void
    {
        $this->changesNote = '';
        $this->showRequestChangesModal = true;
    }

    public function submitRequestChanges(): void
    {
        $this->validate(['changesNote' => 'required|string|min:3|max:1000']);

        $this->moodboard->changeStatus('in_review', 'Client requested changes: ' . $this->changesNote);
        $this->notifyStaff('changes_requested', $this->changesNote);

        $this->showRequestChangesModal = false;
        $this->toastSuccess('Your feedback was sent.');
    }

    /**
     * Same is_system-based owner resolution used throughout this app
     * (MediaUploadController, VendorList, RsvpFormPage) — never a
     * hardcoded role-name lookup, since that silently breaks if a tenant
     * renames their owner role.
     */
    private function notifyStaff(string $action, ?string $note): void
    {
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($this->moodboard->tenant_id);

        $owners = \App\Models\Tenant\User::withoutGlobalScope('tenant')
            ->where('tenant_id', $this->moodboard->tenant_id)
            ->whereHas('roles', fn($q) => $q->where('is_system', true))
            ->get();

        $client = auth('client')->user();

        foreach ($owners as $owner) {
            app(\App\Services\Notifications\NotificationDispatchService::class)->notify(
                notifiable: $owner,
                category: 'moodboards',
                notificationType: 'moodboard_client_' . $action,
                templateKey: 'moodboard_client_' . $action,
                placeholders: [
                    'user_name'    => $owner->name,
                    'client_name'  => $client->name,
                    'board_title'  => $this->moodboard->title,
                    'event_name'   => $this->moodboard->event?->name ?? 'the event',
                    'note'         => $note ?? '',
                ],
                priority: 'normal',
                actionUrl: route('tenant.moodboards.edit', $this->moodboard->id),
                actionLabel: 'View Moodboard',
                subject: $this->moodboard,
                tenantId: $this->moodboard->tenant_id,
            );
        }
    }

    public function render()
    {
        $this->moodboard->load(['items.section', 'sections']);

        // Computes the real bounding box of every item/section so the
        // static canvas is always tall enough to show everything, with
        // no independent scroll mechanism needed (unlike the tenant
        // editor's canvas, which needs scroll since it's actively being
        // worked in and can exceed any fixed height during editing).
        $maxY = 0;
        foreach ($this->moodboard->items->where('type', '!=', 'empty') as $item) {
            $maxY = max($maxY, $item->pos_y + $item->height);
        }
        foreach ($this->moodboard->sections as $section) {
            $maxY = max($maxY, $section->pos_y + $section->height);
        }
        $canvasHeight = max(600, $maxY + 40); // 40px bottom padding, 600px floor

        return view('livewire.client.moodboards.moodboard-view', compact('canvasHeight'));
    }
}