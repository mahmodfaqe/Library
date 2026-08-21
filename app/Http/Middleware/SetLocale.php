<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public const LOCALES = ['ku-sorani', 'ku-badini', 'ku-badini-lat', 'ku-hawrami', 'en', 'ar', 'fa', 'tr'];

    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->session()->get('locale', 'ku-sorani');

        if (in_array($locale, self::LOCALES, true)) {
            App::setLocale($locale);
        }

        return $next($request);
    }
}
