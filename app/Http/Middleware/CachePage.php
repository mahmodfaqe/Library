<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CachePage
{
    /**
     * Cache the rendered home page to storage/framework/pagecache so
     * repeat visits skip the full Blade/DB render. Admin writes already
     * invalidate this cache (see AdminController::clearPageCache()).
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only plain GET requests to the exact home page are cached.
        if (! $request->isMethod('GET') || ! $request->is('/')) {
            return $next($request);
        }

        // Never serve cached content to visitors carrying cookies
        // (e.g. an authenticated admin session).
        if ($request->cookies->count() > 0) {
            return $next($request);
        }

        $file = storage_path('framework/pagecache/home.html');

        if (is_file($file) && $this->isFresh($file)) {
            $html = file_get_contents($file);

            // The cached copy carries the token of the request that
            // warmed the cache. Substitute the current visitor's session
            // token so the embedded @csrf field passes VerifyCsrfToken.
            // (pagecache runs after StartSession, so the session exists.)
            if ($request->hasSession()) {
                $html = $this->withLiveCsrfToken($html, $request->session()->token());
            }

            return new Response($html, 200, [
                'Content-Type' => 'text/html; charset=UTF-8',
                'X-Page-Cache' => 'HIT',
            ]);
        }

        $response = $next($request);

        if ($response->getStatusCode() === 200 && $response->getContent() !== null) {
            if (! is_dir(dirname($file))) {
                @mkdir(dirname($file), 0775, true);
            }
            @file_put_contents($file, $response->getContent());
        }

        $response->headers->set('X-Page-Cache', 'MISS');

        return $response;
    }

    private function isFresh(string $file): bool
    {
        return filemtime($file) > time() - 3600;
    }

    /**
     * Replace the value of the first hidden _token input with the
     * token of the current session.
     */
    private function withLiveCsrfToken(string $html, string $token): string
    {
        return preg_replace_callback(
            '/(<input[^>]*name="_token"[^>]*value=")[^"]*("[^>]*>)/',
            fn (array $m) => $m[1].e($token).$m[2],
            $html,
            1
        );
    }
}
