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
     * every language has one stable, crawlable address.
     *
     * Every public page has an unprefixed address that IS the default locale —
     * /, /books, /privacy — so those are Sorani whatever the visitor looked at
     * last. Reading it from the session instead would mean a visitor who
     * switched to Sorani from the English catalogue landed on /books and was
     * handed English again, and it would let one cached copy be served under
     * the wrong language.
     *
     * Only the admin panel, which has no localised address, falls back to the
     * staff member's last choice.
     *
     * This runs twice: once globally, so that a page nobody routed to — a 404
     * — is still in the language its address asked for, and again inside the
     * web group once the session exists, which is what the admin panel needs.
     * The session is therefore only read when there is one.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $fromUrl = $request->segment(1);

        if (Locale::supports($fromUrl)) {
            App::setLocale($fromUrl);

            if ($request->hasSession()) {
                $request->session()->put('locale', $fromUrl);
            }
        } elseif ($request->is('admin', 'admin/*')) {
            $locale = $request->hasSession()
                ? $request->session()->get('locale', Locale::DEFAULT)
                : Locale::DEFAULT;

            App::setLocale(Locale::supports($locale) ? $locale : Locale::DEFAULT);
        } else {
            App::setLocale(Locale::DEFAULT);
        }

        return $next($request);
    }
}
