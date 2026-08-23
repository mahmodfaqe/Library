<?php

use App\Http\Middleware\CachePage;
use App\Http\Middleware\EnsureAdminAuthenticated;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\SetLocale;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin.auth' => EnsureAdminAuthenticated::class,
            'pagecache' => CachePage::class,
        ]);

        $middleware->web(append: [
            SetLocale::class,
        ]);

        // Globally as well as in the web group: an unrouted request never
        // reaches the group, so without this a 404 would always be English
        // whatever address the visitor typed.
        $middleware->prepend(SetLocale::class);

        $middleware->append(SecurityHeaders::class);

        // The app runs in a container behind the host's nginx, so the real
        // scheme and host arrive in X-Forwarded-*. Without this every
        // generated URL — canonical, hreflang, assets — would say http and
        // the wrong host.
        $middleware->trustProxies(at: '*', headers: Request::HEADER_X_FORWARDED_FOR
            | Request::HEADER_X_FORWARDED_HOST
            | Request::HEADER_X_FORWARDED_PORT
            | Request::HEADER_X_FORWARDED_PROTO);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
