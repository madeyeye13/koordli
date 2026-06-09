<?php

namespace Database\Seeders;

use App\Models\Central\CurrencySetting;
use Illuminate\Database\Seeder;

class CurrencySeeder extends Seeder
{
    public function run(): void
    {
        $currencies = [
            [
                'code'              => 'NGN',
                'name'              => 'Nigerian Naira',
                'symbol'            => '₦',
                'is_active'         => true,
                'gateway_supported' => ['paystack', 'flutterwave'],
            ],
            [
                'code'              => 'GHS',
                'name'              => 'Ghanaian Cedi',
                'symbol'            => '₵',
                'is_active'         => true,
                'gateway_supported' => ['paystack', 'flutterwave'],
            ],
            [
                'code'              => 'GBP',
                'name'              => 'British Pound',
                'symbol'            => '£',
                'is_active'         => true,
                'gateway_supported' => ['flutterwave'],
            ],
            [
                'code'              => 'USD',
                'name'              => 'US Dollar',
                'symbol'            => '$',
                'is_active'         => true,
                'gateway_supported' => ['paystack', 'flutterwave'],
            ],
            [
                'code'              => 'EUR',
                'name'              => 'Euro',
                'symbol'            => '€',
                'is_active'         => true,
                'gateway_supported' => ['flutterwave'],
            ],
            [
                'code'              => 'KES',
                'name'              => 'Kenyan Shilling',
                'symbol'            => 'KSh',
                'is_active'         => true,
                'gateway_supported' => ['paystack', 'flutterwave'],
            ],
            [
                'code'              => 'ZAR',
                'name'              => 'South African Rand',
                'symbol'            => 'R',
                'is_active'         => true,
                'gateway_supported' => ['flutterwave'],
            ],
        ];

        foreach ($currencies as $currency) {
            \App\Models\Central\CurrencySetting::firstOrCreate(
                ['code' => $currency['code']],
                $currency
            );
        }

        $this->command->info('Currencies seeded.');
    }
}