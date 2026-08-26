<?php

namespace App\Http\Controllers;

use App\Models\Tenant\PushSubscription;
use Illuminate\Http\Request;

class PushSubscriptionController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'endpoint'     => 'required|string',
            'keys.p256dh'  => 'required|string',
            'keys.auth'    => 'required|string',
        ]);

        [$notifiable, $tenantId] = $this->currentNotifiable();
        if (!$notifiable) {
            return response()->json(['error' => 'Not authenticated'], 401);
        }

        PushSubscription::withoutGlobalScope('tenant')->updateOrCreate(
            [
                'notifiable_type' => get_class($notifiable),
                'notifiable_id'   => $notifiable->id,
                'endpoint_hash'   => hash('sha256', $data['endpoint']),
            ],
            [
                'tenant_id'  => $tenantId,
                'endpoint'   => $data['endpoint'],
                'p256dh_key' => $data['keys']['p256dh'],
                'auth_token' => $data['keys']['auth'],
                'user_agent' => substr($request->userAgent() ?? '', 0, 255),
            ]
        );

        return response()->json(['success' => true]);
    }

    public function destroy(Request $request)
    {
        $data = $request->validate(['endpoint' => 'required|string']);

        [$notifiable] = $this->currentNotifiable();
        if (!$notifiable) {
            return response()->json(['error' => 'Not authenticated'], 401);
        }

        PushSubscription::withoutGlobalScope('tenant')
            ->where('notifiable_type', get_class($notifiable))
            ->where('notifiable_id', $notifiable->id)
            ->where('endpoint_hash', hash('sha256', $data['endpoint']))
            ->delete();

        return response()->json(['success' => true]);
    }

    private function currentNotifiable(): array
    {
        if (auth('web')->check()) {
            $u = auth('web')->user();
            return [$u, $u->tenant_id];
        }
        if (auth('client')->check()) {
            $u = auth('client')->user();
            return [$u, $u->tenant_id];
        }
        if (auth('vendor')->check()) {
            $u = auth('vendor')->user();
            return [$u, $u->tenant_id];
        }
        return [null, null];
    }
}