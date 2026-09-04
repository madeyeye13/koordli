<?php

namespace App\Enums;

enum ChecklistPhase: string
{
    case TwelvePlusMonths = '12_plus_months';
    case SixToNineMonths  = '6_9_months';
    case ThreeToSixMonths = '3_6_months';
    case OneToThreeMonths = '1_3_months';
    case TwoToFourWeeks   = '2_4_weeks';
    case OneWeek          = '1_week';
    case DayOf            = 'day_of';

    public function label(): string
    {
        return match($this) {
            self::TwelvePlusMonths => '12+ Months Before',
            self::SixToNineMonths  => '6–9 Months Before',
            self::ThreeToSixMonths => '3–6 Months Before',
            self::OneToThreeMonths => '1–3 Months Before',
            self::TwoToFourWeeks   => '2–4 Weeks Before',
            self::OneWeek          => '1 Week Before',
            self::DayOf            => 'Day Of',
        };
    }

    /**
     * Ordering weight for sorting items by phase chronologically —
     * distinct from sort_order, which only orders items WITHIN a phase.
     */
    public function order(): int
    {
        return match($this) {
            self::TwelvePlusMonths => 1,
            self::SixToNineMonths  => 2,
            self::ThreeToSixMonths => 3,
            self::OneToThreeMonths => 4,
            self::TwoToFourWeeks   => 5,
            self::OneWeek          => 6,
            self::DayOf            => 7,
        };
    }

    public static function options(): array
    {
        return array_map(fn($case) => ['value' => $case->value, 'label' => $case->label()], self::cases());
    }
}