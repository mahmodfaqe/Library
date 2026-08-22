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
     */
    public static function request(int $timeout = 60): PendingRequest
    {
        return Http::timeout($timeout)->withOptions([
            'curl' => [CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4],
        ]);
    }
}
