<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class KoordliNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $category,
        public string $notificationType,
        public string $priority,
        public string $subjectLine,
        public string $body,
        public ?string $actionUrl = null,
        public ?string $actionLabel = null,
        public array $channels = ['database', 'mail', 'broadcast'],
    ) {}

    public function via($notifiable): array
    {
        $map = [
            'database'  => 'database',
            'mail'      => 'mail',
            'broadcast' => 'broadcast',
            'push'      => \App\Notifications\Channels\WebPushChannel::class,
        ];
        return array_values(array_intersect_key($map, array_flip($this->channels)));
    }

    public function toMail($notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject($this->subjectLine)
            ->line($this->body);

        if ($this->actionUrl) {
            $mail->action($this->actionLabel ?? 'View', $this->actionUrl);
        }

        return $mail;
    }

    public function toDatabase($notifiable): array
    {
        return [
            'category'          => $this->category,
            'notification_type' => $this->notificationType,
            'priority'          => $this->priority,
            'subject'           => $this->subjectLine,
            'body'              => $this->body,
            'action_url'        => $this->actionUrl,
            'action_label'      => $this->actionLabel,
        ];
    }

    public function toBroadcast($notifiable): \Illuminate\Notifications\Messages\BroadcastMessage
    {
        return new \Illuminate\Notifications\Messages\BroadcastMessage([
            'category'          => $this->category,
            'notification_type' => $this->notificationType,
            'priority'          => $this->priority,
            'subject'           => $this->subjectLine,
            'body'              => $this->body,
            'action_url'        => $this->actionUrl,
        ]);
    }

    public function toPush($notifiable): array
    {
        return [
            'title' => $this->subjectLine,
            'body'  => $this->body,
            'url'   => $this->actionUrl,
        ];
    }
}