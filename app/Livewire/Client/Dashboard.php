<?php

namespace App\Livewire\Client;

use App\Models\Tenant\Event;
use App\Models\Tenant\VendorReview;
use App\Traits\WithToast;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.client')]
class Dashboard extends Component
{
    use WithToast;

    public ?int $reviewAssignId  = null;
    public bool $showReviewForm  = false;
    public ?int $editingReviewId = null;

    public int $r_professionalism    = 5;
    public int $r_communication      = 5;
    public int $r_punctuality        = 5;
    public int $r_quality_of_service = 5;
    public int $r_reliability        = 5;
    public int $r_overall_experience = 5;
    public string $r_comment = '';

    public function showReview(int $assignmentId): void
    {
        $client = auth('client')->user();

        $assignment = \App\Models\Tenant\VendorEventAssignment::withoutGlobalScope('tenant')
            ->where('tenant_id', $client->tenant_id)
            ->with('event')
            ->find($assignmentId);

        if (!$assignment) return;

        // Confirm this assignment belongs to one of the client's events
        $ownsEvent = Event::withoutGlobalScope('tenant')
            ->where('tenant_id', $client->tenant_id)
            ->where('client_email', $client->email)
            ->where('id', $assignment->event_id)
            ->exists();

        if (!$ownsEvent) {
            $this->toastError('You cannot review this vendor.');
            return;
        }

        if (!$assignment->eventHasEnded()) {
            $this->toastError('You can only review a vendor after the event has ended.');
            return;
        }

        $existing = VendorReview::withoutGlobalScope('tenant')
            ->where('vendor_event_assignment_id', $assignmentId)
            ->where('reviewer_type', 'client')
            ->first();

        if ($existing && !$existing->isEditable()) {
            $this->toastError('This review is locked and can no longer be edited.');
            return;
        }

        $this->reviewAssignId  = $assignmentId;
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
        if (in_array($field, ['r_professionalism', 'r_communication', 'r_punctuality', 'r_quality_of_service', 'r_reliability', 'r_overall_experience'])) {
            $this->{$field} = $value;
        }
    }

    public function saveReview(): void
    {
        $this->validate([
            'r_professionalism'    => 'required|integer|min:1|max:5',
            'r_communication'      => 'required|integer|min:1|max:5',
            'r_punctuality'        => 'required|integer|min:1|max:5',
            'r_quality_of_service' => 'required|integer|min:1|max:5',
            'r_reliability'        => 'required|integer|min:1|max:5',
            'r_overall_experience' => 'required|integer|min:1|max:5',
            'r_comment'            => 'nullable|string|max:1000',
        ]);

        $client = auth('client')->user();

        $assignment = \App\Models\Tenant\VendorEventAssignment::withoutGlobalScope('tenant')
            ->where('tenant_id', $client->tenant_id)
            ->find($this->reviewAssignId);

        if (!$assignment) return;

        $data = [
            'tenant_id'                  => $client->tenant_id,
            'vendor_id'                  => $assignment->vendor_id,
            'vendor_event_assignment_id' => $assignment->id,
            'event_id'                   => $assignment->event_id,
            'reviewer_type'              => 'client',
            'reviewer_id'                => $client->id,
            'professionalism'            => $this->r_professionalism,
            'communication'              => $this->r_communication,
            'punctuality'                => $this->r_punctuality,
            'quality_of_service'         => $this->r_quality_of_service,
            'reliability'                => $this->r_reliability,
            'overall_experience'         => $this->r_overall_experience,
            'comment'                    => $this->r_comment ?: null,
        ];

        if ($this->editingReviewId) {
            VendorReview::withoutGlobalScope('tenant')->find($this->editingReviewId)?->update($data);
        } else {
            VendorReview::create($data);
        }

        $this->showReviewForm  = false;
        $this->reviewAssignId  = null;
        $this->editingReviewId = null;
        $this->toastSuccess('Review saved. Thank you for your feedback!');
    }

    public function render()
    {
        $client = auth('client')->user();

        $grantedEventIds = \App\Models\Tenant\ClientEventAccess::where('client_id', $client->id)->pluck('event_id');

        $events = Event::withoutGlobalScope('tenant')
            ->where('tenant_id', $client->tenant_id)
            ->where(function ($q) use ($client, $grantedEventIds) {
                $q->where('client_email', $client->email)      // legacy fallback, kept for safety
                  ->orWhereIn('id', $grantedEventIds);          // authoritative going forward
            })
            ->with([
                'eventType',
                'status',
                'budget.items',
                'budget.clientPayments',
                'rsvpForm.responses',
                'vendorAssignments.vendor',
                'vendorAssignments.reviews',
            ])
            ->orderBy('date', 'asc')
            ->get();

        return view('livewire.client.dashboard', compact('events'));
    }
}