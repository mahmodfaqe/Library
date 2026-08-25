<?php

namespace Tests\Feature;

use App\Http\Middleware\SetLocale;
use App\Support\Locale;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class TranslationFilesTest extends TestCase
{
    use RefreshDatabase;

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

    /**
     * Every key the code asks for must answer, in every language.
     *
     * The other tests here compare the languages against one another, so a key
     * that is missing from all eight equally passes them all — and the page
     * then shows the reader the key itself, "admin.books.table.publisher".
     * This one reads the code instead, which is the only way to catch that.
     */
    public function test_every_key_the_code_asks_for_exists(): void
    {
        $missing = [];

        foreach ($this->keysUsedInCode() as $key => $where) {
            foreach (Locale::SUPPORTED as $locale) {
                // The third argument turns off the fallback. Without it a key
                // present only in Kurdish answers for all eight, and the
                // English page quietly shows Kurdish.
                if (! Lang::has($key, $locale, false)) {
                    $missing[] = "{$key} (used in {$where}) is missing from {$locale}";
                }
            }
        }

        $this->assertSame([], $missing, implode("\n", array_slice($missing, 0, 20)));
    }

    /**
     * Every literal translation key in the project, and one place it is used.
     *
     * Keys built at run time — __('books.languages.'.$locale) — cannot be read
     * this way and are left to the tests that compare the files.
     *
     * @return array<string, string>
     */
    private function keysUsedInCode(): array
    {
        $files = array_merge(
            $this->phpFilesIn(resource_path('views')),
            $this->phpFilesIn(app_path()),
            $this->phpFilesIn(base_path('routes')),
        );

        $keys = [];

        foreach ($files as $file) {
            $code = (string) file_get_contents($file);

            preg_match_all("/(?:__|trans_choice)\(\s*'([a-z][a-zA-Z0-9_.]*)'/", $code, $found);

            foreach ($found[1] as $key) {
                // A trailing dot means the key was completed at run time.
                if (str_ends_with($key, '.') || ! str_contains($key, '.')) {
                    continue;
                }

                $keys[$key] ??= str_replace(base_path().'/', '', $file);
            }
        }

        return $keys;
    }

    /**
     * @return list<string>
     */
    private function phpFilesIn(string $directory): array
    {
        $files = [];

        $walk = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory));

        foreach ($walk as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $files[] = $file->getPathname();
            }
        }

        return $files;
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

    public function test_every_file_exists_in_every_language(): void
    {
        // A missing file does not fail loudly: Laravel falls back, and the
        // page renders in the wrong language without anybody noticing.
        foreach (['messages', 'books', 'privacy', 'admin', 'validation', 'errors'] as $group) {
            foreach (SetLocale::LOCALES as $locale) {
                $this->assertFileExists(
                    lang_path("{$locale}/{$group}.php"),
                    "{$group}.php is missing for {$locale}"
                );
            }
        }
    }

    public function test_every_key_is_translated_into_every_language(): void
    {
        foreach (['messages', 'books', 'privacy', 'admin', 'validation', 'errors'] as $group) {
            $base = $this->namedKeys($this->load('en', $group));

            foreach (SetLocale::LOCALES as $locale) {
                $keys = $this->namedKeys($this->load($locale, $group));

                $this->assertSame([], array_values(array_diff($base, $keys)),
                    "{$locale}/{$group}.php is missing keys");
                $this->assertSame([], array_values(array_diff($keys, $base)),
                    "{$locale}/{$group}.php has keys nothing else does");
            }
        }
    }

    public function test_a_form_error_is_a_sentence_in_every_language(): void
    {
        // The fallback locale is Kurdish, so Laravel's own English validation
        // messages are never reached: without lang/*/validation.php a form
        // error shows the raw key, "validation.required".
        foreach (SetLocale::LOCALES as $locale) {
            $this->app->setLocale($locale);

            $errors = Validator::make(['title' => ''], ['title' => ['required']])
                ->errors()
                ->all();

            $this->assertCount(1, $errors, $locale);
            $this->assertStringNotContainsString('validation.', $errors[0], $locale);
            $this->assertStringNotContainsString(':attribute', $errors[0], $locale);
        }
    }

    public function test_the_page_not_found_page_speaks_the_language_of_its_address(): void
    {
        // Nothing routes a 404, so the locale middleware has to run outside the
        // route group for this to hold.
        foreach (SetLocale::LOCALES as $locale) {
            $prefix = $locale === Locale::DEFAULT ? '' : "/{$locale}";

            $this->get("{$prefix}/no-such-page")
                ->assertNotFound()
                ->assertSee(__('errors.404.title', [], $locale))
                ->assertSee('dir="'.Locale::dir($locale).'"', false);
        }
    }

    /**
     * Keys, less the positions of lists: a language may legitimately break the
     * introduction into a different number of paragraphs.
     *
     * @param  array<string, mixed>  $translations
     * @return list<string>
     */
    private function namedKeys(array $translations): array
    {
        $flat = function (array $values, string $prefix = '') use (&$flat): array {
            $keys = [];

            foreach ($values as $key => $value) {
                $path = $prefix === '' ? (string) $key : "{$prefix}.{$key}";
                $keys = array_merge($keys, is_array($value) ? $flat($value, $path) : [$path]);
            }

            return $keys;
        };

        return array_values(array_filter(
            $flat($translations),
            fn (string $key) => ! preg_match('/\.\d+$/', $key)
        ));
    }

    public function test_the_placeholders_the_views_rely_on_are_present(): void
    {
        foreach (SetLocale::LOCALES as $locale) {
            $messages = $this->load($locale, 'messages');

            $this->assertStringContainsString(':year', $messages['footer']['copyright'], $locale);
            $this->assertStringContainsString(':link', $messages['footer']['uor_line'], $locale);

            // The QR code is linked from the shared footer, so every locale
            // needs a label for it.
            $this->assertNotEmpty($messages['qr_label'], $locale);

            $this->assertCount(1, array_filter(
                $messages['history']['paragraphs'],
                fn (string $p) => str_contains($p, ':date')
            ), "$locale should have exactly one history paragraph with a :date placeholder");
        }
    }

    public function test_the_public_page_carries_no_personal_contact_details(): void
    {
        // An official university page cannot link to a student's personal
        // accounts, and must not publish anyone's phone number.
        $html = $this->get('/')->assertOk()->getContent();

        foreach (['snapchat.com', 'tiktok.com', 'facebook.com', 't.me/', 'wa.me/', 'tel:+'] as $trace) {
            $this->assertStringNotContainsString($trace, $html);
        }
    }

    public function test_every_locale_lists_the_same_people(): void
    {
        // How many people are credited is for the library to decide; that
        // every language names the same number of them is not, or one would
        // silently lose somebody the others still credit.
        $expected = count($this->load('en', 'messages')['intro']['people']);

        $this->assertGreaterThan(0, $expected);

        foreach (SetLocale::LOCALES as $locale) {
            $people = $this->load($locale, 'messages')['intro']['people'];

            $this->assertCount($expected, $people, $locale);

            foreach ($people as $person) {
                $this->assertSame(['name', 'role'], array_keys($person), $locale);
            }
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
                'ku-badini' => 5,   // carries a Bismillah line ahead of the four
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
