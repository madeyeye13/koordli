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

    /**
     * Called via $wire.removeStaffFromEvent(id) directly from Alpine —
     * receives the id as a parameter rather than a bound property, since
     * the confirmation modal that collects it is Alpine-owned for instant
     * open/close (matching the pattern already fixed for Delete Role /
     * Delete Conversation modals — see the wire:click-causes-a-round-trip
     * bug documented in the project context's Rules section).
     */

    // ── Conversations ──────────────────────────────────────────
    public bool $showCreateConversationForm = false;
    public string $conversation_type = 'group';
    public string $conversation_name = '';
    public array  $selected_participants = [];

    public bool $showDeleteConversationModal = false;
    public ?string $deleteConversationUuid = null;

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

        \App\Models\Tenant\ConversationParticipant::create([
            'tenant_id'        => auth()->user()->tenant_id,
            'conversation_id'  => $conversation->id,
            'participant_type' => 'tenant_user',
            'participant_id'   => auth()->id(),
            'added_by'         => auth()->id(),
        ]);

        foreach ($this->selected_participants as $key) {
            [$type, $id] = explode(':', $key);

            if ($type === 'tenant_user' && (int) $id === auth()->id()) continue;

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

    public function deleteConversation(string $uuid): void
    {
        if (!app(PermissionService::class)->userCan(auth()->user(), 'conversations.delete')) {
            $this->toastError('You do not have permission to delete conversations.');
            return;
        }

        $conversation = \App\Models\Tenant\Conversation::where('uuid', $uuid)->first();

        if ($conversation) {
            broadcast(new \App\Events\ConversationDeleted($conversation->uuid, $this->event->slug));
            $conversation->delete(); // cascades to messages/attachments/participants/mentions/deletions
            $this->toastSuccess('Conversation deleted.');
        }
    }

    public function inviteClient(): void
    {
        if (!app(PermissionService::class)->userCan(auth()->user(), 'events.edit')) {
            $this->toastError('You do not have permission to invite a client for this event.');
            return;
        }

        $result = app(\App\Services\ClientInviteService::class)->inviteForEvent($this->event, auth()->id());

        match ($result['status']) {
            'error'   => $this->toastError($result['message']),
            'warning' => $this->toastWarning($result['message']),
            default   => $this->toastSuccess($result['message']),
        };
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
            'canDeleteConversations' => app(PermissionService::class)->userCan(auth()->user(), 'conversations.delete'),
        ]);
    }
}