<?php

namespace Database\Seeders;

use App\Models\Central\FeatureFlag;
use App\Models\Central\Plan;
use App\Models\Central\PlanFeature;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        // ── Feature Flags ──────────────────────────────────────────
        $flags = [
            ['key' => 'max_events',        'label' => 'Maximum Events',          'description' => 'Maximum number of active events'],
            ['key' => 'max_staff',         'label' => 'Maximum Staff Members',   'description' => 'Maximum staff accounts per tenant'],
            ['key' => 'max_storage_mb',    'label' => 'Storage (MB)',            'description' => 'Total file storage in megabytes'],
            ['key' => 'max_guests',        'label' => 'Maximum Guests per Event','description' => 'Maximum guests per event'],
            ['key' => 'rsvp',              'label' => 'RSVP System',             'description' => 'Access to RSVP and guest management'],
            ['key' => 'runsheet',          'label' => 'Runsheet System',         'description' => 'Access to event-day runsheets'],
            ['key' => 'vendor_portal',     'label' => 'Vendor Portal',           'description' => 'Vendor registration and dashboard access'],
            ['key' => 'client_portal',     'label' => 'Client Portal',           'description' => 'Client-facing event portal'],
            ['key' => 'booking_forms',     'label' => 'Booking Forms',           'description' => 'Create hosted booking and consultation forms'],
            ['key' => 'custom_domain',     'label' => 'Custom Domain',           'description' => 'Use own domain instead of koordli subdomain'],
            ['key' => 'white_label',       'label' => 'White Label',             'description' => 'Remove Koordli branding completely'],
            ['key' => 'api_access',        'label' => 'API Access',              'description' => 'Access to public API'],
            ['key' => 'priority_support',  'label' => 'Priority Support',        'description' => 'Priority customer support'],
        ];

        foreach ($flags as $flag) {
            FeatureFlag::firstOrCreate(
                ['key' => $flag['key']],
                [...$flag, 'is_active' => true]
            );
        }

        // ── Plans ──────────────────────────────────────────────────
        $plans = [
            [
                'name'          => 'Free Trial',
                'slug'          => 'free-trial',
                'billing_cycle' => 'trial',
                'trial_days'    => 30,
                'is_active'     => true,
                'features'      => [
                    'max_events'     => '3',
                    'max_staff'      => '2',
                    'max_storage_mb' => '200',
                    'max_guests'     => '100',
                    'rsvp'           => 'true',
                    'runsheet'       => 'true',
                    'client_portal'  => 'true',
                    'booking_forms'  => 'true',
                    'vendor_portal'  => 'false',
                    'custom_domain'  => 'false',
                    'white_label'    => 'false',
                    'api_access'     => 'false',
                    'priority_support' => 'false',
                ],
                'limits' => [
                    'max_events'     => 3,
                    'max_staff'      => 2,
                    'max_storage_mb' => 200,
                    'max_guests'     => 100,
                ],
            ],
            [
                'name'          => 'Starter',
                'slug'          => 'starter',
                'billing_cycle' => 'monthly',
                'trial_days'    => 0,
                'is_active'     => true,
                'features'      => [
                    'max_events'     => '10',
                    'max_staff'      => '5',
                    'max_storage_mb' => '1000',
                    'max_guests'     => '300',
                    'rsvp'           => 'true',
                    'runsheet'       => 'true',
                    'client_portal'  => 'true',
                    'booking_forms'  => 'true',
                    'vendor_portal'  => 'true',
                    'custom_domain'  => 'false',
                    'white_label'    => 'false',
                    'api_access'     => 'false',
                    'priority_support' => 'false',
                ],
                'limits' => [
                    'max_events'     => 10,
                    'max_staff'      => 5,
                    'max_storage_mb' => 1000,
                    'max_guests'     => 300,
                ],
            ],
            [
                'name'          => 'Pro',
                'slug'          => 'pro',
                'billing_cycle' => 'monthly',
                'trial_days'    => 0,
                'is_active'     => true,
                'features'      => [
                    'max_events'     => '50',
                    'max_staff'      => '20',
                    'max_storage_mb' => '10000',
                    'max_guests'     => '1000',
                    'rsvp'           => 'true',
                    'runsheet'       => 'true',
                    'client_portal'  => 'true',
                    'booking_forms'  => 'true',
                    'vendor_portal'  => 'true',
                    'custom_domain'  => 'true',
                    'white_label'    => 'false',
                    'api_access'     => 'true',
                    'priority_support' => 'true',
                ],
                'limits' => [
                    'max_events'     => 50,
                    'max_staff'      => 20,
                    'max_storage_mb' => 10000,
                    'max_guests'     => 1000,
                ],
            ],
            [
                'name'          => 'Enterprise',
                'slug'          => 'enterprise',
                'billing_cycle' => 'monthly',
                'trial_days'    => 0,
                'is_active'     => true,
                'features'      => [
                    'max_events'     => 'unlimited',
                    'max_staff'      => 'unlimited',
                    'max_storage_mb' => 'unlimited',
                    'max_guests'     => 'unlimited',
                    'rsvp'           => 'true',
                    'runsheet'       => 'true',
                    'client_portal'  => 'true',
                    'booking_forms'  => 'true',
                    'vendor_portal'  => 'true',
                    'custom_domain'  => 'true',
                    'white_label'    => 'true',
                    'api_access'     => 'true',
                    'priority_support' => 'true',
                ],
                'limits' => [
                    'max_events'     => -1, // -1 = unlimited
                    'max_staff'      => -1,
                    'max_storage_mb' => -1,
                    'max_guests'     => -1,
                ],
            ],
        ];

        foreach ($plans as $planData) {
            $features = $planData['features'];
            $limits   = $planData['limits'];
            unset($planData['features'], $planData['limits']);

            $plan = Plan::firstOrCreate(
                ['slug' => $planData['slug']],
                [...$planData, 'features' => $features, 'limits' => $limits]
            );
        }

        $this->command->info('Plans and feature flags seeded.');
    }
}