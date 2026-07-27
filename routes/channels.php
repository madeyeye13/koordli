<?php
use Illuminate\Support\Facades\Broadcast;
use App\Models\Central\SupportAgent;
use App\Models\Central\SupportTicket;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
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