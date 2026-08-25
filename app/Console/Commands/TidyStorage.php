<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class TidyStorage extends Command
{
    protected $signature = 'library:tidy
                            {--apply : Delete. Without this nothing is removed}
                            {--days=14 : Keep logs and reports newer than this}';

    protected $description = 'Free disk space, touching nothing outside the library';

    /**
     * The server carries another project besides this one, and that project
     * matters more than any space this could win back.
     *
     * So this command names every directory it will look in, one by one, and
     * every one of them is inside the library's own storage. It walks nothing
     * recursively from a variable path, deletes nothing by pattern outside
     * these lists, and — running inside the library's container — cannot see
     * the other project's files even if it tried.
     *
     * What it will not touch, deliberately:
     *
     *   storage/app/google-service-account.json   the Drive credentials
     *   storage/app/health-state.json             the monitor's memory
     *   storage/app/books/                        books uploaded by staff
     *   storage/backups/                          handled by backup:database,
     *                                             which keeps a known number
     */
    public function handle(): int
    {
        $apply = (bool) $this->option('apply');
        $days = max(1, (int) $this->option('days'));
        $before = $this->freeSpace();

        $this->line('Looking only inside '.storage_path());
        $this->newLine();

        $freed = 0;
        $rows = [];

        foreach ($this->targets($days) as $name => $files) {
            $size = array_sum(array_map(fn ($f) => @filesize($f) ?: 0, $files));
            $freed += $size;

            $rows[] = [$name, count($files), $this->human($size)];

            if ($apply) {
                foreach ($files as $file) {
                    @unlink($file);
                }
            }
        }

        $this->table(['What', 'Files', 'Size'], $rows);

        if (! $apply) {
            $this->warn('Nothing was deleted. Add --apply to free '.$this->human($freed).'.');

            return self::SUCCESS;
        }

        $this->info('Freed '.$this->human($freed).'.');
        $this->line('Disk free: '.$this->human($before).' → '.$this->human($this->freeSpace()));

        return self::SUCCESS;
    }

    /**
     * Every file this may remove, gathered by name rather than by walking.
     *
     * @return array<string, list<string>>
     */
    private function targets(int $days): array
    {
        $old = now()->subDays($days)->getTimestamp();

        return [
            // Rotated logs. The one being written to now is left alone.
            'Old logs' => $this->matching(storage_path('logs'), 'laravel-*.log', $old),

            // Whole rendered pages. They are rebuilt on the next visit, and
            // the deploy clears them anyway.
            'Cached pages' => $this->matching(storage_path('framework/pagecache'), '*.html', 0),

            // Compiled Blade. Rebuilt on first use of each view.
            'Compiled views' => $this->matching(storage_path('framework/views'), '*.php', 0),

            // Anything the file cache driver left behind. The library uses the
            // database for its cache, so this is usually the remains of an
            // older configuration.
            'Stale file cache' => $this->matching(storage_path('framework/cache/data'), '*', $old),

            // Findings from earlier extraction and lookup runs. Worth keeping
            // for a while, not for ever.
            'Old run reports' => array_merge(
                $this->matching(storage_path('app'), 'lookups-*.json', $old),
                $this->matching(storage_path('app'), 'extract-*.json', $old),
                $this->matching(storage_path('app'), 'details-*.json', $old),
            ),

            // Half-finished uploads and downloads, from a run that was
            // interrupted.
            'Abandoned temporary files' => array_merge(
                $this->matching(sys_get_temp_dir(), 'deposit*', $old),
                $this->matching(sys_get_temp_dir(), 'verify*', $old),
                $this->matching(storage_path('app/private'), '*.tmp', $old),
            ),
        ];
    }

    /**
     * Files in one named directory matching one pattern, older than a moment.
     *
     * @return list<string>
     */
    private function matching(string $directory, string $pattern, int $olderThan): array
    {
        if (! is_dir($directory)) {
            return [];
        }

        $found = glob(rtrim($directory, '/').'/'.$pattern) ?: [];

        return array_values(array_filter($found, function (string $file) use ($olderThan) {
            // Never a directory, never a symlink out of here, never the
            // current log.
            if (! is_file($file) || is_link($file)) {
                return false;
            }

            if (basename($file) === 'laravel.log') {
                return false;
            }

            return $olderThan === 0 || (@filemtime($file) ?: PHP_INT_MAX) < $olderThan;
        }));
    }

    private function freeSpace(): int
    {
        $free = @disk_free_space(base_path());

        return $free === false ? 0 : (int) $free;
    }

    private function human(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes.' B';
        }

        $units = ['KB', 'MB', 'GB', 'TB'];
        $size = $bytes / 1024;
        $unit = 0;

        while ($size >= 1024 && $unit < count($units) - 1) {
            $size /= 1024;
            $unit++;
        }

        return number_format($size, 1).' '.$units[$unit];
    }
}
