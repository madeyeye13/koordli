<?php

namespace App\Enums;

enum TaskStatus: string
{
    case ToDo       = 'todo';
    case InProgress = 'in_progress';
    case Blocked    = 'blocked';
    case Done       = 'done';
    case Cancelled  = 'cancelled';

    public function label(): string
    {
        return match($this) {
            self::ToDo       => 'To Do',
            self::InProgress => 'In Progress',
            self::Blocked    => 'Blocked',
            self::Done       => 'Done',
            self::Cancelled  => 'Cancelled',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::ToDo       => '#A8A29E',
            self::InProgress => '#3B82F6',
            self::Blocked    => '#EF4444',
            self::Done       => '#10B981',
            self::Cancelled  => '#78716C',
        };
    }

    public function badgeClass(): string
    {
        return match($this) {
            self::ToDo       => 'krd-badge-stone',
            self::InProgress => 'krd-badge-blue',
            self::Blocked    => 'krd-badge-red',
            self::Done       => 'krd-badge-green',
            self::Cancelled  => 'krd-badge-stone',
        };
    }
}