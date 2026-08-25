<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class HealthChecks
{
    /**
     * A backup older than this has silently stopped running. A day plus two
     * hours: the job runs at 02:30, so a single late night is not an alarm.
     */
    private const BACKUP_HOURS = 26;

    /**
     * The library shares its server with another project, and a full disk
     * takes down both. This leaves room to notice before that happens.
     */
    private const DISK_PERCENT = 85;

    /**
     * What is true about the service right now.
     *
     * Each check answers with a boolean and a short line for a person. The
     * line names no paths and no exact sizes: the endpoint that reports this
     * is public, because a monitor watching from outside the server is the
     * only kind that can tell you the server is gone.
     *
     * @return array<string, array{ok: bool, detail: string}>
     */
    public static function all(): array
    {
        return [
            'database' => self::database(),
            'disk' => self::disk(),
            'backup' => self::backup(),
        ];
    }

    public static function passing(array $checks): bool
    {
        foreach ($checks as $check) {
            if (! $check['ok']) {
                return false;
            }
        }

        return true;
    }

    /**
     * The names of the checks that are failing, for a one-line alert.
     *
     * @return list<string>
     */
    public static function failing(array $checks): array
    {
        return array_keys(array_filter($checks, fn ($check) => ! $check['ok']));
    }

    private static function database(): array
    {
        try {
            // Not a bare connection: a pooled handle can be open to a server
            // that has stopped answering.
            DB::select('select 1');

            return ['ok' => true, 'detail' => 'reachable'];
        } catch (\Throwable $e) {
            return ['ok' => false, 'detail' => 'unreachable'];
        }
    }

    private static function disk(): array
    {
        $free = @disk_free_space(base_path());
        $total = @disk_total_space(base_path());

        if ($free === false || $total === false || $total <= 0) {
            return ['ok' => true, 'detail' => 'unknown'];
        }

        $used = (int) round(($total - $free) / $total * 100);

        return [
            'ok' => $used < self::DISK_PERCENT,
            'detail' => $used.'% used',
        ];
    }

    private static function backup(): array
    {
        $dir = storage_path('backups');

        if (! is_dir($dir)) {
            return ['ok' => false, 'detail' => 'never run'];
        }

        $newest = collect(File::files($dir))->max(fn ($file) => $file->getMTime());

        if ($newest === null) {
            return ['ok' => false, 'detail' => 'never run'];
        }

        $hours = (int) floor((time() - $newest) / 3600);

        return [
            'ok' => $hours < self::BACKUP_HOURS,
            'detail' => $hours.'h old',
        ];
    }
}
