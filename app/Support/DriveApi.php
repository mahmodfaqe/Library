<?php

namespace App\Support;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class DriveApi
{
    public const FILES = 'https://www.googleapis.com/drive/v3/files';

    /**
     * A request to the Drive API, pinned to IPv4.
     *
     * The API key is restricted to this server's address, and that allowlist
     * holds one IPv4 address. Left to itself the server prefers its IPv6
     * address, which is not on the list, and every call comes back 403
     * API_KEY_IP_ADDRESS_BLOCKED — for a reason the message makes clear only
     * if you read the address in it carefully.
     *
     * Guzzle manages the cURL handle itself, so this goes through its own
     * force_ip_resolve option rather than CURLOPT_IPRESOLVE, which it rejects.
     */
    public static function request(int $timeout = 60): PendingRequest
    {
        $options = ['force_ip_resolve' => 'v4'];

        // Set LIBRARY_DRIVE_PROXY to run the import or the detail extraction
        // from somewhere other than the server — an SSH SOCKS tunnel makes the
        // calls leave from the address the key trusts while the files
        // themselves never reach the server at all.
        if (filled($proxy = config('library.drive_proxy'))) {
            $options['proxy'] = $proxy;
        }

        return Http::timeout($timeout)->withOptions($options);
    }
}
