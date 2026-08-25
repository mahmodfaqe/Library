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
     * Optional proxy for Google Drive calls, e.g. socks5://127.0.0.1:18080.
     * Lets the import run off-server while still leaving from the address the
     * IP-restricted API key allows.
     */
    'drive_proxy' => env('LIBRARY_DRIVE_PROXY', ''),

    /*
     * Path to a Google service account key file. When set, Drive is called as
     * that account instead of with the anonymous API key — which is what makes
     * bulk reading possible, since an anonymous caller is cut off after a few
     * dozen downloads.
     */
    'drive_service_account' => env('LIBRARY_DRIVE_SERVICE_ACCOUNT', ''),

    /*
     * The passphrase database backups are encrypted with before any copy of
     * them leaves the server. Keep it in a password manager, never on the
     * server: a key beside the thing it protects protects nothing, and a key
     * lost with the server takes every backup with it.
     *
     * Left empty, backups are written in the clear — which is only safe while
     * they never go anywhere.
     */
    'backup_key' => env('BACKUP_KEY', ''),

    /*
     * Zenodo, run by CERN, mints a real DataCite DOI for nothing. It is how
     * the university can give a thesis a permanent identifier before it can
     * afford a registration agency of its own.
     *
     * Two tokens, deliberately. The sandbox is a full copy of Zenodo where
     * nothing is permanent, and it is what every first attempt should use —
     * because on the live site a published record can never be deleted.
     *
     * Make them at zenodo.org/account/settings/applications/tokens/new,
     * with the deposit:write and deposit:actions scopes.
     */
    'zenodo' => [
        'token' => env('ZENODO_TOKEN', ''),
        'sandbox_token' => env('ZENODO_SANDBOX_TOKEN', ''),
    ],

    /*
     * Hosts allowed to serve book covers, for the Content-Security-Policy.
     * Drive answers a thumbnail request from drive.google.com by redirecting
     * to googleusercontent, so both have to be listed.
     */
    'cover_hosts' => [
        'https://drive.google.com',
        'https://*.googleusercontent.com',
    ],

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
