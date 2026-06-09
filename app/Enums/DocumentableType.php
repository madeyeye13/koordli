<?php

namespace App\Enums;

enum DocumentableType: string
{
    case Event     = 'event';
    case Vendor    = 'vendor';
    case Task      = 'task';
    case Guest     = 'guest';
    case Runsheet  = 'runsheet';

    public function label(): string
    {
        return match($this) {
            self::Event    => 'Event',
            self::Vendor   => 'Vendor',
            self::Task     => 'Task',
            self::Guest    => 'Guest',
            self::Runsheet => 'Runsheet',
        };
    }
}