<?php

namespace App\Livewire\Public;

use App\Jobs\SendRsvpConfirmationJob;
use App\Jobs\SendRsvpNotificationJob;
use App\Models\Central\Client;
use App\Models\Central\Tenant;
use App\Models\Tenant\EventGalleryImage;
use App\Models\Tenant\EventGiftInfo;
use App\Models\Tenant\EventMicrositeSettings;
use App\Models\Tenant\EventStoryChapter;
use App\Models\Tenant\EventWish;
use App\Models\Tenant\EventWishReaction;
use App\Models\Tenant\RsvpForm;
use App\Models\Tenant\RsvpResponse;
use App\Models\Tenant\RsvpResponseAnswer;
use App\Models\Tenant\User;
use App\Services\FeatureGateService;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.rsvp')]
class RsvpMicrosite extends Component
{
    public RsvpForm $rsvpForm;
    public ?EventMicrositeSettings $settings = null;
    public ?EventGiftInfo $giftInfo = null;

    // RSVP form fields
    public int    $step             = 1;
    public string $respondent_name  = '';
    public string $respondent_email = '';
    public string $respondent_phone = '';
    public string $status           = 'confirmed';
    public int    $plus_one_count   = 0;
    public array  $answers          = [];
    public bool   $submitted = false;
    public string $error     = '';
    public ?RsvpResponse $response = null;
    public ?string $editToken = null;
    public bool $editing = false;

    public array  $systemQuestions = [];

    // Companions — added only when attending, capped at 5
    public array  $companions = [];
    public string $newCompanionName = '';
    public string $newCompanionRelation = '';
    public bool   $comingWithSomeone = false;
    public string $decline_reason = '';

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

    // Email-lookup — reuses the existing, proven edit flow rather than
    // reimplementing update logic inline.
    public bool   $emailExists  = false;
    public string $existingName = '';
    public string $existingEditUrl = '';

    /**
    * Uses customQuestions() — the relation query builder — not the possibly-
     * unloaded ->questions collection. Livewire re-hydrates $rsvpForm
     * fresh on every subsequent request without preserving mount()'s
     * eager load, so relying on the collection being loaded breaks
     * intermittently. A fresh count() query is always safe.
     */
    public function totalSteps(): int
    {
        return $this->rsvpForm->customQuestions()->count() > 0 ? 3 : 2;
    }

    public function updatedRespondentEmail(): void
    {
        $this->emailExists = false;
        if (!filter_var($this->respondent_email, FILTER_VALIDATE_EMAIL)) return;

        $existing = RsvpResponse::where('rsvp_form_id', $this->rsvpForm->id)
            ->where('respondent_email', $this->respondent_email)
            ->first();

        if ($existing) {
            $this->emailExists = true;
            $this->existingName = $existing->respondent_name;
            $this->existingEditUrl = $existing->editUrl();
        }
    }

    public function nextStep(): void
    {
        if ($this->step === 1) {
            $rules = [
                'respondent_name' => ($this->systemQuestions['name']['enabled'] ?? true) && ($this->systemQuestions['name']['required'] ?? true)
                    ? 'required|string|min:2|max:150' : 'nullable|string|max:150',
                'respondent_email' => ($this->systemQuestions['email']['enabled'] ?? true) && ($this->systemQuestions['email']['required'] ?? false)
                    ? 'required|email|max:150' : 'nullable|email|max:150',
            ];
            $this->validate($rules, [], ['respondent_name' => 'full name']);

            if ($this->emailExists) {
                // Previously failed silently — clicking Next appeared to
                // do nothing, with no indication why. Now surfaces a
                // real, visible reason instead of a dead click.
                $this->error = 'That email already has an RSVP — use "Edit My Submission" above, or enter a different email to continue.';
                return;
            }
        }

        $this->error = '';
        $this->step = min($this->step + 1, $this->totalSteps());
    }

    public function prevStep(): void
    {
        $this->step = max($this->step - 1, 1);
    }

    // Wish submission
    public string $wishName = '';
    public string $wishEmail = '';
    public string $wishMessage = '';
    public bool $wishSubmitted = false;

    private function wishReactorCookieName(): string
    {
        return 'krd_wish_reactor';
    }

    private function wishReactorToken(): string
    {
        return request()->cookie($this->wishReactorCookieName()) ?: Str::random(64);
    }

    public function toggleWishReaction(int $wishId, string $reactionType): void
    {
        if (!$this->settings?->wishes_enabled || !in_array($reactionType, ['heart', 'congrats'], true)) return;

        $wish = EventWish::withoutGlobalScope('tenant')
            ->where('id', $wishId)
            ->where('event_id', $this->rsvpForm->event_id)
            ->where('status', 'approved')
            ->first();
        if (!$wish) return;

        $token = $this->wishReactorToken();
        $reaction = EventWishReaction::withoutGlobalScope('tenant')
            ->where('wish_id', $wish->id)
            ->where('reaction_type', $reactionType)
            ->where('reactor_token', $token)
            ->first();

        if ($reaction) {
            $reaction->delete();
        } else {
            EventWishReaction::create([
                'wish_id' => $wish->id,
                'tenant_id' => $this->rsvpForm->tenant_id,
                'reaction_type' => $reactionType,
                'reactor_token' => $token,
            ]);
        }

        if (!request()->cookie($this->wishReactorCookieName())) {
            cookie()->queue(cookie($this->wishReactorCookieName(), $token, 60 * 24 * 365));
        }
    }
    public function mount(string $slug, ?string $token = null): void
    {
        $this->rsvpForm = RsvpForm::with([
            'event',
            'customQuestions' => fn($q) => $q->orderBy('sort_order'),
        ])
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        if (!$this->rsvpForm->event->rsvp_enabled) {
            abort(404);
        }

        $this->settings = EventMicrositeSettings::withoutGlobalScope('tenant')
            ->where('event_id', $this->rsvpForm->event_id)->first();

        $this->giftInfo = EventGiftInfo::withoutGlobalScope('tenant')
            ->where('event_id', $this->rsvpForm->event_id)->first();

        $this->systemQuestions = array_replace_recursive(
            RsvpForm::defaultSystemQuestions(),
            $this->rsvpForm->system_questions ?? []
        );

        if ($this->rsvpForm->isDeadlinePassed()) {
            $this->error = 'RSVP is now closed for this event.';
        }

        foreach ($this->rsvpForm->customQuestions ?? [] as $q) {
            $this->answers[$q->id] = '';
        }

        if ($token) {
            $this->loadExistingResponse($token);
        }
    }

    private function loadExistingResponse(string $token): void
    {
        $response = RsvpResponse::with(['companions', 'answers'])
            ->where('edit_token', $token)
            ->where('rsvp_form_id', $this->rsvpForm->id)
            ->firstOrFail();

        $this->editToken = $token;
        $this->editing = true;
        $this->response = $response;
        $this->respondent_name = $response->respondent_name;
        $this->respondent_email = $response->respondent_email ?? '';
        $this->respondent_phone = $response->respondent_phone ?? '';
        $this->status = $response->status;
        $this->decline_reason = $response->decline_reason ?? '';
        $this->companions = $response->companions->map(fn ($companion) => [
            'name' => $companion->name,
            'relation' => $companion->relation,
        ])->toArray();
        $this->comingWithSomeone = $this->companions !== [];

        foreach ($response->answers as $answer) {
            $this->answers[$answer->rsvp_question_id] = $answer->answer;
        }
    }

    /**
     * Confirmed-guest-only address check — a real guest is considered
     * "confirmed" for this session once they've submitted with status
     * = confirmed, tracked via a signed cookie set right after submit.
     * Anyone else sees the placeholder text regardless of settings.
     */
    public function canSeeVenueAddress(): bool
    {
        if (!$this->settings || !$this->settings->gate_venue_address) return true;
        return request()->cookie('krd_rsvp_confirmed_' . $this->rsvpForm->id) === 'true';
    }

    public string $galleryPasswordInput = '';
    public string $galleryPasswordError = '';

    /**
     * Session-scoped (not a long-lived cookie) — a shared gallery
     * password isn't tied to one specific guest's identity the way the
     * venue-confirmation gate is, so it only needs to stay unlocked for
     * this browsing session, not persist for a year.
     */
    public function galleryUnlocked(): bool
    {
        return session('gallery_unlocked_' . $this->rsvpForm->id, false);
    }

    public function unlockGallery(): void
    {
        if ($this->galleryPasswordInput === ($this->settings->gallery_password ?? '')) {
            session(['gallery_unlocked_' . $this->rsvpForm->id => true]);
            $this->galleryPasswordError = '';
        } else {
            $this->galleryPasswordError = 'Incorrect password. Please try again.';
        }
        $this->galleryPasswordInput = '';
    }

    public function submitRsvp(): void
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
            'respondent_name'  => ($this->systemQuestions['name']['enabled'] ?? true) && ($this->systemQuestions['name']['required'] ?? true)
                ? 'required|string|min:2|max:150' : 'nullable|string|max:150',
            'respondent_email' => ($this->systemQuestions['email']['enabled'] ?? true) && ($this->systemQuestions['email']['required'] ?? false)
                ? 'required|email|max:150' : 'nullable|email|max:150',
            'respondent_phone' => 'nullable|string|max:20',
            'status'           => ($this->systemQuestions['status']['enabled'] ?? true) && ($this->systemQuestions['status']['required'] ?? true)
                ? 'required|in:confirmed,declined' : 'nullable|in:confirmed,declined',
            'decline_reason'   => 'nullable|string|max:500',
        ];
        foreach ($this->rsvpForm->customQuestions ?? [] as $q) {
            $rules["answers.{$q->id}"] = $q->is_required ? 'required' : 'nullable';
        }
        $this->validate($rules, [], ['respondent_name' => 'full name']);

        if ($this->respondent_email) {
            $duplicate = RsvpResponse::where('rsvp_form_id', $this->rsvpForm->id)
                ->where('respondent_email', $this->respondent_email)
                ->when($this->response, fn ($query) => $query->where('id', '!=', $this->response->id))
                ->exists();
            if ($duplicate) {
                $this->error = 'An RSVP with this email already exists. Look it up below to edit your response.';
                return;
            }
        }

        if ($this->editing && $this->response) {
            $this->updateExistingResponse();
            return;
        }

        $qrToken = $this->status === 'confirmed' ? 'RSVP-' . strtoupper(Str::random(10)) : null;

        $this->response = RsvpResponse::create([
            'uuid' => Str::uuid(),
            'tenant_id' => $this->rsvpForm->tenant_id,
            'event_id' => $this->rsvpForm->event_id,
            'rsvp_form_id' => $this->rsvpForm->id,
            'respondent_name' => $this->respondent_name,
            'respondent_email' => $this->respondent_email ?: null,
            'respondent_phone' => $this->respondent_phone ?: null,
            'status' => $this->status,
            'decline_reason' => $this->status === 'declined' ? ($this->decline_reason ?: null) : null,
            // Derived from the real, named companions below — never
            // entered as a raw number, so the two can never disagree.
            'plus_one_count' => $this->status === 'confirmed' ? count($this->companions) : 0,
            'qr_token' => $qrToken,
        ]);

        if ($this->status === 'confirmed') {
            foreach ($this->companions as $i => $c) {
                \App\Models\Tenant\RsvpCompanion::create([
                    'rsvp_response_id' => $this->response->id,
                    'tenant_id' => $this->rsvpForm->tenant_id,
                    'name' => $c['name'],
                    'relation' => $c['relation'],
                    'sort_order' => $i,
                ]);
            }
        }

        foreach ($this->rsvpForm->customQuestions ?? [] as $q) {
            if (isset($this->answers[$q->id]) && $this->answers[$q->id] !== '') {
                RsvpResponseAnswer::create([
                    'tenant_id' => $this->rsvpForm->tenant_id,
                    'rsvp_response_id' => $this->response->id,
                    'rsvp_question_id' => $q->id,
                    'answer' => is_array($this->answers[$q->id]) ? implode(', ', $this->answers[$q->id]) : $this->answers[$q->id],
                ]);
            }
        }

        $event = $this->rsvpForm->event;
        $eventDate = $event->date?->format('D, d M Y') ?? 'TBC';

        event(new \App\Events\RsvpSubmitted($this->response, $this->rsvpForm->tenant_id));
        app(\App\Services\Notifications\ClientNotificationService::class)->notifyRsvpSubmitted($this->response);

        if ($this->status === 'confirmed') {
            app(\App\Services\Notifications\ClientNotificationService::class)->checkRsvpMilestone($this->rsvpForm);

            // Sets the confirmed-guest cookie for the venue-address gate —
            // 1 year, this device only, no login required.
            if ($this->settings?->gate_venue_address) {
                cookie()->queue(cookie('krd_rsvp_confirmed_' . $this->rsvpForm->id, 'true', 60 * 24 * 365));
            }
        }

        if ($this->respondent_email && $qrToken) {
            $__tenant = Tenant::find($this->rsvpForm->tenant_id);
            SendRsvpConfirmationJob::dispatch(
                $this->respondent_email, $this->respondent_name, $this->rsvpForm->title, $eventDate,
                $event->venue ?? '', $this->status, $qrToken, $this->response->editUrl(),
                $this->status === 'confirmed' ? count($this->companions) : 0,
                $__tenant?->name ?? 'Koordli',
                $__tenant ? app(FeatureGateService::class)->canAccess($__tenant, 'white_label') : false,
                $this->status === 'confirmed' ? $this->companions : [],
            );
        }

        $plannerUser = User::withoutGlobalScope('tenant')
            ->where('tenant_id', $this->rsvpForm->tenant_id)
            ->whereHas('roles', fn($q) => $q->where('is_system', true))->first();

        if ($plannerUser?->email) {
            SendRsvpNotificationJob::dispatch(
                $plannerUser->email, $plannerUser->name, $this->respondent_name, $event->name,
                $this->status, $this->status === 'confirmed' ? count($this->companions) : 0,
                false, $this->status === 'confirmed' ? $this->companions : [],
            );
        }

        if ($event->client_email) {
            $client = Client::where('tenant_id', $this->rsvpForm->tenant_id)->where('email', $event->client_email)->first();
            if ($client) {
                SendRsvpNotificationJob::dispatch(
                    $client->email, $client->name, $this->respondent_name, $event->name,
                    $this->status, $this->status === 'confirmed' ? count($this->companions) : 0,
                    false, $this->status === 'confirmed' ? $this->companions : [],
                );
            }
        }

        $this->submitted = true;
        $this->error = '';
        $this->step = 1;
    }

    private function updateExistingResponse(): void
    {
        $newStatus = $this->status;
        $qrToken = $this->response->qr_token;
        if ($newStatus === 'confirmed' && !$qrToken) $qrToken = 'RSVP-' . strtoupper(Str::random(10));
        if ($newStatus === 'declined') $qrToken = null;

        $this->response->update([
            'respondent_name' => $this->respondent_name,
            'respondent_email' => $this->respondent_email ?: null,
            'respondent_phone' => $this->respondent_phone ?: null,
            'status' => $newStatus,
            'decline_reason' => $newStatus === 'declined' ? ($this->decline_reason ?: null) : null,
            'plus_one_count' => $newStatus === 'confirmed' ? count($this->companions) : 0,
            'qr_token' => $qrToken,
        ]);

        $this->response->companions()->delete();
        if ($newStatus === 'confirmed') {
            foreach ($this->companions as $index => $companion) {
                \App\Models\Tenant\RsvpCompanion::create([
                    'rsvp_response_id' => $this->response->id,
                    'tenant_id' => $this->response->tenant_id,
                    'name' => $companion['name'],
                    'relation' => $companion['relation'],
                    'sort_order' => $index,
                ]);
            }
        }

        foreach ($this->rsvpForm->customQuestions ?? [] as $question) {
            $answer = $this->answers[$question->id] ?? null;
            $existing = RsvpResponseAnswer::where('rsvp_response_id', $this->response->id)
                ->where('rsvp_question_id', $question->id)->first();
            if ($answer !== null && $answer !== '') {
                $value = is_array($answer) ? implode(', ', $answer) : $answer;
                $existing ? $existing->update(['answer' => $value]) : RsvpResponseAnswer::create([
                    'tenant_id' => $this->response->tenant_id,
                    'rsvp_response_id' => $this->response->id,
                    'rsvp_question_id' => $question->id,
                    'answer' => $value,
                ]);
            } elseif ($existing) {
                $existing->delete();
            }
        }

        $this->sendRsvpNotifications($this->response, true);
        if ($newStatus === 'confirmed' && $this->settings?->gate_venue_address) {
            cookie()->queue(cookie('krd_rsvp_confirmed_' . $this->rsvpForm->id, 'true', 60 * 24 * 365));
        }
        $this->submitted = true;
        $this->error = '';
        $this->step = 1;
    }

    private function sendRsvpNotifications(RsvpResponse $response, bool $isUpdate = false): void
    {
        $event = $this->rsvpForm->event;
        $eventDate = $event->date?->format('D, d M Y') ?? 'TBC';
        if ($response->respondent_email && $response->qr_token) {
            $tenant = Tenant::find($response->tenant_id);
            SendRsvpConfirmationJob::dispatch(
                $response->respondent_email, $response->respondent_name, $this->rsvpForm->title,
                $eventDate, $event->venue ?? '', $response->status, $response->qr_token,
                $response->editUrl(), $response->plus_one_count, $tenant?->name ?? 'Koordli',
                $tenant ? app(FeatureGateService::class)->canAccess($tenant, 'white_label') : false,
                $response->companions()->get()->map(fn ($companion) => [
                    'name' => $companion->name, 'relation' => $companion->relation,
                ])->toArray(),
            );
        }
        $planner = User::withoutGlobalScope('tenant')->where('tenant_id', $response->tenant_id)
            ->whereHas('roles', fn ($query) => $query->where('is_system', true))->first();
        if ($planner?->email) SendRsvpNotificationJob::dispatch(
            $planner->email, $planner->name, $response->respondent_name, $event->name,
            $response->status, $response->plus_one_count, $isUpdate, $this->companions,
        );
        if ($event->client_email) {
            $client = Client::where('tenant_id', $response->tenant_id)->where('email', $event->client_email)->first();
            if ($client) SendRsvpNotificationJob::dispatch(
                $client->email, $client->name, $response->respondent_name, $event->name,
                $response->status, $response->plus_one_count, $isUpdate, $this->companions,
            );
        }
    }

    public function submitWish(): void
    {
        if (!$this->settings?->wishes_enabled) return;

        $this->validate([
            'wishName' => 'required|string|max:100',
            'wishEmail' => 'nullable|email|max:150',
            'wishMessage' => 'required|string|max:1000',
        ]);

        $requiresApproval = $this->settings->wishes_require_approval;

        $wish = EventWish::create([
            'event_id' => $this->rsvpForm->event_id,
            'tenant_id' => $this->rsvpForm->tenant_id,
            'guest_name' => $this->wishName,
            'guest_email' => $this->wishEmail ?: null,
            'message' => $this->wishMessage,
            'status' => $requiresApproval ? 'pending' : 'approved',
        ]);

        event(new \App\Events\WishSubmitted($wish));
        app(\App\Services\Notifications\ClientNotificationService::class)->notifyWishSubmitted($wish);

        $this->reset(['wishName', 'wishEmail', 'wishMessage']);
        $this->wishSubmitted = true;
    }

    public function resetWishSubmission(): void
    {
        $this->wishSubmitted = false;
    }

    public function resetRsvpView(): void
    {
        if ($this->editing || !$this->submitted) return;

        $this->submitted = false;
        $this->response = null;
        $this->step = 1;
        $this->reset([
            'respondent_name', 'respondent_email', 'respondent_phone',
            'companions', 'newCompanionName', 'newCompanionRelation',
            'comingWithSomeone', 'decline_reason', 'answers', 'error',
        ]);
        $this->status = 'confirmed';
    }

    public function render()
    {
        return view('livewire.public.rsvp-microsite', [
            'title' => $this->rsvpForm->title,
            'chapters' => $this->settings?->story_enabled
                ? EventStoryChapter::withoutGlobalScope('tenant')->where('event_id', $this->rsvpForm->event_id)->orderBy('sort_order')->get()
                : collect(),
            'images' => $this->settings?->gallery_enabled
                ? EventGalleryImage::withoutGlobalScope('tenant')->where('event_id', $this->rsvpForm->event_id)->orderBy('sort_order')->get()
                : collect(),
            'wishes' => $this->settings?->wishes_enabled
                ? EventWish::withoutGlobalScope('tenant')->where('event_id', $this->rsvpForm->event_id)->where('status', 'approved')
                    ->withCount([
                        'reactions as heart_reactions_count' => fn ($q) => $q->where('reaction_type', 'heart'),
                        'reactions as congrats_reactions_count' => fn ($q) => $q->where('reaction_type', 'congrats'),
                    ])->orderByDesc('created_at')->get()
                : collect(),
            'myWishReactions' => $this->settings?->wishes_enabled && request()->cookie($this->wishReactorCookieName())
                ? EventWishReaction::withoutGlobalScope('tenant')->where('reactor_token', request()->cookie($this->wishReactorCookieName()))
                    ->whereIn('wish_id', EventWish::withoutGlobalScope('tenant')->where('event_id', $this->rsvpForm->event_id)->pluck('id'))
                    ->get()->groupBy('wish_id')->map(fn ($reactions) => $reactions->pluck('reaction_type')->values())->toArray()
                : [],
        ])->layout('layouts.rsvp', [
            'title' => $this->rsvpForm->title,
        ]);
    }
}