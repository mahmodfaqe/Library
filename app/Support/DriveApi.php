<?php

namespace App\Support;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class DriveApi
{
    public const FILES = 'https://www.googleapis.com/drive/v3/files';

    private const TOKEN_URL = 'https://oauth2.googleapis.com/token';

    /**
     * How long Google's access tokens last, less a minute of slack.
     */
    private const TOKEN_TTL = 3540;

    /**
     * A request to the Drive API.
     *
     * Signed in as the service account when one is configured, and falling
     * back to the plain API key otherwise. The difference matters for bulk
     * work: an API key is an anonymous caller, and Drive answers a few dozen
     * downloads from one address with an HTML "Sorry..." page that takes hours
     * to clear. A signed request is attributed to an account with its own
     * quota and is not treated as abuse.
     *
     * Pinned to IPv4 because an API key restricted to this server's address
     * lists one IPv4 address, while the server prefers its IPv6 one — every
     * call then comes back 403 API_KEY_IP_ADDRESS_BLOCKED, for a reason the
     * message makes clear only if you read the address in it carefully.
     * Guzzle manages the cURL handle itself, so this goes through its own
     * force_ip_resolve option rather than CURLOPT_IPRESOLVE, which it rejects.
     */
    public static function request(int $timeout = 60): PendingRequest
    {
        $options = ['force_ip_resolve' => 'v4'];

        // Set LIBRARY_DRIVE_PROXY to run the import or the detail extraction
        // from somewhere other than the server — an SSH SOCKS tunnel makes the
        // calls leave from the address an IP-restricted key allows, while the
        // files themselves never reach the server at all.
        if (filled($proxy = config('library.drive_proxy'))) {
            $options['proxy'] = $proxy;
        }

        $request = Http::timeout($timeout)->withOptions($options);

        return ($token = self::accessToken()) ? $request->withToken($token) : $request;
    }

    /**
     * Whatever identifies the caller, to be merged into the query string.
     *
     * A signed request carries its identity in the Authorization header and
     * must not also send a key.
     *
     * @return array<string, string>
     */
    public static function credentials(?string $key): array
    {
        return self::accessToken() !== null || blank($key) ? [] : ['key' => $key];
    }

    /**
     * An access token for the configured service account, or null when none is
     * configured and the API key is to be used instead.
     */
    public static function accessToken(): ?string
    {
        $path = config('library.drive_service_account');

        if (blank($path) || ! is_file($path)) {
            return null;
        }

        return Cache::remember(
            'drive.access-token.'.md5($path),
            self::TOKEN_TTL,
            fn () => self::mintToken($path)
        );
    }

    /**
     * Trade the service account's key for an access token.
     *
     * Google's own client library would do this, but it brings a large
     * dependency tree for what is one signed JSON blob and one POST.
     */
    private static function mintToken(string $path): string
    {
        $account = json_decode((string) file_get_contents($path), true);

        foreach (['client_email', 'private_key'] as $field) {
            if (blank($account[$field] ?? null)) {
                throw new RuntimeException("The service account file is missing {$field}: {$path}");
            }
        }

        $now = time();
        $payload = self::base64url((string) json_encode(['alg' => 'RS256', 'typ' => 'JWT']))
            .'.'.self::base64url((string) json_encode([
                'iss' => $account['client_email'],
                'scope' => 'https://www.googleapis.com/auth/drive.readonly',
                'aud' => self::TOKEN_URL,
                'iat' => $now,
                'exp' => $now + 3600,
            ]));

        if (! openssl_sign($payload, $signature, $account['private_key'], OPENSSL_ALGO_SHA256)) {
            throw new RuntimeException('Could not sign the service account assertion.');
        }

        $response = Http::asForm()->timeout(30)->post(self::TOKEN_URL, [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $payload.'.'.self::base64url($signature),
        ]);

        if ($response->failed() || blank($token = $response->json('access_token'))) {
            throw new RuntimeException(
                'Google refused the service account: '
                .($response->json('error_description') ?? $response->body())
            );
        }

        return $token;
    }

    private static function base64url(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}
