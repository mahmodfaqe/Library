<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminAuthenticated
{
    /**
     * Let signed-in staff through, optionally narrowing to one role.
     *
     * Usage: ->middleware('admin.auth') or ->middleware('admin.auth:admin')
     */
    public function handle(Request $request, Closure $next, ?string $role = null): Response
    {
        if (! Auth::check()) {
            return redirect()->guest(route('admin.login'));
        }

        if ($role !== null && Auth::user()->role !== $role) {
            abort(403);
        }

        return $next($request);
    }
}
