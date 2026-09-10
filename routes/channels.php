<?php
use Illuminate\Support\Facades\Broadcast;
use App\Models\Central\SupportAgent;
use App\Models\Central\SupportTicket;
use App\Models\Tenant\Conversation;

// Tenant User personal notifications channel (see User::receivesBroadcastNotificationsOn())
Broadcast::channel('notifications.tenant-user.{id}', function ($user, $id) {
    return auth('web')->check() && (int) auth('web')->id() === (int) $id;
});

// Platform User personal notifications channel (see PlatformUser::receivesBroadcastNotificationsOn())
Broadcast::channel('notifications.platform-user.{id}', function ($user, $id) {
    return auth('platform')->check() && (int) auth('platform')->id() === (int) $id;
});

// Serves BOTH the private channel ('private-support-ticket.{uuid}') AND
// the presence channel ('presence-support-ticket.{uuid}') for the same ticket.
// Laravel matches by logical name after stripping the private-/presence- prefix,
// so ONE registration correctly authorizes both channel types.
Broadcast::channel('support-ticket.{uuid}', function ($user, string $uuid) {
    $ticket = SupportTicket::where('uuid', $uuid)->first();
    if (!$ticket) return false;

    if (auth('web')->check() && auth('web')->id() === $ticket->created_by_user_id) {
        return ['id' => 'tenant-' . auth('web')->id(), 'name' => auth('web')->user()->name, 'type' => 'tenant'];
    }

    if (auth('platform')->check()) {
        $agent = SupportAgent::where('platform_user_id', auth('platform')->id())->first();
        if ($agent && $ticket->assigned_agent_id === $agent->id) {
            return ['id' => 'agent-' . $agent->id, 'name' => auth('platform')->user()->name, 'type' => 'agent'];
        }
    }

    return false;
});

// Every AVAILABLE agent can subscribe to the shared queue — sees new waiting chats
Broadcast::channel('support-queue', function ($user) {
    if (!auth('platform')->check()) return false;

    $agent = SupportAgent::where('platform_user_id', auth('platform')->id())->first();
    return $agent ? ['id' => $agent->id, 'name' => auth('platform')->user()->name] : false;
});

// Serves BOTH private- and presence- variants of the same conversation channel.
// Authorization checks CURRENT (not left) ConversationParticipant membership
// across all three participant-bearing guards — this is the single
// authoritative gate, matching Conversation::hasParticipant().
Broadcast::channel('conversation.{uuid}', function ($user, string $uuid) {
    $conversation = Conversation::where('uuid', $uuid)->first();
    if (!$conversation) return false;

    if (auth('web')->check()) {
        $userId = auth('web')->id();
        if ($conversation->hasParticipant('tenant_user', $userId)) {
            return ['id' => 'tenant_user-' . $userId, 'name' => auth('web')->user()->name, 'type' => 'tenant_user'];
        }
    }

    if (auth('client')->check()) {
        $clientId = auth('client')->id();
        if ($conversation->hasParticipant('client', $clientId)) {
            return ['id' => 'client-' . $clientId, 'name' => auth('client')->user()->name, 'type' => 'client'];
        }
    }

    if (auth('vendor')->check()) {
        $vendorId = auth('vendor')->id();
        if ($conversation->hasParticipant('vendor_account', $vendorId)) {
            return ['id' => 'vendor_account-' . $vendorId, 'name' => auth('vendor')->user()->name, 'type' => 'vendor_account'];
        }
    }

    return false;
});