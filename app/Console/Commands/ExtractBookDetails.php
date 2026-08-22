<?php

namespace App\Console\Commands;

use App\Models\Book;
use App\Support\PdfDetails;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class ExtractBookDetails extends Command
{
    protected $signature = 'books:extract-details
        {--force : Replace details that are already recorded}
        {--limit=0 : Stop after this many books, for a first look}
        {--max-mb=60 : Skip files larger than this}
        {--dry-run : Report what would change without writing anything}';

    protected $description = 'Read the author and year out of each book\'s own PDF';

    private const API = 'https://www.googleapis.com/drive/v3/files';

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

            $path = $this->fetch($book, $key);

            if ($path === null) {
                $unreadable++;

                continue;
            }

            try {
                $details = PdfDetails::read($path);
            } finally {
                // The file is borrowed, never kept. This server also hosts an
                // unrelated site, and a thousand PDFs would fill the disk.
                if ($book->file_path === null) {
                    @unlink($path);
                }
            }

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
     * A readable path to the book, downloading it only if it is not held here.
     */
    private function fetch(Book $book, ?string $key): ?string
    {
        if ($book->file_path !== null && Storage::disk('books')->exists($book->file_path)) {
            return Storage::disk('books')->path($book->file_path);
        }

        if (blank($book->drive_file_id) || blank($key)) {
            return null;
        }

        if ($book->file_size !== null && $book->file_size > (int) $this->option('max-mb') * 1024 ** 2) {
            return null;
        }

        $response = Http::timeout(180)->get(self::API."/{$book->drive_file_id}", [
            'alt' => 'media',
            'key' => $key,
        ]);

        if ($response->failed()) {
            return null;
        }

        // tempnam() creates the file it names, so write into that one rather
        // than a variation on the name — otherwise every book leaves an empty
        // file behind, a thousand of them by the end of a run.
        $path = tempnam(sys_get_temp_dir(), 'book-');

        if ($path === false) {
            return null;
        }

        file_put_contents($path, $response->body());

        return $path;
    }
}
