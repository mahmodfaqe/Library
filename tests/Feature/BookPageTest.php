<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Category;
use App\Support\Locale;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookPageTest extends TestCase
{
    use RefreshDatabase;

    private function book(array $attributes = []): Book
    {
        return Book::create(array_merge([
            'title' => 'Molecular Biology of the Cell',
            'author' => 'Bruce Alberts',
            'year' => 2015,
            'language' => 'English',
            'url' => 'https://drive.test/'.uniqid(),
        ], $attributes));
    }

    public function test_every_book_has_a_page_of_its_own(): void
    {
        $book = $this->book();

        $this->get("/books/{$book->id}")
            ->assertOk()
            ->assertSee('Molecular Biology of the Cell')
            ->assertSee('Bruce Alberts')
            ->assertSee('2015');
    }

    public function test_the_address_survives_a_corrected_title(): void
    {
        // It is keyed by id, because it is what a reader cites.
        $book = $this->book();
        $address = "/books/{$book->id}";

        $book->update(['title' => 'Cell Biology, 6th edition']);

        $this->get($address)
            ->assertOk()
            ->assertSee('Cell Biology, 6th edition');
    }

    public function test_the_page_exists_in_every_language(): void
    {
        $book = $this->book();

        foreach (Locale::SUPPORTED as $locale) {
            $prefix = $locale === Locale::DEFAULT ? '' : "/{$locale}";

            $this->get("{$prefix}/books/{$book->id}")
                ->assertOk()
                ->assertSee(__('books.book.cite', [], $locale))
                ->assertSee('dir="'.Locale::dir($locale).'"', false);
        }
    }

    public function test_switching_language_stays_on_the_same_book(): void
    {
        $book = $this->book();

        $this->get("/en/books/{$book->id}")
            ->assertOk()
            // The switcher and the hreflang tags both point at this book in
            // the other language, not back at the catalogue.
            ->assertSee(url("/ar/books/{$book->id}"), false)
            ->assertSee(url("/books/{$book->id}"), false);
    }

    public function test_a_book_that_is_gone_is_a_404(): void
    {
        $this->get('/books/999999')->assertNotFound();
    }

    public function test_it_offers_the_citation_a_reader_needs(): void
    {
        $book = $this->book();

        $html = $this->get("/books/{$book->id}")->assertOk()->getContent();

        foreach (['APA', 'MLA', 'Chicago'] as $style) {
            $this->assertStringContainsString($style, $html);
        }

        // Author, year, title and the address that leads back here.
        $this->assertStringContainsString('Bruce Alberts (2015)', $html);
        $this->assertStringContainsString(Locale::bookUrl($book->id), $html);
    }

    public function test_a_book_with_no_author_still_cites_correctly(): void
    {
        $book = $this->book(['author' => null, 'year' => null]);

        $html = $this->get("/books/{$book->id}")->assertOk()->getContent();

        // The library stands in as the author, and an unknown date is written
        // the way a bibliography writes one.
        $this->assertStringContainsString('n.d.', $html);

        // But it is named once, not as author and publisher both — and in the
        // language of the book, which is the bibliography it will be pasted
        // into, not the language of the page it was read on.
        $apa = $this->citation($html, 'APA');
        $english = __('messages.university_name', [], 'en');

        $this->assertSame(1, substr_count($apa, $english), $apa);
        $this->assertStringNotContainsString(__('messages.university_name'), $apa);

        // And no style ends the date with two full stops.
        foreach (['APA', 'MLA', 'Chicago'] as $style) {
            $this->assertStringNotContainsString('..', $this->citation($html, $style));
        }
    }

    /**
     * The text of one citation, tags stripped, as a reader would read it.
     */
    private function citation(string $html, string $style): string
    {
        preg_match(
            '/<span class="cite-style">'.$style.'<\/span>\s*<p class="cite-text"[^>]*>(.*?)<\/p>/s',
            $html,
            $found
        );

        return strip_tags($found[1] ?? '');
    }

    public function test_a_title_cannot_inject_markup_into_the_citation(): void
    {
        // Titles are typed by staff and the citation is printed as raw HTML,
        // so the escaping has to happen before it gets there.
        $book = $this->book(['title' => '<script>alert(1)</script> Cell Biology']);

        $html = $this->get("/books/{$book->id}")->assertOk()->getContent();

        $this->assertStringNotContainsString('<script>alert(1)</script>', $html);
        // And the button copies the title as written, tags and all, not a
        // version with pieces eaten out of it.
        $this->assertStringContainsString('&lt;script&gt;alert(1)&lt;/script&gt; Cell Biology', $html);
    }

    public function test_it_prints_the_author_once(): void
    {
        $book = $this->book();

        $html = $this->get("/books/{$book->id}")->assertOk()->getContent();

        // Byline, citations — but not a details row repeating the byline
        // directly above it.
        $this->assertStringNotContainsString('>'.__('books.book.author').'<', $html);
    }

    public function test_a_citation_is_written_in_the_language_of_the_book(): void
    {
        // A reader browsing in Kurdish who cites an English book is pasting it
        // into an English bibliography; "زانکۆی ڕاپەڕین" in the middle of one
        // is not something they can hand in.
        $english = $this->book(['author' => null, 'language' => 'English']);
        $kurdish = $this->book(['author' => null, 'language' => 'کوردی', 'title' => 'ژیناسی']);

        $onEnglish = $this->citation(
            $this->get("/books/{$english->id}")->assertOk()->getContent(),
            'APA'
        );
        $onKurdish = $this->citation(
            $this->get("/books/{$kurdish->id}")->assertOk()->getContent(),
            'APA'
        );

        // Both read on the Kurdish pages, each naming the university its own way.
        $this->assertStringContainsString(__('messages.university_name', [], 'en'), $onEnglish);
        $this->assertStringContainsString(__('messages.university_name', [], 'ku-sorani'), $onKurdish);

        // And the book's language does not change with the page it is read on.
        $fromTurkish = $this->citation(
            $this->get("/tr/books/{$english->id}")->assertOk()->getContent(),
            'APA'
        );

        $this->assertSame($onEnglish, $fromTurkish);
    }

    public function test_a_language_the_library_has_no_word_for_is_left_alone(): void
    {
        $book = $this->book(['language' => 'Deutsch']);

        // Better the word the librarian typed than a wrong guess at it.
        $this->get("/books/{$book->id}")->assertOk()->assertSee('Deutsch');
    }

    public function test_it_describes_itself_as_a_book_to_a_search_engine(): void
    {
        $book = $this->book();

        $html = $this->get("/books/{$book->id}")->assertOk()->getContent();

        preg_match('/<script type="application\/ld\+json">(.*?)<\/script>/s', $html, $found);
        $schema = json_decode($found[1] ?? '', true);

        // Without @context the block is not structured data at all, only a
        // shape that looks like it.
        $this->assertSame('https://schema.org', $schema['@context'] ?? null);
        $this->assertSame('Book', $schema['@type'] ?? null);
        $this->assertSame('2015', $schema['datePublished'] ?? null);
        $this->assertSame('Molecular Biology of the Cell', $schema['name'] ?? null);
        $this->assertSame(Locale::bookUrl($book->id), $schema['url'] ?? null);
    }

    public function test_the_catalogue_links_to_the_page_not_the_file(): void
    {
        $biology = Category::create(['name' => 'Biology', 'sort_order' => 1]);
        $book = $this->book(['category_id' => $biology->id]);

        $this->get("/books?category={$biology->id}")
            ->assertOk()
            ->assertSee(Locale::bookUrl($book->id), false);
    }

    public function test_it_offers_other_books_on_the_same_shelf(): void
    {
        $biology = Category::create(['name' => 'Biology', 'sort_order' => 1]);
        $chemistry = Category::create(['name' => 'Chemistry', 'sort_order' => 2]);

        $book = $this->book(['category_id' => $biology->id]);
        $this->book(['title' => 'Cell structure', 'category_id' => $biology->id]);
        $this->book(['title' => 'Organic chemistry', 'category_id' => $chemistry->id]);

        $this->get("/books/{$book->id}")
            ->assertOk()
            ->assertSee('Cell structure')
            ->assertDontSee('Organic chemistry');
    }

    public function test_a_book_with_nowhere_to_go_says_so(): void
    {
        $book = $this->book(['url' => null, 'file_path' => null]);

        $this->get("/books/{$book->id}")
            ->assertOk()
            ->assertSee(__('books.book.no_link'))
            ->assertDontSee('href=""', false);
    }

    public function test_the_sitemap_index_names_one_file_per_language(): void
    {
        $index = $this->get('/sitemap.xml')->assertOk()->getContent();

        $this->assertStringContainsString('<sitemapindex', $index);

        foreach (Locale::SUPPORTED as $locale) {
            $this->assertStringContainsString(url('sitemap-'.$locale.'.xml'), $index);
        }
    }

    public function test_the_sitemap_lists_every_book(): void
    {
        $a = $this->book();
        $b = $this->book(['title' => 'Second']);

        // Without these a search engine sees one catalogue page and none of
        // the books inside it.
        foreach (Locale::SUPPORTED as $locale) {
            $xml = $this->get('/sitemap-'.$locale.'.xml')->assertOk()->getContent();

            foreach ([$a, $b] as $book) {
                $this->assertStringContainsString(Locale::bookUrl($book->id, $locale), $xml);
            }

            // And each entry offers the reader's own language as an
            // alternate, with a fallback for anyone who reads none of them.
            $this->assertStringContainsString(
                'hreflang="'.Locale::languageTag('en').'"',
                $xml
            );
            $this->assertStringContainsString('hreflang="x-default"', $xml);
        }
    }

    public function test_a_language_the_library_does_not_speak_has_no_sitemap(): void
    {
        $this->get('/sitemap-de.xml')->assertNotFound();
    }

    public function test_the_sitemap_is_not_rebuilt_for_every_crawler(): void
    {
        $this->book();

        foreach (['/sitemap.xml', '/sitemap-en.xml'] as $address) {
            $first = $this->get($address)->assertOk()->getContent();
            $second = $this->get($address)->assertOk()->getContent();

            $this->assertSame($first, $second);
        }
    }
}
