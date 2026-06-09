<?php

namespace App\Enums;

enum TaskStatus: string
{
    case ToDo       = 'todo';
    case InProgress = 'in_progress';
    case Done       = 'done';
    case Overdue    = 'overdue';

    public function label(): string
    {
        return match($this) {
            self::ToDo       => 'To Do',
            self::InProgress => 'In Progress',
            self::Done       => 'Done',
            self::Overdue    => 'Overdue',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::ToDo       => 'ghost',
            self::InProgress => 'blue',
            self::Done       => 'green',
            self::Overdue    => 'danger',
        };
    }
}