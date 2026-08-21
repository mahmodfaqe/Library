<?php

namespace App\Support;

use Illuminate\Support\Facades\App;

class Locale
{
    /**
     * The locale served at the site root, without a URL prefix.
     */
    public const DEFAULT = 'ku-sorani';

    /**
     * Every locale the site is published in, in menu order.
     */
    public const SUPPORTED = [
        'ku-sorani',
        'ku-badini',
        'ku-badini-lat',
        'ku-hawrami',
        'en',
        'ar',
        'fa',
        'tr',
    ];

    /**
     * Supported locales written left-to-right. Every other supported locale
     * (the Arabic-script ones) is right-to-left.
     */
    private const LTR = ['en', 'tr', 'ku-badini-lat'];

    /**
     * BCP 47 tags for the hreflang and og:locale tags. The site's own locale
     * keys are not valid language tags, so they are mapped here.
     */
    private const LANGUAGE_TAGS = [
        'ku-sorani' => 'ckb',
        'ku-badini' => 'kmr-Arab',
        'ku-badini-lat' => 'kmr-Latn',
        'ku-hawrami' => 'hac',
        'en' => 'en',
        'ar' => 'ar',
        'fa' => 'fa',
        'tr' => 'tr',
    ];

    public static function isLtr(): bool
    {
        return in_array(App::getLocale(), self::LTR, true);
    }

    public static function dir(): string
    {
        return self::isLtr() ? 'ltr' : 'rtl';
    }

    /**
     * Value for the <html lang> attribute. The four Kurdish variants share
     * one BCP 47 tag.
     */
    public static function htmlLang(): string
    {
        $locale = App::getLocale();

        return str_starts_with($locale, 'ku') ? 'ku' : $locale;
    }

    /**
     * Class the footer year is rendered into, so the inline script knows
     * whether to convert the digits to Arabic-Indic numerals.
     */
    public static function yearClass(): string
    {
        return self::isLtr() ? 'footer-year-en' : 'footer-year-ar';
    }

    public static function supports(?string $locale): bool
    {
        return $locale !== null && in_array($locale, self::SUPPORTED, true);
    }

    /**
     * The BCP 47 tag for a locale, for hreflang and og:locale.
     */
    public static function languageTag(?string $locale = null): string
    {
        return self::LANGUAGE_TAGS[$locale ?? App::getLocale()] ?? 'ckb';
    }

    /**
     * The home page URL for a locale. The default locale lives at the site
     * root; the rest are prefixed, so every language has a crawlable address.
     */
    public static function url(?string $locale = null): string
    {
        $locale ??= App::getLocale();

        return $locale === self::DEFAULT ? url('/') : url($locale);
    }
}
