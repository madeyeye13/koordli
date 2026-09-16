<?php

return [
    'cname_target' => env('PUBLIC_DOMAIN_CNAME_TARGET', 'koordli.site'),
    'apex_ip' => env('PUBLIC_DOMAIN_APEX_IP', '187.77.102.232'),
    'verification_txt_prefix' => '_koordli-verify',
    'statuses' => [
        'pending',
        'verified',
        'failed',
        'disabled',
    ],
    'types' => [
        'subdomain',
        'apex',
    ],
];
