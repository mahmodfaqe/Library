<?php

return [

    /*
    |--------------------------------------------------------------------------
    | External services
    |--------------------------------------------------------------------------
    | Every third party the public page depends on. These live here rather
    | than in the templates so moving the project onto university-owned
    | accounts is a change to .env, not a change to code.
    */

    'drive' => [
        'main' => env('LIBRARY_DRIVE_MAIN', ''),
        'secondary' => env('LIBRARY_DRIVE_SECONDARY', ''),
    ],

    'qr_url' => env('LIBRARY_QR_URL', ''),

    /*
     * Read-only Google API key, used by `php artisan books:import-drive` to
     * list and fetch the PDFs in a publicly shared folder. Not needed at
     * runtime — only when importing.
     */
    'google_api_key' => env('LIBRARY_GOOGLE_API_KEY', ''),

    'university_url' => env('LIBRARY_UNIVERSITY_URL', 'https://uor.edu.krd'),

    'analytics' => [
        // Empty disables the counter entirely — no third-party request is made.
        'host' => env('LIBRARY_ANALYTICS_HOST', ''),
        'script' => env('LIBRARY_ANALYTICS_SCRIPT', 'https://gc.zgo.at/count.js'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Visitor feedback
    |--------------------------------------------------------------------------
    | How long a submitted message is kept before it is deleted. The privacy
    | notice quotes this number, so change both together.
    */

    'feedback_retention_days' => (int) env('LIBRARY_FEEDBACK_RETENTION_DAYS', 365),

    /*
    |--------------------------------------------------------------------------
    | Uploads
    |--------------------------------------------------------------------------
    | Largest book PDF accepted, in kilobytes. nginx and PHP inside the
    | container are configured to match — raise all three together.
    */

    'max_upload_kb' => (int) env('LIBRARY_MAX_UPLOAD_KB', 51200),

];
