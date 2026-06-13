<?php

namespace App\Enums;

enum TaskPriority: string
{
    case Low    = 'low';
    case Normal = 'normal';
    case High   = 'high';
    case Urgent = 'urgent';

    public function label(): string
    {
        return match($this) {
            self::Low    => 'Low',
            self::Normal => 'Normal',
            self::High   => 'High',
            self::Urgent => 'Urgent',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Low    => '#A8A29E',
            self::Normal => '#3B82F6',
            self::High   => '#F59E0B',
            self::Urgent => '#EF4444',
        };
    }

    public function badgeClass(): string
    {
        return match($this) {
            self::Low    => 'krd-badge-stone',
            self::Normal => 'krd-badge-blue',
            self::High   => 'krd-badge-amber',
            self::Urgent => 'krd-badge-red',
        };
    }
}