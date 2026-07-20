<?php

namespace App\Livewire\Hooks;

use App\Models\Central\Subscription;
use Livewire\ComponentHook;

class SubscriptionLockHook extends ComponentHook
{
    // Livewire internals + safe read-only interactions — NEVER blocked
    protected array $allowedMethods = [
        'render', 'mount', 'hydrate', 'dehydrate', 'boot',
        'updating', 'updated', '$set', '$sync', '$refresh', '$toggle',
        '$parent', '$dispatch', 'setTab', 'setType', 'setStatus',
        'toggleFieldRequired', 'toggleGateway', 'selectDate', 'selectTime',
        'prevMonth', 'nextMonth', 'loadCalendar', 'showAddField', 'showAddItem',
        'editField', 'editItem', 'editTask', 'confirmDelay', 'cancelDelay',
        'backToStep1', 'proceedToStep2',
    ];

    // Only these action patterns are actually blocked (real writes)
    protected array $blockedPatterns = [
        'save', 'create', 'delete', 'confirm', 'update', 'store',
        'submit', 'approve', 'reject', 'activate', 'deactivate',
        'toggle', 'send', 'invite', 'cancel', 'suspend', 'assign',
    ];

    public function call($method, $params, $returnEarly, $metadata, $componentContext)
    {
        // Only apply to tenant components
        if (!str_starts_with(get_class($this->component), 'App\Livewire\Tenant')) {
            return;
        }

        // Billing pages must always work
        if (str_contains(get_class($this->component), 'Billing')) {
            return;
        }

        // Whitelisted methods always allowed
        if (in_array($method, $this->allowedMethods)) {
            return;
        }

        // Only block if method name matches a write-action pattern
        $isWriteAction = false;
        foreach ($this->blockedPatterns as $pattern) {
            if (str_contains(strtolower($method), $pattern)) {
                $isWriteAction = true;
                break;
            }
        }

        if (!$isWriteAction) {
            return;
        }

        $user   = auth('web')->user();
        $tenant = $user?->tenant;
        if (!$tenant) return;

        $subscription = Subscription::where('tenant_id', $tenant->id)->latest()->first();
        if (!$subscription) return;

        if ($subscription->isLocked()) {
            // Stop the method from executing
            $returnEarly(null);

            // Show immediate toast + redirect shortly after
            $this->component->dispatch('subscription-locked');
        }
    }
}