<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Support\Locale;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    use RefreshDatabase;

    public static function locales(): array
    {
        return array_map(fn (string $l) => [$l], Locale::SUPPORTED);
    }

    #[DataProvider('locales')]
    public function test_the_home_page_renders_in_every_locale(string $locale): void
    {
        $this->get($this->homeUrl($locale))
            ->assertOk()
            ->assertSee(__('messages.hero.title', [], $locale), false)
            ->assertSee(__('messages.intro.heading', [], $locale), false);
    }

    #[DataProvider('locales')]
    public function test_no_translation_key_leaks_into_the_page(string $locale): void
    {
        $html = $this->get($this->homeUrl($locale))->getContent();

        // A missing key renders as the key itself, e.g. "messages.hero.title".
        $this->assertDoesNotMatchRegularExpression('/\bmessages\.[a-z_]+/', $html);
        $this->assertStringNotContainsString('admin.', $html);
    }

    #[DataProvider('locales')]
    public function test_every_page_advertises_all_its_translations(string $locale): void
    {
        $response = $this->get($this->homeUrl($locale))->assertOk();

        foreach (Locale::SUPPORTED as $alternate) {
            $response->assertSee(
                '<link rel="alternate" hreflang="'.Locale::languageTag($alternate).'" href="'.url($this->homeUrl($alternate) === '/' ? '/' : $alternate).'">',
                false
            );
        }

        $response->assertSee('hreflang="x-default"', false);
    }

    #[DataProvider('locales')]
    public function test_each_locale_declares_its_own_canonical_url(string $locale): void
    {
        $expected = $locale === Locale::DEFAULT ? url('/') : url($locale);

        $this->get($this->homeUrl($locale))
            ->assertSee('<link rel="canonical" href="'.$expected.'">', false)
            ->assertSee('<meta property="og:url" content="'.$expected.'">', false)
            ->assertSee('<meta property="og:locale" content="'.Locale::languageTag($locale).'">', false);
    }

    public function test_the_site_root_is_kurdish_sorani(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee(__('messages.hero.title', [], 'ku-sorani'), false)
            ->assertSee('<html lang="ku" dir="rtl"', false);
    }

    public function test_the_root_stays_the_default_locale_whatever_the_visitor_picked_before(): void
    {
        // Otherwise the root would serve eight different pages at one URL.
        $this->withSession(['locale' => 'en'])
            ->get('/')
            ->assertSee(__('messages.hero.title', [], 'ku-sorani'), false);
    }

    public function test_the_prefixed_default_locale_redirects_to_the_root(): void
    {
        $this->get('/ku-sorani')->assertRedirect('/')->assertStatus(301);
    }

    public function test_an_unknown_locale_prefix_is_not_a_page(): void
    {
        $this->get('/de')->assertNotFound();
        $this->get('/ku-zazaki')->assertNotFound();
    }

    public function test_latin_locales_render_left_to_right(): void
    {
        $this->get('/en')
            ->assertSee('<html lang="en" dir="ltr"', false)
            ->assertSee('footer-year-en', false);
    }

    public function test_the_opening_date_is_emphasised_inside_the_history_paragraph(): void
    {
        $this->get('/en')
            ->assertSee('<strong>'.__('messages.history.opening_date', [], 'en').'</strong>', false);
    }

    public function test_the_qr_objective_renders_as_a_link(): void
    {
        $this->get('/en')
            ->assertSee('https://scence-bio.github.io/Qr-Code/', false)
            ->assertSee('>'.__('messages.intro.qr_label', [], 'en').'</a>', false);
    }

    public function test_it_lists_the_departments_in_sort_order(): void
    {
        Department::create([
            'sort_order' => 2,
            'icon' => '🧪',
            'drive_url' => 'https://drive.google.com/drive/folders/second',
            'translations' => ['en' => ['title' => 'Chemistry', 'desc' => 'Chem desc', 'button' => 'Open']],
        ]);
        Department::create([
            'sort_order' => 1,
            'icon' => '🔭',
            'drive_url' => 'https://drive.google.com/drive/folders/first',
            'translations' => ['en' => ['title' => 'Astrophysics', 'desc' => 'Astro desc', 'button' => 'Open']],
        ]);

        $html = $this->get('/en')->assertOk()->getContent();

        $this->assertLessThan(strpos($html, 'Chemistry'), strpos($html, 'Astrophysics'));
        $this->assertStringContainsString('https://drive.google.com/drive/folders/first', $html);
    }

    public function test_it_shows_the_empty_state_when_there_are_no_departments(): void
    {
        $this->get('/en')->assertSee(__('messages.no_departments', [], 'en'));
    }

    public function test_the_page_loads_its_stylesheet_from_the_build(): void
    {
        // The Tailwind CDN used to compile the stylesheet in the browser.
        $this->get('/')
            ->assertSee('/build/assets/', false)
            ->assertDontSee('cdn.tailwindcss.com', false);
    }
}
