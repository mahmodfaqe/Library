<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Category;
use App\Support\Asset;
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

    public function test_the_qr_code_is_linked_from_the_footer_of_every_page(): void
    {
        // The URL comes from config, so set it here rather than depending on
        // whatever happens to be in .env.
        config(['library.qr_url' => 'https://example.test/qr']);

        foreach (['/en', '/en/books', '/en/privacy'] as $page) {
            $this->get($page)
                ->assertSee('https://example.test/qr', false)
                ->assertSee('>'.__('messages.qr_label', [], 'en').'</a>', false);
        }
    }

    public function test_the_footer_shows_only_the_contact_lines_that_are_set(): void
    {
        config([
            'library.contact.phone' => '',
            'library.contact.email' => 'library@example.test',
        ]);

        $page = $this->get('/en');

        $page->assertSee(__('messages.footer.address', [], 'en'))
            ->assertSee('mailto:library@example.test', false)
            // The phone was left empty, so no tile for it and no bare 'tel:'.
            ->assertDontSee('tel:', false);
    }

    public function test_the_address_is_in_the_readers_own_language(): void
    {
        // As on uor.edu.krd, where the Kurdish page gives the address in
        // Kurdish rather than the English one on every page.
        config(['library.contact.phone' => '', 'library.contact.email' => '']);

        foreach (Locale::SUPPORTED as $locale) {
            $address = __('messages.footer.address', [], $locale);
            $this->assertNotSame('messages.footer.address', $address, $locale);

            $html = $this->get($locale === Locale::DEFAULT ? '/' : '/'.$locale)->getContent();
            $this->assertStringContainsString(e($address), $html, $locale);
            $this->assertSame(1, substr_count($html, 'footer-info__item'), $locale);
        }
    }

    public function test_the_footer_offers_this_page_in_every_language(): void
    {
        $html = $this->get('/en/books')->assertOk()->getContent();

        foreach (Locale::SUPPORTED as $locale) {
            $href = url($locale === Locale::DEFAULT ? 'books' : $locale.'/books');
            $this->assertStringContainsString('<li><a href="'.$href.'" hreflang="'.Locale::languageTag($locale).'"', $html, $locale);
        }

        $this->assertSame(1, preg_match_all('/aria-current="true"\s*>English<\/a>/', $html));
    }

    public function test_the_hills_above_the_footer_cover_nothing(): void
    {
        // The stylesheet gives the last block on the page room for the hills.
        // A padding in that block's own style attribute wins over it, and the
        // hills then sit on its last row.
        $category = Category::create(['name' => 'Biology', 'sort_order' => 1]);
        $book = Book::create(['title' => 'A book', 'url' => 'https://example.test/x', 'category_id' => $category->id]);

        foreach (['/', '/books', '/books/'.$book->id, '/theses', '/privacy', '/no-such-page'] as $uri) {
            $dom = new \DOMDocument;
            libxml_use_internal_errors(true);
            $dom->loadHTML($this->get($uri)->getContent());
            libxml_clear_errors();

            $last = $dom->getElementById('main')?->lastElementChild;
            $this->assertNotNull($last, $uri);
            $this->assertDoesNotMatchRegularExpression('/padding(-bottom|-block(-end)?)?\s*:/', $last->getAttribute('style'), $uri);
        }
    }

    public function test_nothing_links_nowhere_when_the_urls_are_not_configured_yet(): void
    {
        // A fresh install ships with these blank; the page must not render
        // buttons and links that go to href="".
        config([
            'library.qr_url' => '',
            'library.drive.main' => '',
            'library.drive.secondary' => '',
        ]);

        $html = $this->get('/en')->assertOk()->getContent();

        $this->assertStringNotContainsString('href=""', $html);
    }

    public function test_the_home_page_sends_visitors_to_the_catalogue(): void
    {
        // Visitors go to the catalogue rather than the raw Drive folders: it
        // is searchable and does not expose the storage account.
        config([
            'library.drive.main' => 'https://drive.example.test/main',
            'library.drive.secondary' => 'https://drive.example.test/second',
        ]);

        $this->get('/en')
            ->assertSee(url('en/books'), false)
            ->assertDontSee('https://drive.example.test/main', false)
            ->assertDontSee('https://drive.example.test/second', false);
    }

    public function test_it_lists_the_subjects_in_order(): void
    {
        // The College of Science's own subjects lead; the rest follow.
        Category::create(['name' => 'کیمیا', 'icon' => '⚗️', 'sort_order' => 2]);
        Category::create(['name' => 'بایۆلۆجی', 'icon' => '🧬', 'sort_order' => 1]);

        $html = $this->get('/en')->assertOk()->getContent();

        $this->assertLessThan(mb_strpos($html, 'کیمیا'), mb_strpos($html, 'بایۆلۆجی'));

        // A subject card is its name and the size of its shelf, nothing more:
        // the university's own cards carry no icon, and neither do these.
        $this->assertStringNotContainsString('🧬', $html);
        $this->assertStringNotContainsString('⚗️', $html);
    }

    public function test_each_subject_links_into_its_shelf(): void
    {
        $biology = Category::create(['name' => 'بایۆلۆجی', 'icon' => '🧬', 'sort_order' => 1]);
        Book::create(['title' => 'A book', 'url' => 'https://example.test/x', 'category_id' => $biology->id]);

        $this->get('/en')
            ->assertSee(url('en/books').'?category='.$biology->id, false)
            ->assertSee(trans_choice('books.results', 1, ['count' => 1], 'en'));
    }

    public function test_it_shows_the_empty_state_when_there_are_no_subjects(): void
    {
        $this->get('/en')->assertSee(__('messages.no_departments', [], 'en'));
    }

    public function test_every_page_carries_the_site_icons(): void
    {
        foreach (['/', '/en', '/admin/login'] as $uri) {
            $this->get($uri)
                // Versioned so a replaced icon actually reaches the browser.
                ->assertSee('href="'.Asset::versioned('uor-icon.ico').'"', false)
                ->assertSee('href="'.Asset::versioned('uor-icon-96.png').'"', false)
                ->assertSee('rel="apple-touch-icon"', false);
        }

        foreach (['uor-icon.ico', 'uor-icon-96.png', 'uor-apple-icon.png', 'favicon.ico'] as $asset) {
            $path = public_path($asset);
            $this->assertFileExists($path);
            $this->assertGreaterThan(0, filesize($path), "$asset is empty");
        }
    }

    public function test_the_page_loads_its_stylesheet_from_the_build(): void
    {
        // The stylesheet is built ahead of time, not compiled in the browser.
        $this->get('/')
            ->assertSee('/build/assets/', false)
            ->assertDontSee('cdn.tailwindcss.com', false);
    }
}
