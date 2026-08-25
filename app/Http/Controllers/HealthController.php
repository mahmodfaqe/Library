<?php

namespace App\Http\Controllers;

use App\Support\HealthChecks;
use Illuminate\Http\JsonResponse;

class HealthController extends Controller
{
    /**
     * What an outside monitor asks every few minutes.
     *
     * This is deliberately separate from /up, which Docker's own healthcheck
     * watches: a stale backup means someone should be told, not that the
     * container should be restarted. /up answers "the application is running";
     * this answers "the service is well".
     *
     * A monitor can only tell you the server has gone if it is watching from
     * somewhere else, so this needs no credentials — and in exchange it gives
     * away nothing an attacker could use.
     */
    public function __invoke(): JsonResponse
    {
        $checks = HealthChecks::all();
        $ok = HealthChecks::passing($checks);

        return response()->json([
            'status' => $ok ? 'ok' : 'failing',
            'checks' => $checks,
            'time' => now()->toIso8601String(),
        ], $ok ? 200 : 503)->header('Cache-Control', 'no-store');
    }
}
