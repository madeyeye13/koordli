<?php

namespace App\Helpers;

class PublicHolidayHelper
{
    public static function getHolidays(string $countryCode, int $year): array
    {
        $holidays = [];

        $fixed = static::getFixedHolidays($countryCode, $year);
        $variable = static::getVariableHolidays($countryCode, $year);

        return array_unique(array_merge($fixed, $variable));
    }

    private static function getFixedHolidays(string $countryCode, int $year): array
    {
        $fixed = match(strtoupper($countryCode)) {
            'NG' => [
                "{$year}-01-01", // New Year
                "{$year}-01-03", // New Year Holiday
                "{$year}-05-01", // Workers Day
                "{$year}-06-12", // Democracy Day
                "{$year}-10-01", // Independence Day
                "{$year}-12-25", // Christmas
                "{$year}-12-26", // Boxing Day
            ],
            'GH' => [
                "{$year}-01-01", // New Year
                "{$year}-03-06", // Independence Day
                "{$year}-05-01", // Workers Day
                "{$year}-07-01", // Republic Day
                "{$year}-12-25", // Christmas
                "{$year}-12-26", // Boxing Day
            ],
            'KE' => [
                "{$year}-01-01",
                "{$year}-05-01",
                "{$year}-06-01", // Madaraka Day
                "{$year}-10-10", // Huduma Day
                "{$year}-10-20", // Mashujaa Day
                "{$year}-12-12", // Jamhuri Day
                "{$year}-12-25",
                "{$year}-12-26",
            ],
            'ZA' => [
                "{$year}-01-01",
                "{$year}-03-21", // Human Rights Day
                "{$year}-04-27", // Freedom Day
                "{$year}-05-01",
                "{$year}-06-16", // Youth Day
                "{$year}-08-09", // Women's Day
                "{$year}-09-24", // Heritage Day
                "{$year}-12-16", // Day of Reconciliation
                "{$year}-12-25",
                "{$year}-12-26",
            ],
            'GB' => [
                "{$year}-01-01",
                "{$year}-05-01",
                "{$year}-12-25",
                "{$year}-12-26",
            ],
            'US' => [
                "{$year}-01-01",
                "{$year}-07-04", // Independence Day
                "{$year}-11-11", // Veterans Day
                "{$year}-12-25",
            ],
            default => [
                "{$year}-01-01",
                "{$year}-12-25",
            ],
        };

        return $fixed;
    }

    private static function getVariableHolidays(string $countryCode, int $year): array
    {
        $holidays = [];

        // Easter calculation
        $easter = static::calculateEaster($year);
        $easterDate = \Carbon\Carbon::create($year, 1, 1)->setDate($year, $easter['month'], $easter['day']);

        $goodFriday = $easterDate->copy()->subDays(2)->format('Y-m-d');
        $easterMonday = $easterDate->copy()->addDay()->format('Y-m-d');

        $easterCountries = ['NG', 'GH', 'KE', 'ZA', 'GB', 'US', 'DE', 'FR', 'IT', 'ES'];

        if (in_array(strtoupper($countryCode), $easterCountries)) {
            $holidays[] = $goodFriday;
            $holidays[] = $easterDate->format('Y-m-d');
            $holidays[] = $easterMonday;
        }

        return $holidays;
    }

    private static function calculateEaster(int $year): array
    {
        $a = $year % 19;
        $b = intdiv($year, 100);
        $c = $year % 100;
        $d = intdiv($b, 4);
        $e = $b % 4;
        $f = intdiv($b + 8, 25);
        $g = intdiv($b - $f + 1, 3);
        $h = (19 * $a + $b - $d - $g + 15) % 30;
        $i = intdiv($c, 4);
        $k = $c % 4;
        $l = (32 + 2 * $e + 2 * $i - $h - $k) % 7;
        $m = intdiv($a + 11 * $h + 22 * $l, 451);
        $month = intdiv($h + $l - 7 * $m + 114, 31);
        $day = (($h + $l - 7 * $m + 114) % 31) + 1;

        return ['month' => $month, 'day' => $day];
    }

    public static function isHoliday(string $date, string $countryCode): bool
    {
        $year = (int) substr($date, 0, 4);
        return in_array($date, static::getHolidays($countryCode, $year));
    }
}