<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class DriveImportTest extends TestCase
{
    use RefreshDatabase;

    private const ROOT = 'root-folder';

    protected function setUp(): void
    {
        parent::setUp();
        config(['library.google_api_key' => 'test-key']);
    }

    /**
     * Drive returns children per parent folder, so the fake is keyed the same way.
     */
    private function fakeDrive(array $byParent): void
    {
        Http::fake(function ($request) use ($byParent) {
            preg_match("/'([^']+)' in parents/", urldecode($request->url()), $m);

            return Http::response(['files' => $byParent[$m[1] ?? ''] ?? []]);
        });
    }

    private function folder(string $id, string $name): array
    {
        return ['id' => $id, 'name' => $name, 'mimeType' => 'application/vnd.google-apps.folder'];
    }

    private function pdf(string $id, string $name, int $size = 1024): array
    {
        return ['id' => $id, 'name' => $name, 'mimeType' => 'application/pdf', 'size' => (string) $size];
    }

    public function test_a_book_nested_below_its_language_folder_keeps_that_language(): void
    {
        // The collection groups some subjects into sub-topics underneath the
        // language folder. Reading exactly two levels would miss every one of
        // those books.
        $this->fakeDrive([
            self::ROOT => [$this->folder('bio', 'بایۆلۆجی')],
            'bio' => [$this->folder('bio-ku', 'کوردی')],
            'bio-ku' => [
                $this->pdf('loose', 'ژیناسی.pdf'),
                $this->folder('bio-ku-cells', 'زانستی خانە'),
            ],
            'bio-ku-cells' => [$this->pdf('nested', 'خانەی زیندوو.pdf')],
        ]);

        $this->artisan('books:import-drive', ['folder' => [self::ROOT]])->assertSuccessful();

        $this->assertSame(2, Book::count());

        foreach (Book::all() as $book) {
            $this->assertSame('کوردی', $book->language, $book->title);
        }
    }

    public function test_the_deepest_language_folder_wins(): void
    {
        // If a folder inside a language folder names another language, the
        // nearer one is the one that describes the books in it.
        $this->fakeDrive([
            self::ROOT => [$this->folder('bio', 'بایۆلۆجی')],
            'bio' => [$this->folder('bio-ku', 'کوردی')],
            'bio-ku' => [$this->folder('bio-ku-en', 'English')],
            'bio-ku-en' => [$this->pdf('b1', 'Cell Biology.pdf')],
        ]);

        $this->artisan('books:import-drive', ['folder' => [self::ROOT]])->assertSuccessful();

        $this->assertSame('English', Book::firstOrFail()->language);
    }

    public function test_it_names_the_folders_whose_language_it_could_not_read(): void
    {
        $this->fakeDrive([
            self::ROOT => [$this->folder('bio', 'بایۆلۆجی')],
            'bio' => [
                $this->folder('bio-ku', 'کوردی'),
                $this->folder('bio-mixed', 'عربی-ئینگلیزی'),
            ],
            'bio-ku' => [$this->pdf('b1', 'ژیناسی.pdf')],
            'bio-mixed' => [$this->pdf('b2', 'Mixed.pdf')],
        ]);

        $this->artisan('books:import-drive', ['folder' => [self::ROOT]])
            ->expectsOutputToContain('No language could be read from these folders:')
            ->expectsOutputToContain('عربی-ئینگلیزی')
            ->assertSuccessful();

        // The one it could read is still recorded; the other is simply blank.
        $this->assertSame('کوردی', Book::where('drive_file_id', 'b1')->firstOrFail()->language);
        $this->assertNull(Book::where('drive_file_id', 'b2')->firstOrFail()->language);
    }

    public function test_a_readable_language_folder_is_not_reported(): void
    {
        $this->fakeDrive([
            self::ROOT => [$this->folder('bio', 'بایۆلۆجی')],
            'bio' => [$this->folder('bio-en', 'انگلیزی')],
            'bio-en' => [$this->pdf('b1', 'Cell Biology.pdf')],
        ]);

        $this->artisan('books:import-drive', ['folder' => [self::ROOT]])
            ->doesntExpectOutputToContain('No language could be read')
            ->assertSuccessful();

        $this->assertSame('English', Book::firstOrFail()->language);
    }

    public function test_a_dry_run_does_not_touch_a_book_already_in_the_catalogue(): void
    {
        $existing = Book::create([
            'title' => 'ژیناسی',
            'drive_file_id' => 'b1',
            'language' => 'کوردی',
        ]);

        // The same file, now filed under English on Drive.
        $this->fakeDrive([
            self::ROOT => [$this->folder('bio', 'بایۆلۆجی')],
            'bio' => [$this->folder('bio-en', 'English')],
            'bio-en' => [$this->pdf('b1', 'ژیناسی.pdf')],
        ]);

        $this->artisan('books:import-drive', ['folder' => [self::ROOT], '--dry-run' => true])
            ->assertSuccessful();

        // A run asked to write nothing must write nothing — not even to the
        // records it merely refreshes.
        $existing->refresh();

        $this->assertSame('کوردی', $existing->language);
        $this->assertNull($existing->category_id);
        $this->assertSame(0, Category::count());
    }

    public function test_a_real_run_does_refresh_what_drive_owns(): void
    {
        $existing = Book::create([
            'title' => 'ژیناسی',
            'drive_file_id' => 'b1',
            'language' => 'کوردی',
        ]);

        $this->fakeDrive([
            self::ROOT => [$this->folder('bio', 'بایۆلۆجی')],
            'bio' => [$this->folder('bio-en', 'English')],
            'bio-en' => [$this->pdf('b1', 'ژیناسی.pdf')],
        ]);

        $this->artisan('books:import-drive', ['folder' => [self::ROOT]])->assertSuccessful();

        $this->assertSame('English', $existing->refresh()->language);
    }

    public function test_a_folder_may_be_given_as_the_url_from_the_address_bar(): void
    {
        $this->fakeDrive([
            self::ROOT => [$this->folder('bio', 'بایۆلۆجی')],
            'bio' => [$this->pdf('b1', 'Cell Biology.pdf')],
        ]);

        // What a librarian actually has to hand is the URL, not the bare id.
        $this->artisan('books:import-drive', [
            'folder' => ['https://drive.google.com/drive/folders/'.self::ROOT],
        ])->assertSuccessful();

        $this->assertSame(1, Book::count());
    }

    public function test_subject_folders_become_categories(): void
    {
        $this->fakeDrive([
            self::ROOT => [$this->folder('bio', '١- بایۆلۆجی')],
            'bio' => [$this->pdf('b1', '12-Molecular Biology.pdf')],
        ]);

        $this->artisan('books:import-drive', ['folder' => [self::ROOT]])->assertSuccessful();

        $category = Category::firstOrFail();

        $this->assertSame('بایۆلۆجی', $category->name);
        $this->assertSame(1, $category->sort_order);
        $this->assertSame($category->id, Book::firstOrFail()->category_id);
    }

    public function test_it_strips_the_ordering_number_from_titles(): void
    {
        $this->fakeDrive([
            self::ROOT => [$this->folder('c', 'Subject')],
            'c' => [
                $this->pdf('a', '50-أسس الكيمياء العضوية'),
                $this->pdf('b', 'ECG Mastering-٩'),
                $this->pdf('c', '١٢-الفحوص-المختبرية'),
                $this->pdf('d', '43-Macleod’s Clinical Examination.pdf'),
            ],
        ]);

        $this->artisan('books:import-drive', ['folder' => [self::ROOT]])->assertSuccessful();

        $titles = Book::orderBy('id')->pluck('title')->all();

        $this->assertSame([
            'أسس الكيمياء العضوية',
            'ECG Mastering',
            // No spaces anywhere, so the hyphens are holding words apart.
            'الفحوص المختبرية',
            'Macleod’s Clinical Examination',
        ], $titles);
    }

    public function test_filename_separators_become_spaces(): void
    {
        $this->fakeDrive([
            self::ROOT => [$this->folder('c', 'Subject')],
            'c' => [
                $this->pdf('a', 'for_the_love_of_physics'),
                $this->pdf('b', 'A-collection-of-questions-in-physics'),
                $this->pdf('c', 'Fundamentals_of_Physics_Mechanics.pdf'),
            ],
        ]);

        $this->artisan('books:import-drive', ['folder' => [self::ROOT]])->assertSuccessful();

        $this->assertSame([
            'for the love of physics',
            'A collection of questions in physics',
            'Fundamentals of Physics Mechanics',
        ], Book::orderBy('id')->pluck('title')->all());
    }

    public function test_a_real_title_keeps_its_punctuation(): void
    {
        $this->fakeDrive([
            self::ROOT => [$this->folder('c', 'Subject')],
            'c' => [
                // Already has spaces, so the hyphen is the author's own.
                $this->pdf('a', '7-Evidence-based Nursing Care'),
                $this->pdf('b', 'Introductory Physics: Problems solving'),
            ],
        ]);

        $this->artisan('books:import-drive', ['folder' => [self::ROOT]])->assertSuccessful();

        $this->assertSame([
            'Evidence-based Nursing Care',
            'Introductory Physics: Problems solving',
        ], Book::orderBy('id')->pluck('title')->all());
    }

    public function test_it_reaches_books_nested_one_level_deeper(): void
    {
        $this->fakeDrive([
            self::ROOT => [$this->folder('cat', 'Subject')],
            'cat' => [$this->folder('sub', 'Part one'), $this->pdf('top', 'Top level book')],
            'sub' => [$this->pdf('deep', 'Nested book')],
        ]);

        $this->artisan('books:import-drive', ['folder' => [self::ROOT]])->assertSuccessful();

        $this->assertEqualsCanonicalizing(
            ['Top level book', 'Nested book'],
            Book::pluck('title')->all()
        );
    }

    public function test_running_twice_does_not_duplicate(): void
    {
        $this->fakeDrive([
            self::ROOT => [$this->folder('c', 'Subject')],
            'c' => [$this->pdf('b1', 'A book')],
        ]);

        $this->artisan('books:import-drive', ['folder' => [self::ROOT]]);
        $this->artisan('books:import-drive', ['folder' => [self::ROOT]]);

        $this->assertDatabaseCount('books', 1);
        $this->assertDatabaseCount('categories', 1);
    }

    public function test_dry_run_writes_nothing(): void
    {
        $this->fakeDrive([
            self::ROOT => [$this->folder('c', 'Subject')],
            'c' => [$this->pdf('b1', 'A book')],
        ]);

        $this->artisan('books:import-drive', ['folder' => [self::ROOT], '--dry-run' => true])
            ->assertSuccessful();

        $this->assertDatabaseCount('books', 0);
        $this->assertDatabaseCount('categories', 0);
    }

    public function test_non_pdf_files_are_ignored(): void
    {
        $this->fakeDrive([
            self::ROOT => [$this->folder('c', 'Subject')],
            'c' => [
                $this->pdf('b1', 'A book'),
                ['id' => 'x', 'name' => 'notes.docx', 'mimeType' => 'application/msword'],
            ],
        ]);

        $this->artisan('books:import-drive', ['folder' => [self::ROOT]]);

        $this->assertDatabaseCount('books', 1);
    }

    public function test_it_records_the_drive_link_and_size(): void
    {
        $this->fakeDrive([
            self::ROOT => [$this->folder('c', 'Subject')],
            'c' => [$this->pdf('abc123', 'A book', 5_242_880)],
        ]);

        $this->artisan('books:import-drive', ['folder' => [self::ROOT]]);

        $book = Book::firstOrFail();

        $this->assertSame('https://drive.google.com/file/d/abc123/view', $book->url);
        $this->assertSame(5_242_880, $book->file_size);
        $this->assertSame('5.0 MB', $book->humanFileSize());
    }

    public function test_a_missing_api_key_stops_it(): void
    {
        config(['library.google_api_key' => '']);

        $this->artisan('books:import-drive', ['folder' => [self::ROOT]])->assertFailed();
    }
}
