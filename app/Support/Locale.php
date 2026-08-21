<?php

namespace App\Support;

use Illuminate\Support\Facades\App;

class Locale
{
    /**
     * Supported locales written left-to-right. Every other supported locale
     * (the Arabic-script ones) is right-to-left.
     */
    private const LTR = ['en', 'tr', 'ku-badini-lat'];

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
}
