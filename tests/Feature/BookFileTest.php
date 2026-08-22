<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BookFileTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('books');
    }

    private function staff(): User
    {
        return User::create([
            'name' => 'Library Staff', 'email' => 'staff@uor.edu.krd',
            'password' => 'correct-horse-battery-staple', 'role' => User::ROLE_STAFF,
        ]);
    }

    private function pdf(string $name = 'book.pdf', int $kb = 40): UploadedFile
    {
        return UploadedFile::fake()->create($name, $kb, 'application/pdf');
    }

    public function test_staff_can_upload_a_book_file(): void
    {
        $this->actingAs($this->staff())
            ->post('/admin/books', ['title' => 'Genetics', 'file' => $this->pdf()])
            ->assertRedirect(route('admin.books'));

        $book = Book::firstOrFail();

        $this->assertTrue($book->hasFile());
        Storage::disk('books')->assertExists($book->file_path);
        $this->assertGreaterThan(0, $book->file_size);
    }

    public function test_a_book_needs_either_a_file_or_a_link(): void
    {
        $this->actingAs($this->staff())
            ->from(route('admin.books.create'))
            ->post('/admin/books', ['title' => 'Nowhere'])
            ->assertSessionHasErrors('file');

        $this->assertDatabaseCount('books', 0);
    }

    public function test_a_link_alone_is_enough(): void
    {
        $this->actingAs($this->staff())
            ->post('/admin/books', ['title' => 'Linked', 'url' => 'https://drive.example.test/x'])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseCount('books', 1);
    }

    public function test_only_pdfs_are_accepted(): void
    {
        $this->actingAs($this->staff())
            ->from(route('admin.books.create'))
            ->post('/admin/books', [
                'title' => 'Not a book',
                'file' => UploadedFile::fake()->create('virus.exe', 10, 'application/x-msdownload'),
            ])
            ->assertSessionHasErrors('file');

        $this->assertDatabaseCount('books', 0);
    }

    public function test_replacing_a_file_removes_the_old_one(): void
    {
        $staff = $this->staff();
        $this->actingAs($staff)->post('/admin/books', ['title' => 'Genetics', 'file' => $this->pdf()]);

        $book = Book::firstOrFail();
        $original = $book->file_path;

        $this->actingAs($staff)->put("/admin/books/{$book->id}", [
            'title' => 'Genetics', 'file' => $this->pdf('second.pdf'),
        ]);

        // Otherwise every re-upload would leave a copy behind for ever.
        Storage::disk('books')->assertMissing($original);
        Storage::disk('books')->assertExists($book->fresh()->file_path);
    }

    public function test_deleting_a_book_removes_its_file(): void
    {
        $staff = $this->staff();
        $this->actingAs($staff)->post('/admin/books', ['title' => 'Genetics', 'file' => $this->pdf()]);

        $book = Book::firstOrFail();
        $path = $book->file_path;

        $this->actingAs($staff)->delete("/admin/books/{$book->id}");

        Storage::disk('books')->assertMissing($path);
    }

    public function test_a_visitor_can_download_and_the_count_rises(): void
    {
        $this->actingAs($this->staff())->post('/admin/books', ['title' => 'Genetics', 'file' => $this->pdf()]);
        $book = Book::firstOrFail();

        $this->get(route('books.download', $book))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');

        $this->assertSame(1, $book->fresh()->downloads);
    }

    public function test_a_missing_file_is_a_404_not_a_crash(): void
    {
        $book = Book::create(['title' => 'Ghost', 'file_path' => 'gone.pdf']);

        $this->get(route('books.download', $book))->assertNotFound();
    }

    public function test_the_catalogue_offers_the_local_copy(): void
    {
        $this->actingAs($this->staff())->post('/admin/books', ['title' => 'Genetics', 'file' => $this->pdf()]);
        $book = Book::firstOrFail();

        $this->get('/en/books')
            ->assertOk()
            ->assertSee(route('books.download', $book), false);
    }
}
