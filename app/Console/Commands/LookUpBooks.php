<?php

namespace App\Console\Commands;

use App\Models\Book;
use App\Support\OpenLibrary;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;

class LookUpBooks extends Command
{
    protected $signature = 'books:look-up
                            {--apply : Write what is found, rather than only reporting it}
                            {--limit=0 : Stop after this many books}
                            {--delay=350 : Milliseconds between calls, to stay a polite caller}
                            {--missing-author : Only books with no author recorded}
                            {--again : Include books that have been looked up before}';

    protected $description = 'Match the catalogue against OpenLibrary and fill in what is missing';

    /**
     * Where the run's findings are written, so that what was applied can be
     * read back afterwards, and what was only suggested is not lost.
     */
    private function resultsFile(): string
    {
        return storage_path('app/lookups-'.now()->format('Y-m-d_His').'.json');
    }

    public function handle(): int
    {
        $books = $this->targets();
        $total = $books->count();

        if ($total === 0) {
            $this->info('Nothing to look up.');

            return self::SUCCESS;
        }

        $this->line("Looking up {$total} books.");

        if (! $this->option('apply')) {
            $this->warn('Reporting only. Add --apply to write anything.');
        }

        $found = [];
        $applied = 0;
        $missed = 0;
        $bar = $this->output->createProgressBar($total);
        $bar->start();

        foreach ($books as $book) {
            $record = OpenLibrary::find($book->title);

            if ($record === null) {
                $missed++;
                $this->markChecked($book);
                $bar->advance();
                usleep((int) $this->option('delay') * 1000);

                continue;
            }

            $record['book_id'] = $book->id;
            $record['our_title'] = $book->title;
            $found[] = $record;

            if ($this->option('apply') && $this->write($book, $record)) {
                $applied++;
            }

            $bar->advance();
            usleep((int) $this->option('delay') * 1000);
        }

        $bar->finish();
        $this->newLine(2);

        $this->report($found, $total, $missed, $applied);

        return self::SUCCESS;
    }

    /**
     * @return Collection<int, Book>
     */
    private function targets()
    {
        $query = Book::query()->orderBy('id');

        if ($this->option('missing-author')) {
            $query->whereNull('author');
        }

        // A book already looked up is not looked up again: the answer will not
        // have changed, and the run is long enough without repeating it.
        if (! $this->option('again')) {
            $query->whereNull('metadata_checked_at');
        }

        if ($limit = (int) $this->option('limit')) {
            $query->limit($limit);
        }

        return $query->get();
    }

    /**
     * Write only what describes the work rather than one printing of it. The
     * rest is reported and left to a librarian — see OpenLibrary::SAFE.
     */
    private function write(Book $book, array $record): bool
    {
        $changes = [];

        foreach (OpenLibrary::SAFE as $field) {
            // Never overwrite what a person put there. A lookup is a guess
            // with a good record; a librarian is not.
            if (filled($book->{$field}) || blank($record[$field] ?? null)) {
                continue;
            }

            $changes[$field] = $record[$field];
        }

        $book->forceFill($changes + [
            'metadata_source' => 'openlibrary',
            'metadata_checked_at' => now(),
        ])->save();

        return $changes !== [];
    }

    private function markChecked(Book $book): void
    {
        $book->forceFill(['metadata_checked_at' => now()])->save();
    }

    private function report(array $found, int $total, int $missed, int $applied): void
    {
        $file = $this->resultsFile();
        File::ensureDirectoryExists(dirname($file));
        file_put_contents($file, json_encode($found, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

        $matched = count($found);

        $this->table(['', 'Books'], [
            ['Looked up', $total],
            ['Matched', $matched.' ('.($total ? round($matched / $total * 100) : 0).'%)'],
            ['No match', $missed],
            ['Written', $this->option('apply') ? $applied : '— (reporting only)'],
        ]);

        $withAuthor = count(array_filter($found, fn ($r) => filled($r['author'] ?? null)));
        $suggested = count(array_filter($found, fn ($r) => filled($r['isbn'] ?? null)));

        $this->line("  {$withAuthor} of the matches name an author.");
        $this->line("  {$suggested} carry an ISBN and publisher — reported, not written: a title");
        $this->line('  search answers for the work, not for the edition on the shelf.');
        $this->newLine();
        $this->info('Findings: '.$file);
    }
}
