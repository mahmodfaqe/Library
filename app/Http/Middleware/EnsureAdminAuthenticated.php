<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminAuthenticated
{
    /**
     * Allow the request through only when the admin session flag is set.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->session()->has('admin_authenticated')) {
            return redirect()->guest(route('admin.login'));
        }

        return $next($request);
    }
}
