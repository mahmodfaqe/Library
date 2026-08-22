<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Headers a public university site is expected to send.
     *
     * The CSP is deliberately explicit about the third parties the page loads:
     * the visitor counter and the department links. Anything not listed here
     * is refused by the browser, so an injected script cannot phone home.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // The analytics host and script are configured, not hard-coded, so the
        // policy follows the site onto university-owned accounts.
        $analytics = rtrim((string) config('library.analytics.host'), '/');
        $script = (string) config('library.analytics.script');
        $scriptOrigin = $script !== '' ? parse_url($script, PHP_URL_SCHEME).'://'.parse_url($script, PHP_URL_HOST) : '';

        $csp = implode('; ', array_filter([
            "default-src 'self'",
            // The page carries a few small inline scripts and the Tailwind
            // build emits inline styles.
            trim("script-src 'self' 'unsafe-inline' ".($analytics ? $scriptOrigin : '')),
            "style-src 'self' 'unsafe-inline'",
            trim("img-src 'self' data: ".$analytics),
            "font-src 'self'",
            trim("connect-src 'self' ".$analytics),
            "form-action 'self'",
            "frame-ancestors 'none'",
            "base-uri 'self'",
            'upgrade-insecure-requests',
        ]));

        $headers = [
            'Content-Security-Policy' => $csp,
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'DENY',
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
            'Permissions-Policy' => 'camera=(), microphone=(), geolocation=(), payment=()',
        ];

        // HSTS only means anything over TLS, and pinning it on a plain-HTTP
        // dev server would lock the developer's browser to https://localhost.
        if ($request->secure()) {
            $headers['Strict-Transport-Security'] = 'max-age=31536000; includeSubDomains';
        }

        foreach ($headers as $name => $value) {
            $response->headers->set($name, $value);
        }

        return $response;
    }
}
