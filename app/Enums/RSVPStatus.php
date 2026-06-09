<?php

namespace App\Enums;

enum RSVPStatus: string
{
    case Pending   = 'pending';
    case Confirmed = 'confirmed';
    case Declined  = 'declined';

    public function label(): string
    {
        return match($this) {
            self::Pending   => 'Pending',
            self::Confirmed => 'Confirmed',
            self::Declined  => 'Declined',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Pending   => 'amber',
            self::Confirmed => 'green',
            self::Declined  => 'danger',
        };
    }
}