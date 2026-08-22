<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Support\PdfDetails;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class BookDetailsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The parser reads bytes, not paths: books are never stored on the server.
     */
    private function fixture(string $name): string
    {
        return file_get_contents(base_path("tests/fixtures/{$name}"));
    }

    public function test_it_reads_the_author_and_the_published_year(): void
    {
        $details = PdfDetails::read($this->fixture('book-with-details.pdf'));

        $this->assertSame('Bruce Alberts', $details['author']);
        // From "Copyright 2015" on the page, not the 2003 in /CreationDate.
        $this->assertSame(2015, $details['year']);
    }

    public function test_it_does_not_mistake_the_software_for_the_author(): void
    {
        $details = PdfDetails::read($this->fixture('book-by-software.pdf'));

        $this->assertNull($details['author']);
    }

    public function test_the_scan_date_is_never_taken_for_the_published_year(): void
    {
        // book-by-software.pdf carries a 2003 /CreationDate and says no year
        // anywhere in its text. A blank is the honest answer; 2003 would be a
        // wrong one that looks right.
        $this->assertNull(PdfDetails::read($this->fixture('book-by-software.pdf'))['year']);
    }

    public function test_an_unreadable_file_is_not_an_error(): void
    {
        $this->assertSame(
            ['author' => null, 'year' => null, 'language' => null],
            PdfDetails::read('this is not a pdf at all')
        );
    }

    public function test_the_command_fills_in_what_is_missing(): void
    {
        config(['library.google_api_key' => 'test-key']);

        Http::fake([
            'www.googleapis.com/*' => Http::response($this->fixture('book-with-details.pdf')),
        ]);

        $book = Book::create([
            'title' => 'Molecular Biology of the Cell',
            'drive_file_id' => 'abc123',
        ]);

        $this->artisan('books:extract-details')->assertSuccessful();

        $book->refresh();

        $this->assertSame('Bruce Alberts', $book->author);
        $this->assertSame(2015, $book->year);
    }

    public function test_it_never_overwrites_what_a_librarian_recorded(): void
    {
        config(['library.google_api_key' => 'test-key']);

        Http::fake([
            'www.googleapis.com/*' => Http::response($this->fixture('book-with-details.pdf')),
        ]);

        $book = Book::create([
            'title' => 'Molecular Biology of the Cell',
            'drive_file_id' => 'abc123',
            'author' => 'ب. ئالبێرتس',
        ]);

        $this->artisan('books:extract-details')->assertSuccessful();

        $book->refresh();

        // The corrected author stands; only the blank year is filled.
        $this->assertSame('ب. ئالبێرتس', $book->author);
        $this->assertSame(2015, $book->year);
    }

    public function test_force_replaces_what_is_already_there(): void
    {
        config(['library.google_api_key' => 'test-key']);

        Http::fake([
            'www.googleapis.com/*' => Http::response($this->fixture('book-with-details.pdf')),
        ]);

        $book = Book::create([
            'title' => 'Molecular Biology of the Cell',
            'drive_file_id' => 'abc123',
            'author' => 'Wrong Name',
        ]);

        $this->artisan('books:extract-details', ['--force' => true])->assertSuccessful();

        $this->assertSame('Bruce Alberts', $book->refresh()->author);
    }

    public function test_a_dry_run_writes_nothing(): void
    {
        config(['library.google_api_key' => 'test-key']);

        Http::fake([
            'www.googleapis.com/*' => Http::response($this->fixture('book-with-details.pdf')),
        ]);

        $book = Book::create(['title' => 'Molecular Biology', 'drive_file_id' => 'abc123']);

        $this->artisan('books:extract-details', ['--dry-run' => true])->assertSuccessful();

        $book->refresh();

        $this->assertNull($book->author);
        $this->assertNull($book->year);
    }

    public function test_no_book_is_ever_written_to_disk(): void
    {
        config(['library.google_api_key' => 'test-key']);

        Http::fake([
            'www.googleapis.com/*' => Http::response($this->fixture('book-with-details.pdf')),
        ]);

        Book::create(['title' => 'Molecular Biology', 'drive_file_id' => 'abc123']);

        $before = count(glob(sys_get_temp_dir().'/*'));
        $this->artisan('books:extract-details')->assertSuccessful();

        // The server is nearly full and also hosts an unrelated site. Books
        // are read in memory and dropped; not one may land on the disk.
        $this->assertSame($before, count(glob(sys_get_temp_dir().'/*')));
    }
}
