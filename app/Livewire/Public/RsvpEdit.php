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

    // Custom answers keyed by question ID
    public array $answers = [];

    public bool   $saved  = false;
    public string $error  = '';

    public function mount(string $slug, string $token): void
    {
        $this->slug  = $slug;
        $this->token = $token;

        $this->response = RsvpResponse::with([
            'rsvpForm.event',
            'rsvpForm.questions' => fn($q) => $q->orderBy('sort_order'),
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

        // Pre-fill custom answers
        foreach ($this->response->rsvpForm->questions as $q) {
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
            'plus_one_count'   => 'integer|min:0|max:20',
        ];

        foreach ($this->response->rsvpForm->questions as $q) {
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
            'plus_one_count'   => $newStatus === 'confirmed' ? $this->plus_one_count : 0,
            'qr_token'         => $qrToken,
        ]);

        // Update custom answers
        foreach ($this->response->rsvpForm->questions as $q) {
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
            SendRsvpConfirmationJob::dispatch(
                $this->respondent_email,
                $this->respondent_name,
                $event->name,
                $eventDate,
                $event->venue ?? '',
                $newStatus,
                $qrToken ?? '',
                $this->response->editUrl(),
                $newStatus === 'confirmed' ? $this->plus_one_count : 0,
            );
        }

        // Notify planner
        $plannerUser = \App\Models\Tenant\User::withoutGlobalScope('tenant')
            ->where('tenant_id', $this->response->tenant_id)
            ->whereHas('roles', fn($q) => $q->where('name', 'company_owner'))
            ->first();

        if ($plannerUser?->email) {
            SendRsvpNotificationJob::dispatch(
                $plannerUser->email,
                $plannerUser->name,
                $this->respondent_name,
                $event->name,
                $newStatus,
                $newStatus === 'confirmed' ? $this->plus_one_count : 0,
                true, // isUpdate
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
                    $newStatus === 'confirmed' ? $this->plus_one_count : 0,
                    true,
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