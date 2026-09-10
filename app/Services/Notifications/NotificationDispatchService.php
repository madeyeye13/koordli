<?php

namespace App\Services\Notifications;

use App\Models\Central\NotificationTemplate;
use App\Models\Tenant\ActivityTimeline;
use App\Models\Tenant\NotificationPreference;
use App\Models\Tenant\ReminderLog;
use App\Notifications\KoordliNotification;
use Illuminate\Database\Eloquent\Model;

class NotificationDispatchService
{
    /**
     * Send an instant, one-off notification (e.g. "Task Assigned") — NOT a recurring reminder.
     * Quiet hours do not apply here; this is a direct action confirmation, not a nag.
     */
    public function notify(
        Model $notifiable,
        string $category,
        string $notificationType,
        string $templateKey,
        array $placeholders,
        string $priority = 'normal',
        ?string $actionUrl = null,
        ?string $actionLabel = null,
        ?Model $subject = null,
        ?int $tenantId = null,
        ?int $reminderRuleId = null,
        bool $isManual = false,
    ): void {
        $template = NotificationTemplate::where('key', $templateKey)->where('is_active', true)->first();

        if (!$template) {
            $template = new NotificationTemplate([
                'key' => $templateKey,
                'category' => $category,
                'subject' => $notificationType,
                'body' => $notificationType,
                'is_active' => true,
            ]);
        }

        $rendered = $template->render($placeholders);
        $channels = NotificationPreference::channelsFor($notifiable, $category);

        $notifiable->notify(new KoordliNotification(
            category: $category,
            notificationType: $notificationType,
            priority: $priority,
            subjectLine: $rendered['subject'],
            body: $rendered['body'],
            actionUrl: $actionUrl,
            actionLabel: $actionLabel,
            channels: $channels,
        ));

        foreach ($channels as $channel) {
            ReminderLog::create([
                'tenant_id'        => $tenantId ?? $notifiable->tenant_id ?? null,
                'reminder_rule_id' => $reminderRuleId,
                'notifiable_type'  => get_class($notifiable),
                'notifiable_id'    => $notifiable->id,
                'subject_type'     => $subject ? get_class($subject) : null,
                'subject_id'       => $subject?->id,
                'channel'          => $channel,
                'is_manual'        => $isManual,
                'sent_at'          => now(),
            ]);
        }

        if ($subject) {
            ActivityTimeline::log($subject, $notificationType, $rendered['subject']);
        }
    }
}