<?php

namespace App\Console\Commands;

use App\Models\Book;
use App\Support\DriveApi;
use App\Support\PdfDetails;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ExtractBookDetails extends Command
{
    protected $signature = 'books:extract-details
        {--force : Replace details that are already recorded}
        {--limit=0 : Stop after this many books, for a first look}
        {--max-mb=60 : Skip files larger than this}
        {--dry-run : Report what would change without writing anything}';

    protected $description = 'Read the author and year out of each book\'s own PDF';

    public function handle(): int
    {
        $key = config('library.google_api_key');

        $books = Book::query()
            ->where(fn ($q) => $q->whereNotNull('drive_file_id')->orWhereNotNull('file_path'))
            ->unless($this->option('force'), fn ($q) => $q->where(
                fn ($q) => $q->whereNull('author')->orWhereNull('year')
            ))
            ->when((int) $this->option('limit') > 0, fn ($q) => $q->limit((int) $this->option('limit')))
            ->orderBy('id')
            ->get();

        if ($books->isEmpty()) {
            $this->info('Nothing to read: every book already has an author and a year.');

            return self::SUCCESS;
        }

        $this->line("Reading {$books->count()} book(s).");
        $bar = $this->output->createProgressBar($books->count());

        $found = $unchanged = $unreadable = 0;

        foreach ($books as $book) {
            $bar->advance();

            $pdf = $this->contents($book, $key);

            if ($pdf === null) {
                $unreadable++;

                continue;
            }

            $details = PdfDetails::read($pdf);
            unset($pdf);

            $changes = $this->changes($book, $details);

            if ($changes === []) {
                $unchanged++;

                continue;
            }

            $found++;

            if ($this->option('dry-run')) {
                $this->newLine();
                $this->line("  {$book->title}");

                foreach ($changes as $field => $value) {
                    $this->line("    {$field}: <info>{$value}</info>");
                }

                continue;
            }

            $book->update($changes);
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("Filled in {$found}, found nothing new in {$unchanged}, could not read {$unreadable}.");

        return self::SUCCESS;
    }

    /**
     * The fields this book is missing that the file can supply.
     *
     * Without --force, only blanks are filled: a librarian who corrected an
     * author by hand outranks anything the file claims about itself.
     *
     * @param  array{author: ?string, year: ?int, language: ?string}  $details
     * @return array<string, string|int>
     */
    private function changes(Book $book, array $details): array
    {
        $changes = [];

        foreach (['author', 'year'] as $field) {
            if ($details[$field] === null) {
                continue;
            }

            if ($this->option('force') || blank($book->{$field})) {
                $changes[$field] = $details[$field];
            }
        }

        // The Drive folder is the authority on language; this only speaks for
        // the books whose folder said nothing.
        if ($details['language'] !== null && blank($book->language)) {
            $changes['language'] = $details['language'];
        }

        return $changes;
    }

    /**
     * The bytes of the book, or null if they cannot be had.
     *
     * Nothing is written to disk. The server is nearly full and also hosts an
     * unrelated site, so a book is held only long enough to read its opening
     * pages and is then dropped — one file in memory at a time, never a
     * thousand on the disk.
     */
    private function contents(Book $book, ?string $key): ?string
    {
        if ($book->file_path !== null && Storage::disk('books')->exists($book->file_path)) {
            return Storage::disk('books')->get($book->file_path);
        }

        if (blank($book->drive_file_id) || blank($key)) {
            return null;
        }

        // A large book is mostly scanned images; reading it would cost far
        // more memory than the title page is worth.
        if ($book->file_size !== null && $book->file_size > (int) $this->option('max-mb') * 1024 ** 2) {
            return null;
        }

        $response = DriveApi::request(180)->get(DriveApi::FILES."/{$book->drive_file_id}", [
            'alt' => 'media',
            'key' => $key,
        ]);

        return $response->successful() ? $response->body() : null;
    }
}
