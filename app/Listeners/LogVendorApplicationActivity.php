<?php

namespace App\Listeners;

use App\Events\VendorApplicationSubmitted;
use App\Models\Tenant\ActivityTimeline;
use App\Models\Tenant\User;
use App\Services\Notifications\NotificationDispatchService;
use Illuminate\Contracts\Queue\ShouldQueue;

class LogVendorApplicationActivity implements ShouldQueue
{
    public function handle(VendorApplicationSubmitted $event): void
    {
        $application = $event->application;

        ActivityTimeline::log($application, 'vendor_application_submitted', $application->business_name . ' applied to join your vendor network');

        $owner = User::withoutGlobalScope('tenant')
            ->where('tenant_id', $application->tenant_id)
            ->orderBy('id')
            ->first();

        if (!$owner) return;

        app(NotificationDispatchService::class)->notify(
            notifiable: $owner,
            category: 'vendors',
            notificationType: 'vendor_application_submitted',
            templateKey: 'vendor_application_submitted',
            placeholders: [
                'user_name' => $owner->name,
                'task_name' => $application->business_name,
            ],
            priority: 'normal',
            actionUrl: route('tenant.vendor.applications'),
            actionLabel: 'Review Application',
            subject: $application,
            tenantId: $application->tenant_id,
        );
    }
}