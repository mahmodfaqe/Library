<?php

namespace App\Http\Middleware;

use App\Support\Locale;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Kept for the routes and tests that enumerate the site's locales.
     *
     * @var list<string>
     */
    public const LOCALES = Locale::SUPPORTED;

    /**
     * Pick the locale for this request.
     *
     * The URL wins: /ar is Arabic no matter what the visitor chose before, so
     * every language has one stable, crawlable address. Pages without a locale
     * prefix — the admin panel — fall back to the visitor's last choice, and
     * the site root is always the default locale.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $fromUrl = $request->segment(1);

        if (Locale::supports($fromUrl)) {
            App::setLocale($fromUrl);
            $request->session()->put('locale', $fromUrl);
        } elseif ($request->path() === '/') {
            App::setLocale(Locale::DEFAULT);
        } else {
            $locale = $request->session()->get('locale', Locale::DEFAULT);

            App::setLocale(Locale::supports($locale) ? $locale : Locale::DEFAULT);
        }

        return $next($request);
    }
}
