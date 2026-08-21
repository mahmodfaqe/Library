<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class CachePage
{
    /**
     * How long a cached copy stays servable.
     */
    private const TTL_SECONDS = 3600;

    /**
     * Cache the rendered home page to storage/framework/pagecache so repeat
     * visits skip the full Blade/DB render. Admin writes invalidate the cache
     * (see AdminController::clearPageCache()).
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only plain GET requests to the exact home page are cached.
        if (! $request->isMethod('GET') || ! $request->is('/')) {
            return $next($request);
        }

        // A response that depends on who is asking must never be shared, and a
        // shared copy must never be handed to someone expecting their own.
        if ($this->isPersonalised($request)) {
            return $next($request);
        }

        $file = $this->cacheFile();

        if (is_file($file) && $this->isFresh($file)) {
            $html = file_get_contents($file);

            // The cached copy carries the token of the request that warmed the
            // cache. Substitute the current visitor's session token so the
            // embedded @csrf field passes VerifyCsrfToken. (pagecache runs
            // after StartSession, so the session exists.)
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

    /**
     * The page is rendered per locale, so each locale gets its own copy.
     */
    private function cacheFile(): string
    {
        return storage_path('framework/pagecache/home-'.App::getLocale().'.html');
    }

    /**
     * Whether this request renders something specific to one visitor: the
     * admin's own view, a feedback confirmation, validation errors, or the
     * old input repopulating the feedback form.
     */
    private function isPersonalised(Request $request): bool
    {
        if (! $request->hasSession()) {
            return false;
        }

        $session = $request->session();

        return $session->has('admin_authenticated')
            || $session->has('feedback_sent')
            || $session->has('errors')
            || $session->hasOldInput();
    }

    private function isFresh(string $file): bool
    {
        return filemtime($file) > time() - self::TTL_SECONDS;
    }

    /**
     * Replace the value of the first hidden _token input with the token of the
     * current session.
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
