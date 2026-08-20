<?php

namespace App\Livewire\Tenant\Events;

use App\Helpers\CurrencyHelper;
use App\Models\Tenant\Event;
use App\Models\Tenant\TenantEventStatus;
use App\Services\PermissionService;
use App\Traits\WithToast;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Renderless;
use Livewire\Component;
use App\Models\Central\Client;
use App\Jobs\SendClientInviteJob;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

#[Layout('layouts.tenant')]
class EventDetail extends Component
{
    use WithToast;

    public Event $event;

    public function mount(string $slug): void
    {
        abort_unless(
            app(PermissionService::class)->userCan(auth()->user(), 'events.view'),
            403
        );

        $this->event = Event::with([
            'eventType', 'status', 'tasks', 'rsvpResponses', 'team.user',
            'vendorAssignments.vendor.category',
            'budget.items', 'budget.clientPayments',
        ])->where('slug', $slug)->firstOrFail();
    }

    #[Renderless]
    public function updateStatus(int $statusId): void
    {
        if (!app(PermissionService::class)->userCan(auth()->user(), 'events.edit')) {
            $this->toastError('You do not have permission to edit this event.');
            return;
        }

        $this->event->update(['status_id' => $statusId]);
        $this->toastSuccess('Status updated.');
    }

    // ── Staff Assignment ──────────────────────────────────────
    public bool $showAddStaffForm = false;
    public ?int $add_staff_user_id = null;
    public string $add_staff_role = '';

    public function showAddStaff(): void
    {
        if (!app(PermissionService::class)->userCan(auth()->user(), 'events.edit')) {
            $this->toastError('You do not have permission to edit this event.');
            return;
        }

        $this->reset(['add_staff_user_id', 'add_staff_role']);
        $this->showAddStaffForm = true;
    }

    public function addStaffToEvent(): void
    {
        if (!app(PermissionService::class)->userCan(auth()->user(), 'events.edit')) {
            $this->toastError('You do not have permission to edit this event.');
            return;
        }

        $this->validate([
            'add_staff_user_id' => 'required|exists:users,id',
        ]);

        $exists = \App\Models\Tenant\EventTeam::where('event_id', $this->event->id)
            ->where('user_id', $this->add_staff_user_id)
            ->exists();

        if ($exists) {
            $this->toastWarning('This staff member is already assigned to this event.');
            return;
        }

        \App\Models\Tenant\EventTeam::create([
            'tenant_id'     => auth()->user()->tenant_id,
            'event_id'      => $this->event->id,
            'user_id'       => $this->add_staff_user_id,
            'role_in_event' => $this->add_staff_role ?: null,
        ]);

        $this->event->load('team.user');
        $this->showAddStaffForm = false;
        $this->toastSuccess('Staff member assigned to event.');
    }

    public function removeStaffFromEvent(int $eventTeamId): void
    {
        if (!app(PermissionService::class)->userCan(auth()->user(), 'events.edit')) {
            $this->toastError('You do not have permission to edit this event.');
            return;
        }

        \App\Models\Tenant\EventTeam::find($eventTeamId)?->delete();
        $this->event->load('team.user');
        $this->toastSuccess('Staff member removed from event.');
    }

    // ── Conversations ──────────────────────────────────────────
    public bool $showCreateConversationForm = false;
    public string $conversation_type = 'group';
    public string $conversation_name = '';
    public array  $selected_participants = []; // ["tenant_user:5", "client:2", "vendor_account:9"]

    public function showCreateConversation(): void
    {
        if (!app(PermissionService::class)->userCan(auth()->user(), 'conversations.create')) {
            $this->toastError('You do not have permission to create conversations.');
            return;
        }

        $this->reset(['conversation_type', 'conversation_name', 'selected_participants']);
        $this->conversation_type = 'group';
        $this->showCreateConversationForm = true;
    }

    public function createConversation(): void
    {
        if (!app(PermissionService::class)->userCan(auth()->user(), 'conversations.create')) {
            $this->toastError('You do not have permission to create conversations.');
            return;
        }

        $this->validate([
            'conversation_type' => 'required|in:group,direct',
            'conversation_name' => $this->conversation_type === 'group' ? 'required|string|max:150' : 'nullable|string|max:150',
        ]);

        if (empty($this->selected_participants)) {
            $this->toastError('Select at least one participant.');
            return;
        }

        if ($this->conversation_type === 'direct' && count($this->selected_participants) !== 1) {
            $this->toastError('A direct conversation must have exactly one other participant.');
            return;
        }

        $conversation = \App\Models\Tenant\Conversation::create([
            'tenant_id'       => auth()->user()->tenant_id,
            'event_id'        => $this->event->id,
            'type'            => $this->conversation_type,
            'name'            => $this->conversation_type === 'group' ? $this->conversation_name : null,
            'created_by_type' => 'tenant_user',
            'created_by_id'   => auth()->id(),
        ]);

        // Always add the creating planner as a participant
        \App\Models\Tenant\ConversationParticipant::create([
            'tenant_id'        => auth()->user()->tenant_id,
            'conversation_id'  => $conversation->id,
            'participant_type' => 'tenant_user',
            'participant_id'   => auth()->id(),
            'added_by'         => auth()->id(),
        ]);

        foreach ($this->selected_participants as $key) {
            [$type, $id] = explode(':', $key);

            if ($type === 'tenant_user' && (int) $id === auth()->id()) continue; // already added above

            \App\Models\Tenant\ConversationParticipant::create([
                'tenant_id'        => auth()->user()->tenant_id,
                'conversation_id'  => $conversation->id,
                'participant_type' => $type,
                'participant_id'   => (int) $id,
                'added_by'         => auth()->id(),
            ]);

            \App\Services\Conversations\ConversationNotifier::notifyAdded($conversation, $type, (int) $id, auth()->user()->name);
        }

        $this->showCreateConversationForm = false;
        $this->toastSuccess('Conversation created.');
    }

    public function inviteClient(): void
    {
        if (!app(PermissionService::class)->userCan(auth()->user(), 'events.edit')) {
            $this->toastError('You do not have permission to invite a client for this event.');
            return;
        }

        if (empty($this->event->client_email) || empty($this->event->client_name)) {
            $this->toastError('This event has no client email or name set. Edit the event first.');
            return;
        }

        $tenant = auth()->user()->tenant;

        $existing = Client::where('tenant_id', $tenant->id)
            ->where('email', $this->event->client_email)
            ->first();

        // Repeat client — grant access to THIS event, don't create a duplicate account or re-send credentials
        if ($existing) {
            $alreadyGranted = \App\Models\Tenant\ClientEventAccess::where('client_id', $existing->id)
                ->where('event_id', $this->event->id)
                ->exists();

            if ($alreadyGranted) {
                $this->toastWarning('This client already has access to this event.');
                return;
            }

            \App\Models\Tenant\ClientEventAccess::create([
                'tenant_id'  => $tenant->id,
                'client_id'  => $existing->id,
                'event_id'   => $this->event->id,
                'granted_by' => auth()->id(),
            ]);

            $this->toastSuccess($existing->name . ' already has a Koordli account — granted them access to this event using their existing login.');
            return;
        }

        // New client — create account AND grant access, in one atomic step
        $password = Str::random(10);

        $client = Client::create([
            'tenant_id' => $tenant->id,
            'name'      => $this->event->client_name,
            'email'     => $this->event->client_email,
            'password'  => Hash::make($password),
            'phone'     => $this->event->client_phone,
            'is_active' => true,
        ]);

        \App\Models\Tenant\ClientEventAccess::create([
            'tenant_id'  => $tenant->id,
            'client_id'  => $client->id,
            'event_id'   => $this->event->id,
            'granted_by' => auth()->id(),
        ]);

        SendClientInviteJob::dispatch(
            $this->event->client_email,
            $this->event->client_name,
            $password,
            $tenant->name,
            $this->event->name,
            app(\App\Services\FeatureGateService::class)->canAccess($tenant, 'white_label'),
        );

        $this->toastSuccess('Client invited successfully. Login credentials sent to ' . $this->event->client_email);
    }

    public function render()
    {
        $eligibleStaff = \App\Models\Tenant\User::withoutGlobalScope('tenant')
            ->where('tenant_id', auth()->user()->tenant_id)
            ->whereNotIn('id', $this->event->team->pluck('user_id'))
            ->orderBy('name')
            ->get(['id', 'name']);

        $eligibleParticipants = collect();

        foreach ($this->event->team as $teamMember) {
            $eligibleParticipants->push([
                'key'   => 'tenant_user:' . $teamMember->user_id,
                'label' => ($teamMember->user->name ?? 'Unknown') . ' (Staff' . ($teamMember->role_in_event ? ' — ' . $teamMember->role_in_event : '') . ')',
            ]);
        }

        $clientAccess = \App\Models\Tenant\ClientEventAccess::where('event_id', $this->event->id)->with('client')->get();
        foreach ($clientAccess as $access) {
            if ($access->client) {
                $eligibleParticipants->push(['key' => 'client:' . $access->client->id, 'label' => $access->client->name . ' (Client)']);
            }
        }

        foreach ($this->event->vendorAssignments as $assignment) {
            $vendorAccount = \App\Models\Central\VendorAccount::where('vendor_id', $assignment->vendor_id)->first();
            if ($vendorAccount) {
                $eligibleParticipants->push(['key' => 'vendor_account:' . $vendorAccount->id, 'label' => $vendorAccount->business_name . ' (Vendor)']);
            }
        }

        $conversations = \App\Models\Tenant\Conversation::where('event_id', $this->event->id)
            ->withCount('messages')
            ->orderByDesc('updated_at')
            ->get();

        return view('livewire.tenant.events.event-detail', [
            'statuses'             => TenantEventStatus::orderBy('sort_order')->get(),
            'symbol'               => CurrencyHelper::forTenant(),
            'eligibleStaff'        => $eligibleStaff,
            'eligibleParticipants' => $eligibleParticipants,
            'conversations'        => $conversations,
        ]);
    }
}