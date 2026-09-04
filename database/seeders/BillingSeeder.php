<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BillingSeeder extends Seeder
{
    public function run(): void
    {
        // Gateway charges
        $charges = [
            [
                'gateway'     => 'paystack',
                'region'      => 'local',
                'percentage'  => 0.0150,
                'fixed_fee'   => 100.00,
                'cap'         => 2000.00,
                'absorb'      => true,
                'is_active'   => true,
                'description' => 'Paystack Nigeria: 1.5% + ₦100, capped at ₦2,000',
            ],
            [
                'gateway'     => 'paystack',
                'region'      => 'international',
                'percentage'  => 0.0380,
                'fixed_fee'   => 100.00,
                'cap'         => null,
                'absorb'      => true,
                'is_active'   => true,
                'description' => 'Paystack International: 3.8% + ₦100',
            ],
            [
                'gateway'     => 'flutterwave',
                'region'      => 'local',
                'percentage'  => 0.0140,
                'fixed_fee'   => 0.00,
                'cap'         => 2800.00,
                'absorb'      => true,
                'is_active'   => true,
                'description' => 'Flutterwave Nigeria: 1.4%, capped at ₦2,800',
            ],
            [
                'gateway'     => 'flutterwave',
                'region'      => 'international',
                'percentage'  => 0.0380,
                'fixed_fee'   => 0.00,
                'cap'         => null,
                'absorb'      => true,
                'is_active'   => true,
                'description' => 'Flutterwave International: 3.8%',
            ],
        ];

        foreach ($charges as $charge) {
            DB::table('gateway_charges')->updateOrInsert(
                ['gateway' => $charge['gateway'], 'region' => $charge['region']],
                array_merge($charge, ['created_at' => now(), 'updated_at' => now()])
            );
        }

        // Billing settings
        $settings = [
            ['key' => 'base_currency',          'value' => 'NGN',      'type' => 'string',  'label' => 'Base Currency',              'description' => 'All plan prices are set in this currency'],
            ['key' => 'grace_period_days',       'value' => '7',        'type' => 'integer', 'label' => 'Grace Period (days)',        'description' => 'Days after expiry before access is locked'],
            ['key' => 'reminder_days',           'value' => '14',       'type' => 'integer', 'label' => 'Reminder Days Before Expiry','description' => 'Send renewal reminder this many days before expiry'],
            ['key' => 'reminder_days_urgent',    'value' => '3',        'type' => 'integer', 'label' => 'Urgent Reminder Days',       'description' => 'Send urgent reminder this many days before expiry'],
            ['key' => 'paystack_secret_key',     'value' => '',         'type' => 'string',  'label' => 'Paystack Secret Key',        'description' => 'From Paystack dashboard'],
            ['key' => 'paystack_public_key',     'value' => '',         'type' => 'string',  'label' => 'Paystack Public Key',        'description' => 'From Paystack dashboard'],
            ['key' => 'flutterwave_secret_key',  'value' => '',         'type' => 'string',  'label' => 'Flutterwave Secret Key',     'description' => 'From Flutterwave dashboard'],
            ['key' => 'flutterwave_public_key',  'value' => '',         'type' => 'string',  'label' => 'Flutterwave Public Key',     'description' => 'From Flutterwave dashboard'],
            ['key' => 'enabled_gateways',        'value' => '["paystack","flutterwave"]', 'type' => 'json', 'label' => 'Enabled Gateways', 'description' => 'Which payment gateways are active'],
            ['key' => 'frankfurter_cache_hours', 'value' => '24',       'type' => 'integer', 'label' => 'Exchange Rate Cache (hours)', 'description' => 'How long to cache Frankfurter exchange rates'],
            ['key' => 'default_trial_days',      'value' => '14',       'type' => 'integer', 'label' => 'Default Trial Days',          'description' => 'Trial length applied when seeding a new plan that doesn\'t specify its own trial_days'],
        ];

        foreach ($settings as $setting) {
            DB::table('billing_settings')->updateOrInsert(
                ['key' => $setting['key']],
                array_merge($setting, ['created_at' => now(), 'updated_at' => now()])
            );
        }
    }
}