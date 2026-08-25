<?php

namespace App\Console\Commands;

use App\Support\Telegram;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;

class BackupDatabase extends Command
{
    protected $signature = 'backup:database {--keep=14 : How many daily copies to retain}';

    protected $description = 'Write a database backup into storage/backups and prune old copies';

    public function handle(): int
    {
        $dir = storage_path('backups');
        File::ensureDirectoryExists($dir, 0750);

        $driver = config('database.default');
        $stamp = now()->format('Y-m-d_His');

        $target = match ($driver) {
            'sqlite' => $this->backupSqlite($dir, $stamp),
            'mysql', 'mariadb' => $this->backupMysql($dir, $stamp),
            default => null,
        };

        if ($target === null) {
            // A backup that fails quietly is the reason people discover they
            // have none on the day they need one.
            $this->reportFailure("No backup strategy for the [{$driver}] driver.");

            return self::FAILURE;
        }

        $this->info('Wrote '.basename($target).' ('.number_format(filesize($target) / 1024, 1).' KB)');

        $this->prune($dir, max(1, (int) $this->option('keep')));

        return self::SUCCESS;
    }

    /**
     * VACUUM INTO takes a consistent copy even while the site is serving.
     */
    private function backupSqlite(string $dir, string $stamp): ?string
    {
        $source = config('database.connections.sqlite.database');

        if (! is_string($source) || ! is_file($source)) {
            $this->reportFailure('The SQLite database was not found.');

            return null;
        }

        $target = "{$dir}/database-{$stamp}.sqlite";
        DB::connection('sqlite')->statement('VACUUM INTO ?', [$target]);

        return $target;
    }

    /**
     * The password goes through the environment rather than the command line,
     * where it would be visible to anyone running ps.
     */
    private function backupMysql(string $dir, string $stamp): ?string
    {
        $c = config('database.connections.'.config('database.default'));
        $target = "{$dir}/database-{$stamp}.sql.gz";

        $process = Process::fromShellCommandline(
            'mariadb-dump --host="$DB_HOST" --port="$DB_PORT" --user="$DB_USER" '
            .'--single-transaction --quick --default-character-set=utf8mb4 '
            .'"$DB_NAME" | gzip > "$TARGET"'
        );

        $process->setEnv([
            'DB_HOST' => $c['host'],
            'DB_PORT' => (string) $c['port'],
            'DB_USER' => $c['username'],
            'MYSQL_PWD' => $c['password'],
            'DB_NAME' => $c['database'],
            'TARGET' => $target,
        ]);
        $process->setTimeout(600);
        $process->run();

        if (! $process->isSuccessful()) {
            $this->reportFailure(trim($process->getErrorOutput()) ?: 'mariadb-dump failed.');
            @unlink($target);

            return null;
        }

        return $target;
    }

    /**
     * Say it on the console and on the phone both: nobody is reading the
     * scheduler's output at half past two in the morning.
     */
    private function reportFailure(string $reason): void
    {
        $this->error($reason);

        Telegram::send("❌ The library's backup did not run.\n".config('app.url')."\n\n".$reason);
    }

    private function prune(string $dir, int $keep): void
    {
        $backups = collect(glob($dir.'/database-*') ?: [])
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
