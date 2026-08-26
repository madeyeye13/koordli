<?php

namespace App\Livewire\Tenant;

use App\Services\PermissionService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.tenant')]
class SettingsHub extends Component
{
    public function render()
    {
        $user = auth()->user();
        $can = fn($perm) => app(PermissionService::class)->userCan($user, $perm);

        $cards = [
            ['label' => 'My Profile', 'desc' => 'Update your name and change your password.', 'route' => 'tenant.my-profile', 'visible' => true],
            ['label' => 'My Quick Access Link', 'desc' => 'Manage your personal no-login link.', 'route' => 'tenant.my-quick-access', 'visible' => app(\App\Services\FeatureGateService::class)->canAccess($user->tenant, 'quick_access_links')],
            ['label' => 'Domain Settings', 'desc' => 'Custom subdomain and domain configuration.', 'route' => 'tenant.domain-settings', 'visible' => $can('domain-settings.manage')],
            ['label' => 'Clients', 'desc' => 'View and manage client access to your events.', 'route' => 'tenant.clients', 'visible' => $can('clients.manage')],
            ['label' => 'Client Notifications', 'desc' => 'Control what your clients are notified about.', 'route' => 'tenant.client-notifications', 'visible' => $can('client-notifications.manage')],
            ['label' => 'Roles & Permissions', 'desc' => 'Manage staff roles and what they can access.', 'route' => 'tenant.staff.roles', 'visible' => $can('staff.roles.manage')],
            ['label' => 'Quick Access Links', 'desc' => 'Manage no-login links for staff and vendors.', 'route' => 'tenant.quick-access', 'visible' => $can('quick-access.manage')],
            ['label' => 'Billing', 'desc' => 'Your subscription plan and payment history.', 'route' => 'tenant.billing', 'visible' => $can('billing.view')],
        ];

        return view('livewire.tenant.settings-hub', ['cards' => array_filter($cards, fn($c) => $c['visible'])]);
    }
}