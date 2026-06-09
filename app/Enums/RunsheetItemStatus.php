<?php

namespace App\Enums;

enum RunsheetItemStatus: string
{
    case Pending    = 'pending';
    case InProgress = 'in_progress';
    case Done       = 'done';
    case Delayed    = 'delayed';

    public function label(): string
    {
        return match($this) {
            self::Pending    => 'Pending',
            self::InProgress => 'In Progress',
            self::Done       => 'Done',
            self::Delayed    => 'Delayed',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Pending    => 'ghost',
            self::InProgress => 'blue',
            self::Done       => 'green',
            self::Delayed    => 'danger',
        };
    }
}