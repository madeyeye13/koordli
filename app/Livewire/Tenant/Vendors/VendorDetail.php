<?php

namespace App\Livewire\Tenant\Vendors;

use App\Jobs\SendVendorAssignedJob;
use App\Jobs\SendVendorInviteJob;
use App\Models\Central\VendorAccount;
use App\Models\Tenant\Event;
use App\Models\Tenant\Vendor;
use App\Models\Tenant\VendorEventAssignment;
use App\Services\PermissionService;
use App\Traits\WithToast;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.tenant')]
class VendorDetail extends Component
{
    use WithToast;

    public Vendor $vendor;

    public bool   $showAssignForm     = false;
    public ?int   $assign_event_id    = null;
    public string $assign_amount      = '';
    public string $assign_notes       = '';
    public string $assign_amount_paid = '';
    public string $assign_status      = 'pending';

    // Advanced client-involvement fields — all default to today's implicit
    // behavior (planner_selected, not visible to client, budget-paid) so a
    // staff member who never touches these gets identical results to before.
    public string $assign_selection_source          = 'planner_selected';
    public bool   $assign_is_client_visible          = false;
    public bool   $assign_client_can_view_pricing    = false;
    public string $assign_payment_responsibility     = 'planner_pays_from_budget';


    public array $assignConflicts = [];

    // Reviews
    public ?int $reviewAssignId = null;
    public bool $showReviewForm = false;
    public int  $r_professionalism    = 5;
    public int  $r_communication      = 5;
    public int  $r_punctuality        = 5;
    public int  $r_quality_of_service = 5;
    public int  $r_reliability        = 5;
    public int  $r_overall_experience = 5;
    public string $r_comment = '';
    public ?int $editingReviewId = null;

    public ?int   $editAssignId     = null;
    public string $editAmountAgreed = '';
    public string $editAmountPaid   = '';
    public string $editStatus       = '';
    public string $editNotes        = '';

    public bool $showDeleteAssign = false;
    public ?int $deleteAssignId   = null;

    public function mount(int $id): void
    {
        abort_unless(
            app(PermissionService::class)->userCan(auth()->user(), 'vendors.view'),
            403
        );

        $this->vendor = Vendor::with([
            'category',
            'eventAssignments.event',
            'eventAssignments.reviews',
            'unavailableDates' => fn($q) => $q->orderBy('date_from'),
        ])->findOrFail($id);
    }

    public function checkConflicts(): void
    {
        $this->assignConflicts = [];

        if (!$this->assign_event_id) return;

        $event = Event::find($this->assign_event_id);
        if (!$event || !$event->date) return;

        $dateStr = $event->date->format('Y-m-d');

        // Check personal unavailability
        if ($this->vendor->isUnavailableOn($dateStr)) {
            $this->assignConflicts[] = "This vendor has marked themselves unavailable on {$event->date->format('d M Y')}.";
        }

        // Check other event assignments same day
        $conflicts = $this->vendor->conflictingAssignments($dateStr);
        foreach ($conflicts as $eventName) {
            $this->assignConflicts[] = "This vendor is already assigned to \"{$eventName}\" on the same date.";
        }
    }

    public function updated($property): void
    {
        if ($property === 'assign_event_id') {
            $this->checkConflicts();
        }
    }

    public function assignToEvent(): void
    {
        if (!app(PermissionService::class)->userCan(auth()->user(), 'vendors.assign')) {
            $this->toastError('You do not have permission to assign vendors to events.');
            return;
        }

        $this->validate([
            'assign_event_id' => ['required', Rule::exists('events', 'id')->where('tenant_id', auth()->user()->tenant_id)],
            'assign_amount'   => 'nullable|numeric|min:0',
            'assign_status'   => 'required|in:pending,confirmed,cancelled',
            'assign_notes'    => 'nullable|string|max:500',
        ], [], ['assign_event_id' => 'event']);

        $exists = VendorEventAssignment::where('vendor_id', $this->vendor->id)
            ->where('event_id', $this->assign_event_id)
            ->exists();

        if ($exists) {
            $this->addError('assign_event_id', 'This vendor is already assigned to that event.');
            return;
        }

        $assignment = VendorEventAssignment::create([
            'tenant_id'                => auth()->user()->tenant_id,
            'vendor_id'                => $this->vendor->id,
            'event_id'                 => $this->assign_event_id,
            'amount_agreed'            => $this->assign_amount ?: 0,
            'amount_paid'              => $this->assign_amount_paid ?: 0,
            'status'                   => $this->assign_status,
            'notes'                    => $this->assign_notes ?: null,
            'selection_source'         => $this->assign_selection_source,
            'is_client_visible'        => $this->assign_is_client_visible,
            'client_can_view_pricing'  => $this->assign_client_can_view_pricing,
            'payment_responsibility'   => $this->assign_payment_responsibility,
        ]);

        app(\App\Services\Notifications\VendorNotificationService::class)
            ->notifyBookingCreated($assignment);

        // Notify vendor of the new assignment (auto-create portal account if none exists yet)
        if (!empty($this->vendor->email)) {
            $tenant = auth()->user()->tenant;
            $event  = Event::find($this->assign_event_id);

            $existingAccount = \App\Models\Central\VendorAccount::where('tenant_id', $tenant->id)
                ->where('email', $this->vendor->email)
                ->first();

            $isNewAccount = false;
            $generatedPassword = '';

            if (!$existingAccount) {
                $generatedPassword = Str::random(10);
                \App\Models\Central\VendorAccount::create([
                    'tenant_id'        => $tenant->id,
                    'vendor_id'        => $this->vendor->id,
                    'name'             => $this->vendor->contact_name ?? $this->vendor->name,
                    'email'            => $this->vendor->email,
                    'password'         => Hash::make($generatedPassword),
                    'phone'            => $this->vendor->phone,
                    'business_name'    => $this->vendor->name,
                    'is_active'        => true,
                    'password_changed' => false,
                ]);
                $isNewAccount = true;
            }

            \App\Jobs\SendVendorAssignedJob::dispatch(
                $this->vendor->email,
                $this->vendor->contact_name ?? $this->vendor->name,
                $this->vendor->name,
                $event->name,
                $event->date?->format('D, d M Y') ?? 'TBC',
                $tenant->name,
                $isNewAccount,
                $generatedPassword,
                app(\App\Services\FeatureGateService::class)->canAccess($tenant, 'white_label'),
            );
        }

    }
    public function inviteVendor(): void
    {
        if (!app(PermissionService::class)->userCan(auth()->user(), 'vendors.edit')) {
            $this->toastError('You do not have permission to invite vendors to the portal.');
            return;
        }

        if (empty($this->vendor->email)) {
            $this->toastError('This vendor has no email address. Edit the vendor first.');
            return;
        }

        $tenant = auth()->user()->tenant;

        $existing = VendorAccount::where('tenant_id', $tenant->id)
            ->where('email', $this->vendor->email)
            ->first();

        if ($existing) {
            $this->toastWarning('This vendor has already been invited. A portal account exists for ' . $this->vendor->email);
            return;
        }

        $password = Str::random(10);

        VendorAccount::create([
            'tenant_id'        => $tenant->id,
            'vendor_id'        => $this->vendor->id,
            'name'             => $this->vendor->contact_name ?? $this->vendor->name,
            'email'            => $this->vendor->email,
            'password'         => Hash::make($password),
            'phone'            => $this->vendor->phone,
            'business_name'    => $this->vendor->name,
            'is_active'        => true,
            'password_changed' => false,
        ]);

        SendVendorInviteJob::dispatch(
            $this->vendor->email,
            $this->vendor->contact_name ?? $this->vendor->name,
            $this->vendor->name,
            $password,
            $tenant->name,
            app(\App\Services\FeatureGateService::class)->canAccess($tenant, 'white_label'),
        );

        $this->toastSuccess('Portal invite sent to ' . $this->vendor->email);
    }

    public function startEditAssignment(int $assignId): void
    {
        if (!app(PermissionService::class)->userCan(auth()->user(), 'vendors.assign')) {
            $this->toastError('You do not have permission to edit vendor assignments.');
            return;
        }

        $assign = VendorEventAssignment::find($assignId);
        if (!$assign) return;
        $this->editAssignId     = $assignId;
        $this->editAmountAgreed = $assign->amount_agreed;
        $this->editAmountPaid   = $assign->amount_paid;
        $this->editStatus       = $assign->status;
        $this->editNotes        = $assign->notes ?? '';
    }

    public function saveEditAssignment(): void
    {
        if (!app(PermissionService::class)->userCan(auth()->user(), 'vendors.assign')) {
            $this->toastError('You do not have permission to edit vendor assignments.');
            return;
        }

        $this->validate([
            'editAmountAgreed' => 'nullable|numeric|min:0',
            'editAmountPaid'   => 'nullable|numeric|min:0',
            'editStatus'       => 'required|in:pending,confirmed,cancelled',
        ]);

        $assign = VendorEventAssignment::find($this->editAssignId);
        if ($assign) {
            $previousStatus = $assign->status;

            $assign->update([
                'amount_agreed' => $this->editAmountAgreed ?: 0,
                'amount_paid'   => $this->editAmountPaid ?: 0,
                'status'        => $this->editStatus,
                'notes'         => $this->editNotes ?: null,
            ]);

            if ($assign->status !== $previousStatus) {
                app(\App\Services\Notifications\ClientNotificationService::class)
                    ->notifyVendorBookingChanged($assign);

                app(\App\Services\Notifications\VendorNotificationService::class)
                    ->notifyBookingStatusChanged($assign);
            }
        }

        $this->vendor->load('eventAssignments.event');
        $this->reset(['editAssignId', 'editAmountAgreed', 'editAmountPaid', 'editStatus', 'editNotes']);
        $this->toastSuccess('Assignment updated.');
    }

    public function cancelEditAssignment(): void
    {
        $this->reset(['editAssignId', 'editAmountAgreed', 'editAmountPaid', 'editStatus', 'editNotes']);
    }

    public function confirmDeleteAssignment(int $assignId): void
    {
        if (!app(PermissionService::class)->userCan(auth()->user(), 'vendors.assign')) {
            $this->toastError('You do not have permission to remove vendor assignments.');
            return;
        }

        $this->deleteAssignId   = $assignId;
        $this->showDeleteAssign = true;
    }

    public function deleteAssignment(): void
    {
        if (!app(PermissionService::class)->userCan(auth()->user(), 'vendors.assign')) {
            $this->toastError('You do not have permission to remove vendor assignments.');
            $this->showDeleteAssign = false;
            return;
        }

        VendorEventAssignment::find($this->deleteAssignId)?->delete();
        $this->vendor->load('eventAssignments.event');
        $this->showDeleteAssign = false;
        $this->deleteAssignId   = null;
        $this->toastSuccess('Assignment removed.');
    }

   
    public function showReview(int $assignmentId): void
    {
        if (!app(PermissionService::class)->userCan(auth()->user(), 'vendors.edit')) {
            $this->toastError('You do not have permission to review vendors.');
            return;
        }

        $assignment = \App\Models\Tenant\VendorEventAssignment::find($assignmentId);
        if (!$assignment) return;

        if (!$assignment->eventHasEnded()) {
            $this->toastError('You can only review a vendor after the event has ended.');
            return;
        }

        $existing = $assignment->plannerReview();

        if ($existing && !$existing->isEditable()) {
            $this->toastError('This review is locked and can no longer be edited (7-day edit window has passed).');
            return;
        }

        $this->reviewAssignId = $assignmentId;
        $this->editingReviewId = $existing?->id;

        if ($existing) {
            $this->r_professionalism    = $existing->professionalism;
            $this->r_communication      = $existing->communication;
            $this->r_punctuality        = $existing->punctuality;
            $this->r_quality_of_service = $existing->quality_of_service;
            $this->r_reliability        = $existing->reliability;
            $this->r_overall_experience = $existing->overall_experience;
            $this->r_comment            = $existing->comment ?? '';
        } else {
            $this->reset(['r_comment']);
            $this->r_professionalism = $this->r_communication = $this->r_punctuality =
                $this->r_quality_of_service = $this->r_reliability = $this->r_overall_experience = 5;
        }

        $this->showReviewForm = true;
    }

    public function setRating(string $field, int $value): void
    {
        if (!app(PermissionService::class)->userCan(auth()->user(), 'vendors.edit')) {
            return;
        }

        if (in_array($field, ['r_professionalism', 'r_communication', 'r_punctuality', 'r_quality_of_service', 'r_reliability', 'r_overall_experience'])) {
            $this->{$field} = $value;
        }
    }

    public function saveReview(): void
    {
        if (!app(PermissionService::class)->userCan(auth()->user(), 'vendors.edit')) {
            $this->toastError('You do not have permission to review vendors.');
            return;
        }

        $this->validate([
            'r_professionalism'    => 'required|integer|min:1|max:5',
            'r_communication'      => 'required|integer|min:1|max:5',
            'r_punctuality'        => 'required|integer|min:1|max:5',
            'r_quality_of_service' => 'required|integer|min:1|max:5',
            'r_reliability'        => 'required|integer|min:1|max:5',
            'r_overall_experience' => 'required|integer|min:1|max:5',
            'r_comment'            => 'nullable|string|max:1000',
        ]);

        $assignment = \App\Models\Tenant\VendorEventAssignment::find($this->reviewAssignId);
        if (!$assignment) return;

        $data = [
            'tenant_id'                  => auth()->user()->tenant_id,
            'vendor_id'                  => $this->vendor->id,
            'vendor_event_assignment_id' => $assignment->id,
            'event_id'                   => $assignment->event_id,
            'reviewer_type'              => 'planner',
            'reviewer_id'                => auth()->id(),
            'professionalism'            => $this->r_professionalism,
            'communication'              => $this->r_communication,
            'punctuality'                => $this->r_punctuality,
            'quality_of_service'         => $this->r_quality_of_service,
            'reliability'                => $this->r_reliability,
            'overall_experience'         => $this->r_overall_experience,
            'comment'                    => $this->r_comment ?: null,
        ];

        if ($this->editingReviewId) {
            \App\Models\Tenant\VendorReview::find($this->editingReviewId)?->update($data);
        } else {
            \App\Models\Tenant\VendorReview::create($data);
        }

        $this->vendor->refresh();
        $this->showReviewForm = false;
        $this->reviewAssignId = null;
        $this->editingReviewId = null;
        $this->toastSuccess('Review saved.');
    }

    public function render()
    {
        $availableEvents = Event::whereDoesntHave('vendorAssignments', fn($q) =>
            $q->where('vendor_id', $this->vendor->id)
        )->orderBy('date')->get(['id', 'name', 'slug', 'date']);

        return view('livewire.tenant.vendors.vendor-detail', [
            'availableEvents' => $availableEvents,
        ]);
    }
}