<?php

namespace App\Livewire\Public;

use App\Jobs\SendRsvpConfirmationJob;
use App\Jobs\SendRsvpNotificationJob;
use App\Models\Central\Client;
use App\Models\Tenant\RsvpResponse;
use App\Models\Tenant\RsvpResponseAnswer;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.rsvp')]
class RsvpEdit extends Component
{
    public RsvpResponse $response;
    public string $slug  = '';
    public string $token = '';

    // System fields
    public string $respondent_name  = '';
    public string $respondent_email = '';
    public string $respondent_phone = '';
    public string $status           = 'confirmed';
    public int    $plus_one_count   = 0;
    public string $decline_reason   = '';

    // Companions — same pattern as the initial RSVP submission
    public array  $companions            = [];
    public string $newCompanionName      = '';
    public string $newCompanionRelation  = '';
    public bool   $comingWithSomeone     = false;

    // Custom answers keyed by question ID
    public array $answers = [];

    public function addCompanion(): void
    {
        if (!$this->newCompanionName || count($this->companions) >= 5) return;

        $this->companions[] = ['name' => $this->newCompanionName, 'relation' => $this->newCompanionRelation ?: null];
        $this->newCompanionName = '';
        $this->newCompanionRelation = '';
    }

    public function removeCompanion(int $index): void
    {
        unset($this->companions[$index]);
        $this->companions = array_values($this->companions);
    }

    public bool   $saved  = false;
    public string $error  = '';

    public function mount(string $slug, string $token): void
    {
        $this->slug  = $slug;
        $this->token = $token;

        $this->response = RsvpResponse::with([
            'rsvpForm.event',
            'rsvpForm.customQuestions' => fn($q) => $q->orderBy('sort_order'),
            'answers',
        ])
            ->where('edit_token', $token)
            ->whereHas('rsvpForm', fn($q) => $q->where('slug', $slug))
            ->firstOrFail();

        // Check if form is still active
        if (!$this->response->rsvpForm->is_active) {
            $this->error = 'This RSVP form is no longer accepting responses.';
        }

        // Pre-fill system fields
        $this->respondent_name  = $this->response->respondent_name;
        $this->respondent_email = $this->response->respondent_email ?? '';
        $this->respondent_phone = $this->response->respondent_phone ?? '';
        $this->status           = $this->response->status;
        $this->plus_one_count   = $this->response->plus_one_count;
        $this->decline_reason   = $this->response->decline_reason ?? '';

        $this->companions = $this->response->companions()
            ->orderBy('sort_order')
            ->get()
            ->map(fn($c) => ['name' => $c->name, 'relation' => $c->relation])
            ->toArray();
        $this->comingWithSomeone = !empty($this->companions);


        // Pre-fill custom answers
        foreach ($this->response->rsvpForm->customQuestions as $q) {
            $existing = $this->response->answers->firstWhere('rsvp_question_id', $q->id);
            $this->answers[$q->id] = $existing?->answer ?? '';
        }
    }

    public function update(): void
    {
        if (!empty($this->error)) return;

        // Build validation rules
        $rules = [
            'respondent_name'  => 'required|string|min:2|max:150',
            'respondent_email' => 'nullable|email|max:150',
            'respondent_phone' => 'nullable|string|max:20',
            'status'           => 'required|in:confirmed,declined',
            'decline_reason'   => 'nullable|string|max:500',
        ];

        foreach ($this->response->rsvpForm->customQuestions as $q) {
            $rules["answers.{$q->id}"] = $q->is_required ? 'required' : 'nullable';
        }

        $this->validate($rules, [], ['respondent_name' => 'full name']);

        $wasConfirmed = $this->response->isConfirmed();
        $newStatus    = $this->status;

        // Generate QR if newly confirmed
        $qrToken = $this->response->qr_token;
        if ($newStatus === 'confirmed' && !$qrToken) {
            $qrToken = 'RSVP-' . strtoupper(\Illuminate\Support\Str::random(10));
        }
        if ($newStatus === 'declined') {
            $qrToken = null;
        }

        $this->response->update([
            'respondent_name'  => $this->respondent_name,
            'respondent_email' => $this->respondent_email ?: null,
            'respondent_phone' => $this->respondent_phone ?: null,
            'status'           => $newStatus,
            'decline_reason'   => $newStatus === 'declined' ? ($this->decline_reason ?: null) : null,
            // Derived from the real, named companions below — matches
            // the same rule the initial RSVP submission already follows.
            'plus_one_count'   => $newStatus === 'confirmed' ? count($this->companions) : 0,
            'qr_token'         => $qrToken,
        ]);

        // Replace the companion list wholesale — simpler and safe here,
        // since companions have no independent identity worth diffing
        // (unlike answers, which are keyed to a specific question).
        $this->response->companions()->delete();
        if ($newStatus === 'confirmed') {
            foreach ($this->companions as $i => $c) {
                \App\Models\Tenant\RsvpCompanion::create([
                    'rsvp_response_id' => $this->response->id,
                    'tenant_id' => $this->response->tenant_id,
                    'name' => $c['name'],
                    'relation' => $c['relation'],
                    'sort_order' => $i,
                ]);
            }
        }

        // Update custom answers
        foreach ($this->response->rsvpForm->customQuestions as $q) {
            $answer = $this->answers[$q->id] ?? null;
            $existing = RsvpResponseAnswer::where('rsvp_response_id', $this->response->id)
                ->where('rsvp_question_id', $q->id)
                ->first();

            if ($answer !== null && $answer !== '') {
                $answerValue = is_array($answer) ? implode(', ', $answer) : $answer;
                if ($existing) {
                    $existing->update(['answer' => $answerValue]);
                } else {
                    RsvpResponseAnswer::create([
                        'tenant_id'        => $this->response->tenant_id,
                        'rsvp_response_id' => $this->response->id,
                        'rsvp_question_id' => $q->id,
                        'answer'           => $answerValue,
                    ]);
                }
            } elseif ($existing) {
                $existing->delete();
            }
        }

        $event     = $this->response->rsvpForm->event;
        $eventDate = $event->date?->format('D, d M Y') ?? 'TBC';

        // Send updated confirmation to guest
        if ($this->respondent_email) {
            $__tenant = \App\Models\Central\Tenant::find($this->response->tenant_id);
            SendRsvpConfirmationJob::dispatch(
                $this->respondent_email,
                $this->respondent_name,
                $this->response->rsvpForm->title,
                $eventDate,
                $event->venue ?? '',
                $newStatus,
                $qrToken ?? '',
                $this->response->editUrl(),
                $newStatus === 'confirmed' ? count($this->companions) : 0,
                $__tenant?->name ?? 'Koordli',
                $__tenant ? app(\App\Services\FeatureGateService::class)->canAccess($__tenant, 'white_label') : false,
                $newStatus === 'confirmed' ? $this->companions : [],
            );
        }

        // Notify planner — uses is_system flag, not a hardcoded role
        // name, matching the fix already applied elsewhere in this app.
        $plannerUser = \App\Models\Tenant\User::withoutGlobalScope('tenant')
            ->where('tenant_id', $this->response->tenant_id)
            ->whereHas('roles', fn($q) => $q->where('is_system', true))
            ->first();

        if ($plannerUser?->email) {
            SendRsvpNotificationJob::dispatch(
                $plannerUser->email,
                $plannerUser->name,
                $this->respondent_name,
                $event->name,
                $newStatus,
                $newStatus === 'confirmed' ? count($this->companions) : 0,
                true, // isUpdate
                $newStatus === 'confirmed' ? $this->companions : [],
            );
        }

        // Notify client
        if ($event->client_email) {
            $client = Client::where('tenant_id', $this->response->tenant_id)
                ->where('email', $event->client_email)
                ->first();
            if ($client) {
                SendRsvpNotificationJob::dispatch(
                    $client->email,
                    $client->name,
                    $this->respondent_name,
                    $event->name,
                    $newStatus,
                    $newStatus === 'confirmed' ? count($this->companions) : 0,
                    true,
                    $newStatus === 'confirmed' ? $this->companions : [],
                );
            }
        }

        $this->saved = true;
    }

    public function render()
    {
        return view('livewire.public.rsvp-edit');
    }
}