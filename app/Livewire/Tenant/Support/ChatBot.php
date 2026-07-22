<?php

namespace App\Livewire\Tenant\Support;

use App\Models\Central\SupportAgent;
use App\Models\Central\SupportChatSession;
use App\Models\Central\SupportFaq;
use App\Models\Central\SupportTicket;
use App\Models\Central\SupportTicketMessage;
use App\Services\FeatureGateService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.tenant')]
class ChatBot extends Component
{
    public SupportTicket $ticket;
    public SupportChatSession $session;

    public string $userInput = '';
    public string $stage = 'menu'; // menu|typing_faq|escalated|no_agent|waiting

    public function mount(): void
    {
        $tenant = auth()->user()->tenant;

        $this->ticket = SupportTicket::create([
            'tenant_id'          => $tenant->id,
            'created_by_user_id' => auth()->id(),
            'subject'            => 'Live Support Chat — ' . now()->format('d M Y g:i A'),
            'description'        => null,
            'priority'           => 'medium',
            'category'           => 'chat',
            'status'             => 'open',
            'source'             => 'chat',
        ]);

        $this->session = SupportChatSession::create([
            'ticket_id'      => $this->ticket->id,
            'status'         => 'bot',
            'bot_engaged_at' => now(),
        ]);

        $this->botSay("Hi! I'm the Koordli Assistant 👋 What can I help you with today?");
    }

    private function botSay(string $text): void
    {
        SupportTicketMessage::create([
            'ticket_id'   => $this->ticket->id,
            'sender_type' => 'bot',
            'sender_id'   => null,
            'message'     => $text,
        ]);
    }

    private function tenantSay(string $text): void
    {
        SupportTicketMessage::create([
            'ticket_id'   => $this->ticket->id,
            'sender_type' => 'tenant',
            'sender_id'   => auth()->id(),
            'message'     => $text,
        ]);
    }

    public function selectOption(string $option): void
    {
        $labels = [
            'billing' => 'Billing / Plan Question',
            'howto'   => 'How do I...?',
            'broken'  => "Something's broken",
            'human'   => 'Talk to a human',
        ];

        $this->tenantSay($labels[$option] ?? $option);

        match ($option) {
            'billing' => $this->answerBillingQuestion(),
            'howto'   => $this->promptForFaqSearch(),
            'broken'  => $this->promptForFaqSearch(),
            'human'   => $this->escalate(),
            default   => null,
        };
    }

    private function answerBillingQuestion(): void
    {
        $tenant = auth()->user()->tenant->fresh();
        $subscription = $tenant->subscriptions()->latest()->first();
        $gate = app(FeatureGateService::class);

        $planName = $tenant->plan?->name ?? 'No plan';
        $status   = ucfirst($tenant->status ?? 'unknown');

        $lines = [];
        $lines[] = "Here's your account summary:";
        $lines[] = "• Plan: **{$planName}**";
        $lines[] = "• Status: **{$status}**";

        if ($subscription) {
            if ($subscription->isTrialing()) {
                $daysLeft = $subscription->trialDaysRemaining();
                $lines[] = "• Trial: **{$daysLeft} day(s) remaining**";
            } elseif ($subscription->isInGracePeriod()) {
                $lines[] = "• Your subscription is in its grace period — please renew soon to avoid lockout.";
            } elseif ($subscription->isActive()) {
                $lines[] = "• Next renewal: **" . ($subscription->current_period_end?->format('d M Y') ?? 'N/A') . "**";
            }
        }

        $features = ['custom_subdomain', 'custom_domain', 'white_label', 'rsvp', 'vendor_portal', 'client_portal', 'api_access'];
        $enabled = array_filter($features, fn($f) => $gate->canAccess($tenant, $f));

        if (!empty($enabled)) {
            $lines[] = "• Enabled features: " . implode(', ', array_map(fn($f) => str_replace('_', ' ', ucfirst($f)), $enabled));
        }

        $this->botSay(implode("\n", $lines));
        $this->botSay("Need anything else, or would you like to talk to a human agent?");
    }

    private function promptForFaqSearch(): void
    {
        $this->botSay("Sure — go ahead and describe what you're trying to do, and I'll find the best answer.");
        $this->stage = 'typing_faq';
    }

    public function sendMessage(): void
    {
        if (empty(trim($this->userInput))) return;

        $text = $this->userInput;
        $this->tenantSay($text);
        $this->userInput = '';

        $results = SupportFaq::searchByMessage($text);

        if ($results->isNotEmpty()) {
            $best = $results->first();
            $this->botSay($best->answer);

            if ($results->count() > 1) {
                $others = $results->slice(1)->pluck('question')->implode(' / ');
                $this->botSay("You might also be looking for: " . $others);
            }

            $this->botSay("Did that help, or would you like to talk to a human agent?");
        } else {
            $this->botSay("I'm not confident I can help with that one — want me to connect you with a support agent?");
        }
    }

    public function escalate(): void
    {
        $agent = SupportAgent::nextAvailable();

        if (!$agent) {
            $this->botSay("Our agents are currently busy helping other tenants 🙏 Please leave a message below describing your issue, and we'll follow up by email as soon as we're free.");
            $this->stage = 'no_agent';
            $this->session->update(['status' => 'ended', 'ended_by' => 'system', 'ended_at' => now()]);
            return;
        }

        $this->session->update([
            'status'       => 'waiting',
            'escalated_at' => now(),
        ]);

        $this->botSay("Great, connecting you with an available agent now. Please hold on...");
        $this->stage = 'waiting';

        // Stage 4 will wire real-time queue broadcast + presence here.
        // For now, ticket + session correctly reflect "waiting" state for an agent to pick up from the inbox.
    }

    public function leaveMessage(): void
    {
        if (empty(trim($this->userInput))) return;

        $this->tenantSay($this->userInput);
        $this->ticket->update(['description' => $this->userInput]);
        $this->userInput = '';
        $this->botSay("Thanks — we've created ticket #" . strtoupper(substr($this->ticket->uuid, 0, 8)) . " and will email you as soon as an agent is free.");
        $this->stage = 'ended';
    }

    public function backToMenu(): void
    {
        $this->stage = 'menu';
        $this->botSay("What else can I help with?");
    }

    public function render()
    {
        $this->ticket->load('messages');
        return view('livewire.tenant.support.chat-bot');
    }
}