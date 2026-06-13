<?php

return [

    'defaults' => [
        'guard'     => 'web',
        'passwords' => 'users',
    ],

    'guards' => [
        'web' => [
            'driver'   => 'session',
            'provider' => 'users',
        ],
        'platform' => [
            'driver'   => 'session',
            'provider' => 'platform_users',
        ],
        'client' => [
            'driver'   => 'session',
            'provider' => 'clients',
        ],
        'vendor' => [
            'driver'   => 'session',
            'provider' => 'vendor_accounts',
        ],
    ],

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model'  => App\Models\Tenant\User::class,
        ],
        'platform_users' => [
            'driver' => 'eloquent',
            'model'  => App\Models\Central\PlatformUser::class,
        ],
        'clients' => [
            'driver' => 'eloquent',
            'model'  => App\Models\Central\Client::class,
        ],
        'vendor_accounts' => [
            'driver' => 'eloquent',
            'model'  => App\Models\Central\VendorAccount::class,
        ],
    ],

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table'    => 'password_reset_tokens',
            'expire'   => 60,
            'throttle' => 60,
        ],
        'platform_users' => [
            'provider' => 'platform_users',
            'table'    => 'password_reset_tokens',
            'expire'   => 60,
            'throttle' => 60,
        ],
        'clients' => [
            'provider' => 'clients',
            'table'    => 'password_reset_tokens',
            'expire'   => 60,
            'throttle' => 60,
        ],
        'vendor_accounts' => [
            'provider' => 'vendor_accounts',
            'table'    => 'password_reset_tokens',
            'expire'   => 60,
            'throttle' => 60,
        ],
    ],

    'password_timeout' => 10800,
];