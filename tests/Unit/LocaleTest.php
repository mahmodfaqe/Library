<?php

namespace Tests\Unit;

use App\Support\Locale;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class LocaleTest extends TestCase
{
    public static function ltrLocales(): array
    {
        return [['en'], ['tr'], ['ku-badini-lat']];
    }

    public static function rtlLocales(): array
    {
        return [['ku-sorani'], ['ku-badini'], ['ku-hawrami'], ['ar'], ['fa']];
    }

    #[DataProvider('ltrLocales')]
    public function test_latin_script_locales_are_left_to_right(string $locale): void
    {
        $this->app->setLocale($locale);

        $this->assertTrue(Locale::isLtr());
        $this->assertSame('ltr', Locale::dir());
        $this->assertSame('footer-year-en', Locale::yearClass());
    }

    #[DataProvider('rtlLocales')]
    public function test_arabic_script_locales_are_right_to_left(string $locale): void
    {
        $this->app->setLocale($locale);

        $this->assertFalse(Locale::isLtr());
        $this->assertSame('rtl', Locale::dir());
        $this->assertSame('footer-year-ar', Locale::yearClass());
    }

    public function test_every_kurdish_variant_reports_ku_as_its_html_lang(): void
    {
        foreach (['ku-sorani', 'ku-badini', 'ku-badini-lat', 'ku-hawrami'] as $locale) {
            $this->app->setLocale($locale);
            $this->assertSame('ku', Locale::htmlLang());
        }
    }

    public function test_other_locales_use_their_own_html_lang(): void
    {
        foreach (['en', 'ar', 'fa', 'tr'] as $locale) {
            $this->app->setLocale($locale);
            $this->assertSame($locale, Locale::htmlLang());
        }
    }
}
