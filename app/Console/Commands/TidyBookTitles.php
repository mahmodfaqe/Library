<?php

namespace App\Console\Commands;

use App\Models\Book;
use App\Support\ArabicText;
use Illuminate\Console\Command;

class TidyBookTitles extends Command
{
    protected $signature = 'books:tidy-titles {--dry-run : Show what would change without writing}';

    protected $description = 'Turn leftover filename separators in book titles into spaces';

    public function handle(): int
    {
        $changed = 0;

        Book::select('id', 'title', 'author')->chunkById(200, function ($books) use (&$changed) {
            foreach ($books as $book) {
                $tidy = $this->tidy($book->title);

                if ($tidy === $book->title) {
                    continue;
                }

                $this->line("  {$book->title}");
                $this->line("    → {$tidy}");
                $changed++;

                if (! $this->option('dry-run')) {
                    Book::whereKey($book->id)->update([
                        'title' => $tidy,
                        'search_text' => ArabicText::fold($tidy.' '.$book->author),
                    ]);
                }
            }
        });

        $this->newLine();
        $this->info($this->option('dry-run')
            ? "{$changed} title(s) would change."
            : "Tidied {$changed} title(s).");

        return self::SUCCESS;
    }

    /**
     * A title with no spaces is a filename whose words are held apart by
     * underscores or hyphens. One that already has spaces keeps its own
     * punctuation; only stray underscores are replaced.
     */
    private function tidy(string $title): string
    {
        $title = preg_match('/\s/u', $title)
            ? str_replace('_', ' ', $title)
            : str_replace(['_', '-'], ' ', $title);

        return trim(preg_replace('/\s+/u', ' ', $title));
    }
}
