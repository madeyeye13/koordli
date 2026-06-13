<?php

namespace App\Helpers;

class CurrencyHelper
{
    public static function symbol(string $currency): string
    {
        return match($currency) {
            'NGN' => '₦',
            'GHS' => '₵',
            'GBP' => '£',
            'USD' => '$',
            'EUR' => '€',
            'KES' => 'KSh',
            'ZAR' => 'R',
            default => $currency . ' '
        };
    }

    public static function format(float $amount, string $currency): string
    {
        return static::symbol($currency) . number_format($amount, 2);
    }

    public static function forTenant(): string
    {
        return static::symbol(
            auth()->user()?->tenant?->billing_currency ?? 'NGN'
        );
    }

    public static function fromCountry(string $countryCode): string
    {
        return match(strtoupper($countryCode)) {
            'NG' => 'NGN',
            'GH' => 'GHS',
            'GB' => 'GBP',
            'US' => 'USD',
            'CA' => 'USD',
            'AU' => 'USD',
            'DE', 'FR', 'IT', 'ES', 'NL', 'BE', 'PT', 'AT', 'FI', 'IE' => 'EUR',
            'KE' => 'KES',
            'ZA' => 'ZAR',
            default => 'USD'
        };
    }

    public static function countries(): array
    {
        return [
            'NG' => 'Nigeria',
            'GH' => 'Ghana',
            'ZA' => 'South Africa',
            'KE' => 'Kenya',
            'GB' => 'United Kingdom',
            'US' => 'United States',
            'CA' => 'Canada',
            'AU' => 'Australia',
            'DE' => 'Germany',
            'FR' => 'France',
            'IT' => 'Italy',
            'ES' => 'Spain',
            'NL' => 'Netherlands',
            'BE' => 'Belgium',
            'PT' => 'Portugal',
            'AT' => 'Austria',
            'FI' => 'Finland',
            'IE' => 'Ireland',
            'AE' => 'UAE',
            'SA' => 'Saudi Arabia',
            'IN' => 'India',
            'SG' => 'Singapore',
        ];
    }
}