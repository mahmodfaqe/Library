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
            // the inner hyphen is part of the title and must survive
            'الفحوص-المختبرية',
            'Macleod’s Clinical Examination',
        ], $titles);
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
