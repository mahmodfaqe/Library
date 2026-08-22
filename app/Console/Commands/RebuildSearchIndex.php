<?php

namespace App\Console\Commands;

use App\Models\Book;
use App\Support\ArabicText;
use Illuminate\Console\Command;

class RebuildSearchIndex extends Command
{
    protected $signature = 'books:reindex';

    protected $description = 'Rebuild the folded search text for every book';

    public function handle(): int
    {
        $total = Book::count();
        $bar = $this->output->createProgressBar($total);

        Book::select('id', 'title', 'author')->chunkById(200, function ($books) use ($bar) {
            foreach ($books as $book) {
                // Written directly: this is a derived column, and touching
                // timestamps on a reindex would be misleading.
                Book::whereKey($book->id)->update([
                    'search_text' => ArabicText::fold($book->title.' '.$book->author),
                ]);
                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine(2);
        $this->info("Reindexed {$total} book(s).");

        return self::SUCCESS;
    }
}
