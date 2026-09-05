<?php

namespace App\Livewire\Tenant\Vendors;

use App\Enums\DocumentableType;
use App\Models\Central\Client;
use App\Models\Tenant\ClientEventAccess;
use App\Models\Tenant\Event;
use App\Models\Tenant\User;
use App\Models\Tenant\Vendor;
use App\Models\Tenant\VendorCategory;
use App\Models\Tenant\VendorEventAssignment;
use App\Models\Tenant\VendorSuggestion;
use App\Services\PermissionService;
use App\Traits\WithToast;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.tenant')]
class VendorSuggestions extends Component
{
    use WithToast;

    public Event $event;

    public bool $showCreateForm = false;
    public string $name = '';
    public string $category_label = '';
    public ?int $vendor_category_id = null;
    public string $portfolio_link = '';
    public string $notes = '';

    public bool $showRejectModal = false;
    public ?int $rejectingId = null;
    public string $rejectReason = '';

    public bool $showFinalizeForm = false;
    public ?int $finalizingId = null;
    public ?int $finalize_existing_vendor_id = null;
    public string $finalize_amount = '';
    public string $finalize_payment_responsibility = 'planner_pays_from_budget';
    public bool $finalize_is_client_visible = true;
    public bool $finalize_client_can_view_pricing = false;

    public function mount(string $slug): void
    {
        abort_unless(
            app(PermissionService::class)->userCan(auth()->user(), 'vendors.view'),
            403
        );

        $this->event = Event::where('slug', $slug)->firstOrFail();
    }

    private function requireManage(): bool
    {
        if (!app(PermissionService::class)->userCan(auth()->user(), 'vendors.client_involvement.manage')) {
            $this->toastError('You do not have permission to manage vendor suggestions.');
            return false;
        }
        return true;
    }

    public function showCreate(): void
    {
        if (!$this->requireManage()) return;
        $this->reset(['name', 'category_label', 'vendor_category_id', 'portfolio_link', 'notes']);
        $this->showCreateForm = true;
    }

    public function createSuggestion(): void
    {
        if (!$this->requireManage()) return;

        $this->validate([
            'name'           => 'required|string|min:2|max:200',
            'category_label' => 'nullable|string|max:100',
            'portfolio_link' => 'nullable|url|max:500',
            'notes'          => 'nullable|string|max:1000',
        ]);

        $suggestion = VendorSuggestion::create([
            'tenant_id'         => auth()->user()->tenant_id,
            'event_id'          => $this->event->id,
            'suggested_by_type' => User::class,
            'suggested_by_id'   => auth()->id(),
            'name'              => $this->name,
            'category_label'    => $this->category_label ?: null,
            'vendor_category_id'=> $this->vendor_category_id,
            'portfolio_link'    => $this->portfolio_link ?: null,
            'notes'             => $this->notes ?: null,
            'status'            => 'pending',
        ]);

        $this->notifyClient($suggestion, 'suggested');

        $this->showCreateForm = false;
        $this->toastSuccess('Vendor suggestion sent to the client.');
    }

    public function withdrawSuggestion(int $id): void
    {
        if (!$this->requireManage()) return;

        $suggestion = VendorSuggestion::find($id);
        if ($suggestion && $suggestion->isSuggestedByStaff() && $suggestion->isPending()) {
            $suggestion->update(['status' => 'withdrawn']);
            $this->toastSuccess('Suggestion withdrawn.');
        }
    }

    public function approveClientSuggestion(int $id): void
    {
        if (!$this->requireManage()) return;

        $suggestion = VendorSuggestion::find($id);
        if (!$suggestion || !$suggestion->isSuggestedByClient() || !$suggestion->isPending()) return;

        $suggestion->update([
            'status'          => 'approved',
            'decided_by_type' => User::class,
            'decided_by_id'   => auth()->id(),
            'decided_at'      => now(),
        ]);

        $this->notifyClient($suggestion, 'approved');
        $this->toastSuccess('Suggestion approved. Finalize it to add it to the event.');
    }

    public function openReject(int $id): void
    {
        if (!$this->requireManage()) return;
        $this->rejectingId = $id;
        $this->rejectReason = '';
        $this->showRejectModal = true;
    }

    public function rejectClientSuggestion(): void
    {
        if (!$this->requireManage()) return;

        $suggestion = VendorSuggestion::find($this->rejectingId);
        if ($suggestion && $suggestion->isSuggestedByClient() && $suggestion->isPending()) {
            $suggestion->update([
                'status'          => 'rejected',
                'decided_by_type' => User::class,
                'decided_by_id'   => auth()->id(),
                'decision_note'   => $this->rejectReason ?: null,
                'decided_at'      => now(),
            ]);

            $this->notifyClient($suggestion, 'rejected');
        }

        $this->showRejectModal = false;
        $this->toastSuccess('Suggestion rejected.');
    }

    public function showFinalize(int $id): void
    {
        if (!$this->requireManage()) return;

        $suggestion = VendorSuggestion::find($id);
        if (!$suggestion || $suggestion->status !== 'approved' || $suggestion->resulting_assignment_id) return;

        $this->finalizingId = $id;
        $this->finalize_existing_vendor_id = null;
        $this->finalize_amount = '';
        $this->finalize_payment_responsibility = 'planner_pays_from_budget';
        $this->finalize_is_client_visible = true;
        $this->finalize_client_can_view_pricing = false;
        $this->showFinalizeForm = true;
    }

    public function finalizeSuggestion(): void
    {
        if (!$this->requireManage()) return;

        $this->validate([
            'finalize_amount'                 => 'nullable|numeric|min:0',
            'finalize_payment_responsibility' => 'required|in:client_pays_planner,client_pays_vendor_direct,planner_pays_from_budget',
        ]);

        $suggestion = VendorSuggestion::find($this->finalizingId);
        if (!$suggestion) return;

        $isFromClient = $suggestion->isSuggestedByClient();

        if ($this->finalize_existing_vendor_id) {
            // Linking to a known, already-vetted directory vendor.
            $vendor = Vendor::where('tenant_id', auth()->user()->tenant_id)
                ->find($this->finalize_existing_vendor_id);
            $selectionSource = $isFromClient ? 'client_selected' : 'planner_suggested';
        } else {
            // Brand new vendor, created directly from this suggestion.
            $vendor = Vendor::create([
                'tenant_id'          => auth()->user()->tenant_id,
                'vendor_category_id' => $suggestion->vendor_category_id,
                'name'               => $suggestion->name,
                'instagram'          => $suggestion->portfolio_link,
                'is_active'          => true,
                'source'             => $isFromClient ? 'client_added' : 'directory',
                'added_by_client_id' => $isFromClient ? $this->clientForEvent()?->id : null,
            ]);
            // A brand-new, unvetted vendor sourced from the CLIENT is the
            // real liability scenario the disclaimer exists for. A staff-
            // suggested new vendor doesn't need it — staff vetted it.
            $selectionSource = $isFromClient ? 'client_external' : 'planner_suggested';
        }

        $assignment = VendorEventAssignment::create([
            'tenant_id'               => auth()->user()->tenant_id,
            'vendor_id'               => $vendor->id,
            'event_id'                => $this->event->id,
            'amount_agreed'           => $this->finalize_amount ?: 0,
            'status'                  => 'pending',
            'selection_source'        => $selectionSource,
            'client_approval_status'  => 'approved',
            'client_approved_at'      => now(),
            'is_client_visible'       => $this->finalize_is_client_visible,
            'client_can_view_pricing' => $this->finalize_client_can_view_pricing,
            'payment_responsibility'  => $this->finalize_payment_responsibility,
        ]);

        $suggestion->update([
            'resulting_vendor_id'     => $vendor->id,
            'resulting_assignment_id' => $assignment->id,
        ]);

        $this->showFinalizeForm = false;
        $this->toastSuccess('Vendor added to the event.');
    }

    /**
     * Resolves "the client" for this event the same way EventDetail's
     * inviteClient() does — via ClientEventAccess, not a hard FK, since an
     * event can in principle have more than one client account with access.
     * Takes the first one found; multi-client notification fan-out isn't
     * needed for this feature's scope.
     */
    private function clientForEvent(): ?Client
    {
        return ClientEventAccess::where('event_id', $this->event->id)
            ->with('client')
            ->first()?->client;
    }

    private function notifyClient(VendorSuggestion $suggestion, string $action): void
    {
        $client = $this->clientForEvent();
        if (!$client) return;

        $templateKey = match($action) {
            'suggested' => 'vendor_suggestion_received',
            'approved'  => 'vendor_suggestion_decided',
            'rejected'  => 'vendor_suggestion_decided',
            default     => null,
        };
        if (!$templateKey) return;

        app(\App\Services\Notifications\NotificationDispatchService::class)->notify(
            notifiable: $client,
            category: 'vendor_suggestions',
            notificationType: $templateKey,
            templateKey: $templateKey,
            placeholders: [
                'user_name'    => $client->name,
                'vendor_name'  => $suggestion->name,
                'event_name'   => $this->event->name,
                'decision'     => ucfirst($action),
            ],
            priority: 'normal',
            actionUrl: route('client.events.vendors', $this->event->slug),
            actionLabel: 'View Vendors',
            tenantId: auth()->user()->tenant_id,
        );
    }

    public function render()
    {
        $suggestions = VendorSuggestion::where('event_id', $this->event->id)
            ->orderByDesc('created_at')
            ->get();

        return view('livewire.tenant.vendors.vendor-suggestions', [
            'suggestions' => $suggestions,
            'vendors'     => Vendor::where('tenant_id', auth()->user()->tenant_id)
                ->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'categories'  => VendorCategory::where('tenant_id', auth()->user()->tenant_id)
                ->orderBy('sort_order')->get(),
            'canManage'   => app(PermissionService::class)->userCan(auth()->user(), 'vendors.client_involvement.manage'),
        ]);
    }
}