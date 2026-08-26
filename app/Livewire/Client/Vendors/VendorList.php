<?php

namespace App\Livewire\Client\Vendors;

use App\Enums\DocumentableType;
use App\Models\Central\Client;
use App\Models\Tenant\ClientEventAccess;
use App\Models\Tenant\Event;
use App\Models\Tenant\User;
use App\Models\Tenant\VendorEventAssignment;
use App\Models\Tenant\VendorSuggestion;
use App\Traits\WithToast;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.client')]
class VendorList extends Component
{
    use WithToast;

    public Event $event;
    public string $involvementLevel = 'none';

    public bool $showSuggestForm = false;
    public string $name = '';
    public string $category_label = '';
    public string $portfolio_link = '';
    public string $notes = '';

    public bool $showDisclaimerModal = false;
    public ?int $disclaimerAssignmentId = null;

    public function mount(string $slug): void
    {
        $this->event = Event::withoutGlobalScope('tenant')->where('slug', $slug)->firstOrFail();

        abort_unless($this->hasAccess(), 403);

        $this->involvementLevel = $this->event->effectiveClientVendorInvolvementLevel();

        abort_if($this->involvementLevel === 'none', 403);
    }

    private function hasAccess(): bool
    {
        return ClientEventAccess::withoutGlobalScope('tenant')
            ->where('client_id', auth('client')->id())
            ->where('event_id', $this->event->id)
            ->exists();
    }

    private function canApproveOrReject(): bool
    {
        return in_array($this->involvementLevel, ['approve_selections', 'full_participation']);
    }

    private function canSuggest(): bool
    {
        return $this->involvementLevel === 'full_participation';
    }

    public function showCreate(): void
    {
        if (!$this->canSuggest()) {
            $this->toastError('This event does not allow suggesting your own vendors.');
            return;
        }
        $this->reset(['name', 'category_label', 'portfolio_link', 'notes']);
        $this->showSuggestForm = true;
    }

    public function submitSuggestion(): void
    {
        if (!$this->canSuggest()) {
            $this->toastError('This event does not allow suggesting your own vendors.');
            return;
        }

        $this->validate([
            'name'           => 'required|string|min:2|max:200',
            'category_label' => 'nullable|string|max:100',
            'portfolio_link' => 'nullable|url|max:500',
            'notes'          => 'nullable|string|max:1000',
        ]);

        $client = auth('client')->user();

        $suggestion = VendorSuggestion::create([
            'tenant_id'         => $this->event->tenant_id,
            'event_id'          => $this->event->id,
            'suggested_by_type' => Client::class,
            'suggested_by_id'   => $client->id,
            'name'              => $this->name,
            'category_label'    => $this->category_label ?: null,
            'portfolio_link'    => $this->portfolio_link ?: null,
            'notes'             => $this->notes ?: null,
            'status'            => 'pending',
        ]);

        $this->notifyOwners($suggestion, 'client_suggested_vendor', 'suggested a vendor for');

        $this->showSuggestForm = false;
        $this->toastSuccess('Vendor suggestion sent.');
    }

    public function approveStaffSuggestion(int $id): void
    {
        if (!$this->canApproveOrReject()) return;

        $suggestion = VendorSuggestion::withoutGlobalScope('tenant')
            ->where('id', $id)
            ->where('event_id', $this->event->id)
            ->first();

        if (!$suggestion || !$suggestion->isSuggestedByStaff() || !$suggestion->isPending()) return;

        $client = auth('client')->user();

        $suggestion->update([
            'status'          => 'approved',
            'decided_by_type' => Client::class,
            'decided_by_id'   => $client->id,
            'decided_at'      => now(),
        ]);

        $this->notifyOwners($suggestion, 'client_vendor_decision', 'approved your vendor suggestion for');
        $this->toastSuccess('Vendor approved.');
    }

    public function rejectStaffSuggestion(int $id): void
    {
        if (!$this->canApproveOrReject()) return;

        $suggestion = VendorSuggestion::withoutGlobalScope('tenant')
            ->where('id', $id)
            ->where('event_id', $this->event->id)
            ->first();

        if (!$suggestion || !$suggestion->isSuggestedByStaff() || !$suggestion->isPending()) return;

        $client = auth('client')->user();

        $suggestion->update([
            'status'          => 'rejected',
            'decided_by_type' => Client::class,
            'decided_by_id'   => $client->id,
            'decided_at'      => now(),
        ]);

        $this->notifyOwners($suggestion, 'client_vendor_decision', 'declined your vendor suggestion for');
        $this->toastSuccess('Vendor declined.');
    }

    public function openDisclaimer(int $assignmentId): void
    {
        $this->disclaimerAssignmentId = $assignmentId;
        $this->showDisclaimerModal = true;
    }

    public function acknowledgeDisclaimer(): void
    {
        $assignment = VendorEventAssignment::withoutGlobalScope('tenant')
            ->where('id', $this->disclaimerAssignmentId)
            ->where('event_id', $this->event->id)
            ->first();

        if ($assignment) {
            $assignment->update(['disclaimer_acknowledged_at' => now()]);
        }

        $this->showDisclaimerModal = false;
        $this->toastSuccess('Acknowledged.');
    }

    /**
     * Same is_system-based owner resolution as MediaUploadController's
     * guest-upload notification — explicit team context required since
     * this route has no middleware setting it automatically.
     */
    private function notifyOwners(VendorSuggestion $suggestion, string $templateKey, string $actionPhrase): void
    {
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($this->event->tenant_id);

        $owners = User::withoutGlobalScope('tenant')
            ->where('tenant_id', $this->event->tenant_id)
            ->whereHas('roles', fn($q) => $q->where('is_system', true))
            ->get();

        foreach ($owners as $owner) {
            app(\App\Services\Notifications\NotificationDispatchService::class)->notify(
                notifiable: $owner,
                category: 'vendor_suggestions',
                notificationType: $templateKey,
                templateKey: $templateKey,
                placeholders: [
                    'user_name'      => $owner->name,
                    'vendor_name'    => $suggestion->name,
                    'event_name'     => $this->event->name,
                    'action_phrase'  => $actionPhrase,
                ],
                priority: 'normal',
                actionUrl: route('tenant.events.vendor-suggestions', $this->event->slug),
                actionLabel: 'View Suggestions',
                tenantId: $this->event->tenant_id,
            );
        }
    }

    public function render()
    {
        // view_only clients can't act on suggestions at all, so showing a
        // pending suggestion with nothing clickable is a confusing dead
        // end — the section is simply hidden for that tier instead.
        $suggestions = ($this->canApproveOrReject() || $this->canSuggest())
            ? VendorSuggestion::withoutGlobalScope('tenant')
                ->where('event_id', $this->event->id)
                ->whereIn('status', ['pending'])
                ->orderByDesc('created_at')
                ->get()
            : collect();

        $confirmedAssignments = VendorEventAssignment::withoutGlobalScope('tenant')
            ->with('vendor.category')
            ->where('event_id', $this->event->id)
            ->where('is_client_visible', true)
            ->orderByDesc('created_at')
            ->get();

        return view('livewire.client.vendors.vendor-list', [
            'suggestions'          => $suggestions,
            'confirmedAssignments' => $confirmedAssignments,
            'canApproveOrReject'   => $this->canApproveOrReject(),
            'canSuggest'           => $this->canSuggest(),
        ]);
    }
}