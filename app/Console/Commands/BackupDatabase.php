<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class BackupDatabase extends Command
{
    protected $signature = 'backup:database {--keep=14 : How many daily copies to retain}';

    protected $description = 'Copy the SQLite database into storage/backups and prune old copies';

    public function handle(): int
    {
        $source = config('database.connections.sqlite.database');

        if (! is_string($source) || ! is_file($source)) {
            $this->error('The SQLite database was not found. Nothing to back up.');

            return self::FAILURE;
        }

        $dir = storage_path('backups');
        File::ensureDirectoryExists($dir, 0750);

        $target = $dir.'/database-'.now()->format('Y-m-d_His').'.sqlite';

        // VACUUM INTO takes a consistent copy even while the site is serving.
        DB::connection('sqlite')->statement('VACUUM INTO ?', [$target]);

        $this->info('Wrote '.basename($target).' ('.number_format(filesize($target) / 1024, 1).' KB)');

        $this->prune($dir, max(1, (int) $this->option('keep')));

        return self::SUCCESS;
    }

    private function prune(string $dir, int $keep): void
    {
        $backups = collect(glob($dir.'/database-*.sqlite') ?: [])
            ->sortByDesc(fn (string $file) => filemtime($file))
            ->values();

        $stale = $backups->slice($keep);

        foreach ($stale as $file) {
            File::delete($file);
        }

        if ($stale->isNotEmpty()) {
            $this->line('Pruned '.$stale->count().' older backup(s).');
        }
    }
}
