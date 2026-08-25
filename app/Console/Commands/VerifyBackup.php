<?php

namespace App\Console\Commands;

use App\Support\BackupCipher;
use App\Support\Telegram;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class VerifyBackup extends Command
{
    protected $signature = 'backup:verify {file? : A particular backup, rather than the newest}
                            {--quiet-when-well : Say nothing on success, for the scheduler}';

    protected $description = 'Open the newest backup and check it holds the library';

    /**
     * A backup nobody has ever restored is not a backup: it is a file that is
     * assumed to be one.
     *
     * This opens the real thing — decrypts it, reads it, and looks for the
     * library inside — so that the day it is needed is not the first day
     * anyone finds out whether it works.
     */
    public function handle(): int
    {
        $file = $this->argument('file') ?: $this->newest();

        if ($file === null) {
            return $this->failed('There are no backups at all.');
        }

        $this->line('Checking '.basename($file));

        try {
            $contents = (string) file_get_contents($file);

            if (str_ends_with($file, '.enc')) {
                $key = BackupCipher::key();

                if ($key === null) {
                    return $this->failed('The backup is encrypted and BACKUP_KEY is not set.');
                }

                $contents = BackupCipher::decrypt($contents, $key);
                $this->line('  decrypted');
            }

            $books = str_ends_with($file, '.sqlite') || str_ends_with($file, '.sqlite.enc')
                ? $this->readSqlite($contents)
                : $this->readDump($contents);
        } catch (\Throwable $e) {
            return $this->failed($e->getMessage());
        }

        if ($books === null) {
            return $this->failed('The backup opened, but there is no books table in it.');
        }

        // An empty catalogue restores perfectly and is still a disaster.
        if ($books === 0) {
            return $this->failed('The backup holds a books table with nothing in it.');
        }

        $this->info("  holds {$books} books — this backup would restore.");

        return self::SUCCESS;
    }

    private function newest(): ?string
    {
        $dir = storage_path('backups');

        if (! is_dir($dir)) {
            return null;
        }

        return collect(File::files($dir))
            ->sortByDesc(fn ($file) => $file->getMTime())
            ->first()?->getPathname();
    }

    /**
     * Open the copy as a database of its own and count what is in it. Nothing
     * touches the live connection.
     */
    private function readSqlite(string $contents): ?int
    {
        $scratch = tempnam(sys_get_temp_dir(), 'verify');
        file_put_contents($scratch, $contents);

        try {
            $pdo = new \PDO('sqlite:'.$scratch);
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

            $table = $pdo->query("select name from sqlite_master where type='table' and name='books'")->fetch();

            if ($table === false) {
                return null;
            }

            return (int) $pdo->query('select count(*) from books')->fetchColumn();
        } finally {
            @unlink($scratch);
        }
    }

    /**
     * A mariadb-dump, gzipped. Reading it is enough: if the statements that
     * rebuild the books table are there and intact, so is the catalogue.
     */
    private function readDump(string $contents): ?int
    {
        $sql = @gzdecode($contents);

        if ($sql === false) {
            throw new \RuntimeException('The backup is not readable gzip.');
        }

        if (! str_contains($sql, 'CREATE TABLE `books`')) {
            return null;
        }

        // Rows arrive as INSERT INTO `books` VALUES (...),(...); so the count
        // of value groups is the count of books.
        preg_match_all('/INSERT INTO `books` VALUES (.+?);\n/s', $sql, $found);

        $rows = 0;

        foreach ($found[1] ?? [] as $values) {
            $rows += substr_count($values, '),(') + 1;
        }

        return $rows;
    }

    private function failed(string $reason): int
    {
        $this->error($reason);

        Telegram::send("❌ The library's backup did not verify.\n".config('app.url')."\n\n".$reason);

        return self::FAILURE;
    }
}
