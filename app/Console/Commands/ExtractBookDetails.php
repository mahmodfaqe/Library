<?php

namespace App\Console\Commands;

use App\Models\Book;
use App\Support\DriveApi;
use App\Support\PdfDetails;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class ExtractBookDetails extends Command
{
    protected $signature = 'books:extract-details
        {--force : Replace details that are already recorded}
        {--limit=0 : Stop after this many books, for a first look}
        {--max-mb=60 : Skip files larger than this}
        {--dry-run : Report what would change without writing anything}
        {--targets= : Read the books to look at from this JSON file instead of the database}
        {--results= : Write what was found to this JSON file instead of the database}
        {--apply= : Write a results file produced elsewhere into the database}
        {--delay=2 : Seconds to wait between books, to stay under Drive\'s rate limit}';

    protected $description = 'Read the author and year out of each book\'s own PDF';

    public function handle(): int
    {
        if ($this->option('apply')) {
            return $this->apply($this->option('apply'));
        }

        $key = config('library.google_api_key');

        $books = $this->targets();

        if ($books->isEmpty()) {
            $this->info('Nothing to read: every book already has an author and a year.');

            return self::SUCCESS;
        }

        $this->line("Reading {$books->count()} book(s).");
        $bar = $this->output->createProgressBar($books->count());

        $found = $unchanged = $unreadable = 0;

        // A run over a thousand books takes hours. Results are written as they
        // are found and re-read on the next run, so an interruption costs the
        // book in hand and nothing else.
        $collected = $this->option('results') && is_file($this->option('results'))
            ? (json_decode(file_get_contents($this->option('results')), true) ?: [])
            : [];

        foreach ($books as $book) {
            $bar->advance();

            if (array_key_exists((string) $book->id, $collected)) {
                $unchanged++;

                continue;
            }

            // Marked as attempted before the file is touched. Parsing a very
            // large scanned book can exhaust memory and take the process with
            // it; without this the next run reaches the same book and dies the
            // same way, and the run never gets past it.
            if ($this->option('results')) {
                $collected[(string) $book->id] = [];
                $this->save($collected);
            }

            $pdf = $this->contents($book, $key);

            if ($pdf === self::RATE_LIMITED) {
                $bar->finish();
                $this->newLine(2);
                $this->warn('Drive is still refusing downloads after backing off. Stopping here.');
                $this->line('Everything found so far is saved; run the same command again later to carry on.');

                break;
            }

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

            if ($this->option('results')) {
                $collected[(string) $book->id] = $changes;
                $this->save($collected);

                continue;
            }

            $book->update($changes);
        }

        $bar->finish();
        $this->newLine(2);

        if ($this->option('results')) {
            $this->save($collected);
            $this->line('Wrote '.count($collected).' result(s) to '.$this->option('results'));
        }

        $this->info("Filled in {$found}, found nothing new in {$unchanged}, could not read {$unreadable}.");

        return self::SUCCESS;
    }

    /**
     * @param  array<string, array<string, string|int>>  $collected
     */
    private function save(array $collected): void
    {
        file_put_contents(
            $this->option('results'),
            json_encode($collected, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
        );
    }

    /**
     * The books to look at.
     *
     * Normally the catalogue itself. With --targets they come from a file, so
     * the reading can be done on a machine that is not the server: the file
     * lists only ids and Drive references, and the books never land there.
     *
     * @return Collection<int, Book>
     */
    private function targets(): Collection
    {
        if ($path = $this->option('targets')) {
            return collect(json_decode(file_get_contents($path), true) ?: [])
                ->map(fn (array $row) => tap(new Book($row), function (Book $book) use ($row) {
                    $book->id = $row['id'];
                    $book->exists = true;
                }))
                ->when((int) $this->option('limit') > 0, fn ($c) => $c->take((int) $this->option('limit')));
        }

        return Book::query()
            ->where(fn ($q) => $q->whereNotNull('drive_file_id')->orWhereNotNull('file_path'))
            ->unless($this->option('force'), fn ($q) => $q->where(
                fn ($q) => $q->whereNull('author')->orWhereNull('year')
            ))
            ->when((int) $this->option('limit') > 0, fn ($q) => $q->limit((int) $this->option('limit')))
            ->orderBy('id')
            ->get();
    }

    /**
     * Write a results file produced elsewhere into the catalogue.
     */
    private function apply(string $path): int
    {
        if (! is_file($path)) {
            $this->error("No such file: {$path}");

            return self::FAILURE;
        }

        $results = json_decode(file_get_contents($path), true) ?: [];
        $written = $missing = 0;

        foreach ($results as $id => $changes) {
            $book = Book::find($id);

            if ($book === null) {
                $missing++;

                continue;
            }

            // Still never over a librarian's own correction, unless asked.
            $changes = collect($changes)
                ->reject(fn ($v, $field) => ! $this->option('force') && filled($book->{$field}))
                ->all();

            if ($changes !== []) {
                $book->update($changes);
                $written++;
            }
        }

        $this->info("Updated {$written} book(s), {$missing} no longer in the catalogue.");

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
     * Returned instead of the file when Drive has started refusing downloads.
     */
    private const RATE_LIMITED = "\0rate-limited";

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

        if (blank($book->drive_file_id) || (blank($key) && DriveApi::accessToken() === null)) {
            return null;
        }

        // A large book is mostly scanned images; reading it would cost far
        // more memory than the title page is worth.
        if ($book->file_size !== null && $book->file_size > (int) $this->option('max-mb') * 1024 ** 2) {
            return null;
        }

        // Drive tolerates a steady trickle and blocks a flood. Asking for a
        // thousand books back to back earns an IP-wide "Sorry..." page that
        // takes hours to clear, so the run waits between books and backs off
        // hard the moment it is refused.
        if (($delay = (float) $this->option('delay')) > 0) {
            usleep((int) ($delay * 1_000_000));
        }

        foreach ([0, 30, 90, 240] as $wait) {
            if ($wait > 0) {
                $this->newLine();
                $this->warn("  Drive refused the download; waiting {$wait}s before trying again.");
                sleep($wait);
            }

            $response = DriveApi::request(180)->get(
                DriveApi::FILES."/{$book->drive_file_id}",
                ['alt' => 'media'] + DriveApi::credentials($key)
            );

            if ($response->successful()) {
                return $response->body();
            }

            // A missing or private file is a fact about that file; only a
            // refusal aimed at us is worth waiting out.
            if (! $this->isRateLimited($response->status(), $response->body())) {
                return null;
            }
        }

        return self::RATE_LIMITED;
    }

    /**
     * Whether this refusal is aimed at us rather than at the file.
     *
     * A rate-limited download comes back as Google's HTML "Sorry..." page
     * rather than as a JSON API error, so the body has to be looked at.
     */
    private function isRateLimited(int $status, string $body): bool
    {
        if ($status === 429) {
            return true;
        }

        return $status === 403 && ! str_contains($body, '"error"');
    }
}
