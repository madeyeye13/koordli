<?php

return [
    /*
    | Every blog file-storage call in the app reads this ONE value,
    | never a hardcoded 'public' string directly. Switching to S3 later
    | is exactly one .env change — BLOG_STORAGE_DISK=s3 — no code touched.
    */
    'storage_disk' => env('BLOG_STORAGE_DISK', 'public'),

    'image' => [
        'max_width'  => 1600, // resized down if larger, never upscaled
        'quality'    => 82,   // JPEG/WebP compression quality
    ],
];