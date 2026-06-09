<?php

namespace App\Enums;

enum NotificationChannel: string
{
    case Mail      = 'mail';
    case Database  = 'database';
    case SMS       = 'sms';
    case WhatsApp  = 'whatsapp';
    case Push      = 'push';

    public function label(): string
    {
        return match($this) {
            self::Mail     => 'Email',
            self::Database => 'In-App',
            self::SMS      => 'SMS',
            self::WhatsApp => 'WhatsApp',
            self::Push     => 'Push Notification',
        };
    }

    public function isAvailable(): bool
    {
        return match($this) {
            self::Mail     => true,
            self::Database => true,
            self::SMS      => false, // Phase 2+
            self::WhatsApp => false, // Phase 2+
            self::Push     => false, // Phase 2+
        };
    }
}