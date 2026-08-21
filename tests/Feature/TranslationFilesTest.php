<?php

namespace Tests\Feature;

use App\Http\Middleware\SetLocale;
use Tests\TestCase;

class TranslationFilesTest extends TestCase
{
    /**
     * Flatten a translation array to its key paths, ignoring list indices so a
     * locale is free to have a different number of paragraphs or objectives.
     */
    private function keyPaths(array $data, string $prefix = ''): array
    {
        $paths = [];

        foreach ($data as $key => $value) {
            if (is_int($key)) {
                // A list entry: describe its shape once, not per index.
                $paths = array_merge($paths, is_array($value)
                    ? $this->keyPaths($value, $prefix.'[]')
                    : [$prefix.'[]']);

                continue;
            }

            $path = $prefix === '' ? $key : "$prefix.$key";
            $paths = array_merge($paths, is_array($value) ? $this->keyPaths($value, $path) : [$path]);
        }

        return array_values(array_unique($paths));
    }

    private function load(string $locale, string $file): array
    {
        $path = lang_path("$locale/$file.php");

        $this->assertFileExists($path, "Missing $file.php for $locale");

        return require $path;
    }

    public function test_every_locale_defines_the_same_message_keys(): void
    {
        $reference = $this->keyPaths($this->load('ku-sorani', 'messages'));
        sort($reference);

        foreach (SetLocale::LOCALES as $locale) {
            $paths = $this->keyPaths($this->load($locale, 'messages'));
            sort($paths);

            $this->assertSame($reference, $paths, "messages.php for $locale does not match ku-sorani");
        }
    }

    public function test_no_translation_carries_markup(): void
    {
        foreach (SetLocale::LOCALES as $locale) {
            $withMarkup = [];

            $messages = $this->load($locale, 'messages');

            array_walk_recursive(
                $messages,
                function ($value, $key) use (&$withMarkup) {
                    if (is_string($value) && preg_match('/<[a-z]+[\s>\/]/i', $value)) {
                        $withMarkup[] = $key;
                    }
                }
            );

            $this->assertSame([], $withMarkup, "messages.php for $locale still contains HTML");
        }
    }

    public function test_the_placeholders_the_views_rely_on_are_present(): void
    {
        foreach (SetLocale::LOCALES as $locale) {
            $messages = $this->load($locale, 'messages');

            $this->assertStringContainsString(':year', $messages['footer']['copyright'], $locale);
            $this->assertStringContainsString(':link', $messages['footer']['uor_line'], $locale);

            $this->assertCount(1, array_filter(
                $messages['intro']['objectives'],
                fn (string $o) => str_contains($o, ':qr')
            ), "$locale should have exactly one objective with a :qr placeholder");

            $this->assertCount(1, array_filter(
                $messages['history']['paragraphs'],
                fn (string $p) => str_contains($p, ':date')
            ), "$locale should have exactly one history paragraph with a :date placeholder");
        }
    }

    public function test_exactly_one_person_carries_the_social_links(): void
    {
        foreach (SetLocale::LOCALES as $locale) {
            $people = $this->load($locale, 'messages')['intro']['people'];

            $this->assertCount(1, array_filter($people, fn (array $p) => $p['social']), $locale);
        }
    }

    public function test_arabic_script_locales_do_not_trail_off_into_latin(): void
    {
        // ku-badini once ended a sentence in Kurmanji Latin mid-paragraph.
        $allowed = ['QR', 'code', 'BioNova', 'uor', 'edu', 'krd', 'Title', 'Description', 'Button'];

        foreach (['ku-sorani', 'ku-badini', 'ku-hawrami', 'ar', 'fa'] as $locale) {
            $messages = $this->load($locale, 'messages');
            $offenders = [];

            array_walk_recursive($messages, function ($value, $key) use (&$offenders, $allowed) {
                if (! is_string($value)) {
                    return;
                }

                // :date, :year and friends are placeholders, not stray words.
                $value = preg_replace('/:[a-z_]+/', '', $value);

                preg_match_all('/[A-Za-zÎîÛûÊêŞşÇç]{3,}/u', $value, $m);

                foreach ($m[0] as $word) {
                    if (! in_array($word, $allowed, true)) {
                        $offenders[] = "$key: $word";
                    }
                }
            });

            $this->assertSame([], $offenders, "Latin-script words in $locale");
        }
    }

    public function test_the_intro_reads_the_same_way_in_every_locale(): void
    {
        // Sorani is the reference: welcome, resources, what an e-library is,
        // mission. ku-badini adds a Bismillah line ahead of them.
        foreach (SetLocale::LOCALES as $locale) {
            $count = count($this->load($locale, 'messages')['intro']['paragraphs']);

            $expected = match ($locale) {
                'ku-badini' => 5,
                'ku-hawrami' => 1,   // still awaiting a translation of paragraphs 2-4
                default => 4,
            };

            $this->assertSame($expected, $count, "intro paragraph count for $locale");
        }
    }

    public function test_the_admin_panel_has_a_kurdish_sorani_translation_for_every_key(): void
    {
        $reference = $this->keyPaths($this->load('ku-sorani', 'admin'));
        sort($reference);

        // Locales without their own admin.php fall back to ku-sorani; the ones
        // that do have a file must be complete.
        foreach (SetLocale::LOCALES as $locale) {
            if (! is_file(lang_path("$locale/admin.php"))) {
                continue;
            }

            $paths = $this->keyPaths($this->load($locale, 'admin'));
            sort($paths);

            $this->assertSame($reference, $paths, "admin.php for $locale does not match ku-sorani");
        }
    }

    public function test_the_admin_fallback_locale_is_kurdish_sorani(): void
    {
        $this->assertSame('ku-sorani', config('app.fallback_locale'));
    }
}
