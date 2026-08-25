<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\User;
use App\Support\OpenLibrary;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class BookRecordTest extends TestCase
{
    use RefreshDatabase;

    private function book(array $attributes = []): Book
    {
        return Book::create(array_merge([
            'title' => 'Fungal Biology',
            'language' => 'English',
            'url' => 'https://drive.test/'.uniqid(),
        ], $attributes));
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

    private function librarian(): User
    {
        return User::create([
            'name' => 'Library Staff',
            'email' => 'staff@uor.edu.krd',
            'password' => 'correct-horse-battery-staple',
            'role' => User::ROLE_STAFF,
        ]);
    }

    /**
     * One OpenLibrary answer, in the shape their search endpoint returns.
     */
    private function openLibraryAnswers(array $doc = []): void
    {
        Http::fake(['openlibrary.org/*' => Http::response(['docs' => [array_merge([
            'title' => 'Fungal Biology',
            'author_name' => ['J. W. Deacon'],
            'first_publish_year' => 1980,
            'publisher' => ['Blackwell'],
            'isbn' => ['9781405130660'],
            'number_of_pages_median' => 384,
            'subject' => ['Mycology', 'Fungi'],
        ], $doc)]])]);
    }

    // ── The record a catalogue needs ────────────────────────────────────

    public function test_a_book_page_shows_the_whole_record(): void
    {
        $book = $this->book([
            'author' => 'J. W. Deacon',
            'year' => 2006,
            'publisher' => 'Blackwell',
            'isbn' => '9781405130660',
            'edition' => '4th ed.',
            'pages' => 371,
            'abstract' => 'An introduction to the biology of fungi.',
            'keywords' => 'Mycology, Fungi',
        ]);

        $this->get("/en/books/{$book->id}")
            ->assertOk()
            ->assertSee('Blackwell')
            ->assertSee('4th ed.')
            ->assertSee('371')
            ->assertSee('An introduction to the biology of fungi.')
            ->assertSee('Mycology');
    }

    public function test_a_citation_names_the_publisher_of_the_book(): void
    {
        // Not the library: a bibliography wants whoever printed it.
        $book = $this->book([
            'author' => 'J. W. Deacon',
            'year' => 2006,
            'publisher' => 'Blackwell',
            'edition' => '4th ed.',
        ]);

        $html = $this->get("/en/books/{$book->id}")->assertOk()->getContent();

        $this->assertStringContainsString('J. W. Deacon (2006)', $html);
        $this->assertStringContainsString('Blackwell', $html);
        $this->assertStringContainsString('4th ed.', $html);
    }

    public function test_the_library_stands_in_only_when_no_publisher_is_known(): void
    {
        $book = $this->book(['author' => 'J. W. Deacon', 'year' => 2006]);

        $html = $this->get("/en/books/{$book->id}")->assertOk()->getContent();

        $this->assertStringContainsString(__('messages.university_name', [], 'en'), $html);
    }

    public function test_an_isbn_is_one_number_however_it_was_typed(): void
    {
        $book = $this->book(['isbn' => '9781405130660']);

        // Stored bare, shown the way a reader would write it down.
        $this->assertSame('978-1-4051-3066-0', $book->isbnForDisplay());
    }

    public function test_the_admin_form_stores_an_isbn_without_its_hyphens(): void
    {
        $book = $this->book();

        $this->actingAs($this->librarian())
            ->put("/admin/books/{$book->id}", [
                'title' => $book->title,
                'url' => $book->url,
                'isbn' => '978-1-4051-3066-0',
                'publisher' => 'Blackwell',
                'pages' => 371,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'isbn' => '9781405130660',
            'publisher' => 'Blackwell',
            'pages' => 371,
            'metadata_source' => 'librarian',
        ]);
    }

    public function test_the_edit_form_offers_every_field_with_a_real_label(): void
    {
        $book = $this->book();

        $html = $this->actingAs($this->librarian())
            ->get("/admin/books/{$book->id}/edit")
            ->assertOk()
            ->getContent();

        foreach (['publisher', 'isbn', 'edition', 'pages', 'abstract', 'keywords'] as $field) {
            $this->assertStringContainsString('name="'.$field.'"', $html);

            // Not the key itself: a label reading "admin.books.table.publisher"
            // is what a missing translation looks like on the page.
            $this->assertStringContainsString(__('admin.books.table.'.$field), $html);
            $this->assertStringNotContainsString('admin.books.table.'.$field, $html);
        }
    }

    public function test_keywords_split_on_whichever_comma_was_typed(): void
    {
        // A librarian writing Arabic script reaches for ، not ,
        $book = $this->book(['keywords' => 'بایۆلۆجی، زانست؛ کیمیا, fungi']);

        $this->assertSame(
            ['بایۆلۆجی', 'زانست', 'کیمیا', 'fungi'],
            $book->keywordList()
        );
    }

    public function test_a_keyword_searches_the_catalogue_for_itself(): void
    {
        $book = $this->book(['keywords' => 'Mycology']);

        $this->get("/en/books/{$book->id}")
            ->assertOk()
            ->assertSee('?q=Mycology', false);
    }

    public function test_the_search_looks_through_keywords_and_publisher(): void
    {
        // A reader looking for "mycology" wants this book, and its title does
        // not say so.
        $this->book(['title' => 'Fungal Biology', 'keywords' => 'Mycology, Fungi']);
        $this->book(['title' => 'Organic Chemistry', 'publisher' => 'Garland Science']);

        $this->get('/en/books?q=mycology')->assertOk()->assertSee('Fungal Biology');
        $this->get('/en/books?q=garland')->assertOk()->assertSee('Organic Chemistry');
    }

    public function test_the_structured_data_carries_the_record(): void
    {
        $book = $this->book([
            'publisher' => 'Blackwell',
            'isbn' => '9781405130660',
            'pages' => 371,
            'edition' => '4th ed.',
        ]);

        $html = $this->get("/en/books/{$book->id}")->assertOk()->getContent();
        preg_match('/<script type="application\/ld\+json">(.*?)<\/script>/s', $html, $found);
        $schema = json_decode($found[1] ?? '', true);

        $this->assertSame('9781405130660', $schema['isbn'] ?? null);
        $this->assertSame(371, $schema['numberOfPages'] ?? null);
        $this->assertSame('4th ed.', $schema['bookEdition'] ?? null);
        // The publisher is whoever printed it; the library is the provider.
        $this->assertSame('Blackwell', $schema['publisher']['name'] ?? null);
        $this->assertStringContainsString('Raparin', $schema['provider']['name'] ?? '');
    }

    // ── The identifier the publisher gave it ────────────────────────────

    public function test_a_doi_is_stored_bare_however_it_was_typed(): void
    {
        $book = $this->book();
        $librarian = $this->librarian();

        foreach ([
            'https://doi.org/10.1002/9781118685068',
            'http://dx.doi.org/10.1002/9781118685068',
            '10.1002/9781118685068',
        ] as $written) {
            $this->actingAs($librarian)
                ->put("/admin/books/{$book->id}", [
                    'title' => $book->title,
                    'url' => $book->url,
                    'doi' => $written,
                ])
                ->assertRedirect();

            // One book, one identifier, whichever way a librarian wrote it.
            $this->assertSame('10.1002/9781118685068', $book->fresh()->doi);
        }
    }

    public function test_something_that_is_not_a_doi_is_refused(): void
    {
        $book = $this->book();

        $this->actingAs($this->librarian())
            ->from("/admin/books/{$book->id}/edit")
            ->put("/admin/books/{$book->id}", [
                'title' => $book->title,
                'url' => $book->url,
                'doi' => 'see the back cover',
            ])
            ->assertSessionHasErrors('doi');
    }

    public function test_a_citation_prefers_the_doi_to_a_link_to_us(): void
    {
        // Every style asks for the DOI where there is one: it is the address
        // the publisher guarantees, not a link to whichever library the reader
        // happened to use.
        $book = $this->book(['author' => 'J. W. Deacon', 'year' => 2006, 'doi' => '10.1002/9781118685068']);

        $apa = $this->citation(
            $this->get("/en/books/{$book->id}")->assertOk()->getContent(),
            'APA'
        );

        $this->assertStringContainsString('https://doi.org/10.1002/9781118685068', $apa);
        $this->assertStringNotContainsString('/en/books/'.$book->id, $apa);
    }

    public function test_without_a_doi_the_citation_points_here(): void
    {
        $book = $this->book(['author' => 'J. W. Deacon', 'year' => 2006]);

        $apa = $this->citation(
            $this->get("/en/books/{$book->id}")->assertOk()->getContent(),
            'APA'
        );

        $this->assertStringContainsString('/en/books/'.$book->id, $apa);
    }

    public function test_the_doi_is_a_link_a_reader_can_follow(): void
    {
        $book = $this->book(['doi' => '10.1002/9781118685068']);

        $this->get("/en/books/{$book->id}")
            ->assertOk()
            ->assertSee('https://doi.org/10.1002/9781118685068', false)
            ->assertSee('name="citation_doi" content="10.1002/9781118685068"', false);
    }

    public function test_the_exports_carry_the_doi(): void
    {
        $book = $this->book(['author' => 'J. W. Deacon', 'doi' => '10.1002/9781118685068']);

        $bib = $this->get("/books/{$book->id}/cite.bib")->assertOk()->getContent();
        $ris = $this->get("/books/{$book->id}/cite.ris")->assertOk()->getContent();

        $this->assertStringContainsString('doi        = {10.1002/9781118685068},', $bib);
        $this->assertStringContainsString("DO  - 10.1002/9781118685068\r\n", $ris);
    }

    // ── Handing a book to a reference manager ───────────────────────────

    public function test_a_book_downloads_as_bibtex(): void
    {
        $book = $this->book([
            'author' => 'J. W. Deacon',
            'year' => 2006,
            'publisher' => 'Blackwell',
            'isbn' => '9781405130660',
        ]);

        $response = $this->get("/books/{$book->id}/cite.bib")->assertOk();

        $this->assertStringContainsString('application/x-bibtex', $response->headers->get('Content-Type'));
        $this->assertStringContainsString(
            'filename="fungal-biology.bib"',
            $response->headers->get('Content-Disposition')
        );

        $bib = $response->getContent();
        // The key a reader will see in their own bibliography.
        $this->assertStringContainsString('@book{deacon2006fungal,', $bib);
        $this->assertStringContainsString('author     = {J. W. Deacon},', $bib);
        $this->assertStringContainsString('publisher  = {Blackwell},', $bib);
    }

    public function test_a_book_downloads_as_ris(): void
    {
        $book = $this->book(['author' => 'J. W. Deacon', 'year' => 2006, 'keywords' => 'Mycology, Fungi']);

        $ris = $this->get("/books/{$book->id}/cite.ris")->assertOk()->getContent();

        // RIS is line-oriented and still read that way: CRLF, "TAG  - value".
        $this->assertStringStartsWith("TY  - BOOK\r\n", $ris);
        $this->assertStringContainsString("AU  - J. W. Deacon\r\n", $ris);
        $this->assertStringContainsString("KW  - Mycology\r\n", $ris);
        $this->assertStringContainsString("KW  - Fungi\r\n", $ris);
        $this->assertStringEndsWith("ER  - \r\n", $ris);
    }

    public function test_a_format_the_library_does_not_write_is_a_404(): void
    {
        $book = $this->book();

        $this->get("/books/{$book->id}/cite.docx")->assertNotFound();
    }

    public function test_three_authors_are_three_people(): void
    {
        $book = $this->book(['author' => 'Bruce Alberts, Alexander Johnson, Julian Lewis']);

        $ris = $this->get("/books/{$book->id}/cite.ris")->assertOk()->getContent();

        $this->assertSame(3, substr_count($ris, 'AU  - '));
        $this->assertStringContainsString('AU  - Alexander Johnson', $ris);
    }

    public function test_one_author_with_initials_is_still_one_person(): void
    {
        // "Deacon, J. W." is a comma inside a name, not between two.
        $book = $this->book(['author' => 'Deacon, J. W.']);

        $ris = $this->get("/books/{$book->id}/cite.ris")->assertOk()->getContent();

        $this->assertSame(1, substr_count($ris, 'AU  - '));
        $this->assertStringContainsString('AU  - Deacon, J. W.', $ris);
    }

    public function test_bibtex_survives_a_title_full_of_its_own_syntax(): void
    {
        $book = $this->book(['title' => 'Cost & Value: 50% {of} $it_all#']);

        $bib = $this->get("/books/{$book->id}/cite.bib")->assertOk()->getContent();

        // Unescaped, any one of these would break the reader's bibliography.
        $this->assertStringContainsString('\&', $bib);
        $this->assertStringContainsString('\%', $bib);
        $this->assertStringContainsString('\{of\}', $bib);
        $this->assertStringContainsString('\$it\_all\#', $bib);
    }

    public function test_the_page_tells_zotero_what_the_book_is(): void
    {
        $book = $this->book([
            'author' => 'Bruce Alberts, Alexander Johnson',
            'year' => 2015,
            'publisher' => 'Garland Science',
            'isbn' => '9780815344322',
            'keywords' => 'Cell biology',
        ]);

        $html = $this->get("/en/books/{$book->id}")->assertOk()->getContent();

        // Google Scholar will not index a repository without these.
        $this->assertStringContainsString('name="citation_title" content="Fungal Biology"', $html);
        $this->assertSame(2, substr_count($html, 'name="citation_author"'));
        $this->assertStringContainsString('name="citation_publication_date" content="2015"', $html);
        $this->assertStringContainsString('name="citation_publisher" content="Garland Science"', $html);
        $this->assertStringContainsString('name="citation_isbn" content="9780815344322"', $html);
        $this->assertStringContainsString('name="citation_keywords" content="Cell biology"', $html);
    }

    public function test_it_does_not_claim_a_pdf_it_does_not_serve(): void
    {
        // A Drive link is a page about a file, not the file. Naming it as
        // citation_pdf_url would tell Scholar something untrue.
        $book = $this->book(['url' => 'https://drive.google.com/file/d/abc/view']);

        $this->get("/en/books/{$book->id}")
            ->assertOk()
            ->assertDontSee('citation_pdf_url', false);
    }

    public function test_the_page_offers_both_files(): void
    {
        $book = $this->book();

        $this->get("/en/books/{$book->id}")
            ->assertOk()
            ->assertSee('BibTeX')
            ->assertSee('RIS')
            ->assertSee(__('books.book.cite_download', [], 'en'));
    }

    // ── Looking the catalogue up ────────────────────────────────────────

    public function test_it_fills_in_an_author_it_does_not_have(): void
    {
        $this->openLibraryAnswers();
        $book = $this->book();

        $this->artisan('books:look-up --apply')->assertSuccessful();

        $this->assertSame('J. W. Deacon', $book->fresh()->author);
        $this->assertSame('openlibrary', $book->fresh()->metadata_source);
    }

    public function test_it_does_not_write_the_year_publisher_or_isbn(): void
    {
        // A title search answers for the work, not the edition on the shelf:
        // it offers 1980 and Blackwell for a book the library may hold the
        // 2006 printing of. A wrong citation is worse than a thin one.
        $this->openLibraryAnswers();
        $book = $this->book();

        $this->artisan('books:look-up --apply')->assertSuccessful();

        $fresh = $book->fresh();
        $this->assertNull($fresh->year);
        $this->assertNull($fresh->publisher);
        $this->assertNull($fresh->isbn);
    }

    public function test_it_never_overwrites_what_a_librarian_typed(): void
    {
        $this->openLibraryAnswers();
        $book = $this->book(['author' => 'Deacon, J.W.']);

        $this->artisan('books:look-up --apply')->assertSuccessful();

        $this->assertSame('Deacon, J.W.', $book->fresh()->author);
    }

    public function test_it_writes_nothing_without_being_asked(): void
    {
        $this->openLibraryAnswers();
        $book = $this->book();

        $this->artisan('books:look-up')->assertSuccessful();

        $this->assertNull($book->fresh()->author);
    }

    public function test_a_title_that_is_not_the_same_book_is_refused(): void
    {
        // Half the books ever printed are called Biology. A near miss on a
        // title is not a match.
        $this->openLibraryAnswers(['title' => 'Marine Biology of the Pacific']);
        $book = $this->book(['title' => 'Fungal Biology']);

        $this->artisan('books:look-up --apply')->assertSuccessful();

        $this->assertNull($book->fresh()->author);
    }

    public function test_a_subtitle_is_still_the_same_book(): void
    {
        $this->openLibraryAnswers(['title' => 'Fungal Biology: Fourth Edition']);
        $book = $this->book(['title' => 'Fungal Biology']);

        $this->artisan('books:look-up --apply')->assertSuccessful();

        $this->assertSame('J. W. Deacon', $book->fresh()->author);
    }

    public function test_a_drive_file_name_still_matches_its_book(): void
    {
        // The catalogue's titles came from Drive: "Fungal-biology.pdf".
        $this->assertGreaterThanOrEqual(
            0.9,
            OpenLibrary::alike('Fungal-biology.pdf', 'Fungal Biology')
        );
        $this->assertGreaterThanOrEqual(
            0.9,
            OpenLibrary::alike('Molecular_Biology_of_the_Cell', 'Molecular biology of the cell')
        );
    }

    public function test_a_book_is_not_looked_up_twice(): void
    {
        $this->openLibraryAnswers();
        $this->book();

        $this->artisan('books:look-up --apply');
        Http::assertSentCount(1);

        // The answer will not have changed, and the run is long enough already.
        $this->artisan('books:look-up --apply');
        Http::assertSentCount(1);
    }

    public function test_openlibrary_being_down_does_not_break_the_run(): void
    {
        Http::fake(['openlibrary.org/*' => fn () => throw new \RuntimeException('unreachable')]);
        $book = $this->book();

        $this->artisan('books:look-up --apply')->assertSuccessful();

        $this->assertNull($book->fresh()->author);
    }
}
