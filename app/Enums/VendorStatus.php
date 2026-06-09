<?php

namespace App\Enums;

enum VendorStatus: string
{
    case Researching = 'researching';
    case InContact   = 'in_contact';
    case Confirmed   = 'confirmed';
    case PaidInFull  = 'paid_in_full';
    case Cancelled   = 'cancelled';

    public function label(): string
    {
        return match($this) {
            self::Researching => 'Researching',
            self::InContact   => 'In Contact',
            self::Confirmed   => 'Confirmed',
            self::PaidInFull  => 'Paid in Full',
            self::Cancelled   => 'Cancelled',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Researching => 'ghost',
            self::InContact   => 'amber',
            self::Confirmed   => 'violet',
            self::PaidInFull  => 'green',
            self::Cancelled   => 'danger',
        };
    }
}