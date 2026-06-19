<?php

namespace App\Livewire\Public;

use App\Jobs\SendRsvpConfirmationJob;
use App\Jobs\SendRsvpNotificationJob;
use App\Models\Central\Client;
use App\Models\Central\Tenant;
use App\Models\Tenant\RsvpForm;
use App\Models\Tenant\RsvpResponse;
use App\Models\Tenant\RsvpResponseAnswer;
use App\Models\Tenant\User;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.rsvp')]
class RsvpFormPage extends Component
{
    public RsvpForm $rsvpForm;

    public string $respondent_name  = '';
    public string $respondent_email = '';
    public string $respondent_phone = '';
    public string $status           = 'confirmed';
    public int    $plus_one_count   = 0;
    public array  $answers          = [];

    public bool   $submitted = false;
    public string $error     = '';
    public ?RsvpResponse $response = null;

    public function mount(string $slug): void
    {
        $this->rsvpForm = RsvpForm::with([
            'event',
            'questions' => fn($q) => $q->orderBy('sort_order'),
        ])
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        if (!$this->rsvpForm->event->rsvp_enabled) {
            abort(404);
        }

        if ($this->rsvpForm->isDeadlinePassed()) {
            $this->error = 'RSVP is now closed for this event.';
        }

        foreach ($this->rsvpForm->questions ?? [] as $q) {
            $this->answers[$q->id] = '';
        }
    }

    public function submit(): void
    {
        if ($this->rsvpForm->isDeadlinePassed()) {
            $this->error = 'RSVP is now closed for this event.';
            return;
        }

        if ($this->rsvpForm->isAtCapacity()) {
            $this->error = 'Sorry, this event has reached its guest capacity.';
            return;
        }

        $rules = [
            'respondent_name'  => 'required|string|min:2|max:150',
            'respondent_email' => 'nullable|email|max:150',
            'respondent_phone' => 'nullable|string|max:20',
            'status'           => 'required|in:confirmed,declined',
            'plus_one_count'   => 'integer|min:0|max:20',
        ];

        foreach ($this->rsvpForm->questions ?? [] as $q) {
            $rules["answers.{$q->id}"] = $q->is_required ? 'required' : 'nullable';
        }

        $this->validate($rules, [], ['respondent_name' => 'full name']);

        if ($this->respondent_email) {
            $duplicate = RsvpResponse::where('rsvp_form_id', $this->rsvpForm->id)
                ->where('respondent_email', $this->respondent_email)
                ->exists();

            if ($duplicate) {
                $this->error = 'An RSVP with this email already exists. Use your edit link to update your response.';
                return;
            }
        }

        $qrToken = $this->status === 'confirmed'
            ? 'RSVP-' . strtoupper(Str::random(10))
            : null;

        $this->response = RsvpResponse::create([
            'uuid'             => Str::uuid(),
            'tenant_id'        => $this->rsvpForm->tenant_id,
            'event_id'         => $this->rsvpForm->event_id,
            'rsvp_form_id'     => $this->rsvpForm->id,
            'respondent_name'  => $this->respondent_name,
            'respondent_email' => $this->respondent_email ?: null,
            'respondent_phone' => $this->respondent_phone ?: null,
            'status'           => $this->status,
            'plus_one_count'   => $this->status === 'confirmed' ? $this->plus_one_count : 0,
            'qr_token'         => $qrToken,
        ]);

        foreach ($this->rsvpForm->questions ?? [] as $q) {
            if (isset($this->answers[$q->id]) && $this->answers[$q->id] !== '') {
                RsvpResponseAnswer::create([
                    'tenant_id'        => $this->rsvpForm->tenant_id,
                    'rsvp_response_id' => $this->response->id,
                    'rsvp_question_id' => $q->id,
                    'answer'           => is_array($this->answers[$q->id])
                        ? implode(', ', $this->answers[$q->id])
                        : $this->answers[$q->id],
                ]);
            }
        }

        $event     = $this->rsvpForm->event;
        $eventDate = $event->date?->format('D, d M Y') ?? 'TBC';

        if ($this->respondent_email && $qrToken) {
            SendRsvpConfirmationJob::dispatch(
                $this->respondent_email,
                $this->respondent_name,
                $event->name,
                $eventDate,
                $event->venue ?? '',
                $this->status,
                $qrToken,
                $this->response->editUrl(),
                $this->status === 'confirmed' ? $this->plus_one_count : 0,
            );
        }

        $plannerUser = User::withoutGlobalScope('tenant')
            ->where('tenant_id', $this->rsvpForm->tenant_id)
            ->whereHas('roles', fn($q) => $q->where('name', 'company_owner'))
            ->first();

        if ($plannerUser?->email) {
            SendRsvpNotificationJob::dispatch(
                $plannerUser->email,
                $plannerUser->name,
                $this->respondent_name,
                $event->name,
                $this->status,
                $this->status === 'confirmed' ? $this->plus_one_count : 0,
            );
        }

        if ($event->client_email) {
            $client = Client::where('tenant_id', $this->rsvpForm->tenant_id)
                ->where('email', $event->client_email)
                ->first();

            if ($client) {
                SendRsvpNotificationJob::dispatch(
                    $client->email,
                    $client->name,
                    $this->respondent_name,
                    $event->name,
                    $this->status,
                    $this->status === 'confirmed' ? $this->plus_one_count : 0,
                );
            }
        }

        $this->submitted = true;
        $this->error     = '';
    }

    public function render()
    {
        return view('livewire.public.rsvp-form');
    }
}