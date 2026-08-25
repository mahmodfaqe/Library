<?php

namespace App\Console\Commands;

use App\Support\HealthChecks;
use App\Support\Telegram;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ReportHealth extends Command
{
    protected $signature = 'health:report {--force : Send the current state even when nothing has changed}';

    protected $description = 'Check the service and send word when something breaks, or is mended';

    /**
     * Kept as a file rather than in the cache, because a deploy clears the
     * cache and would make every release look like a change of state.
     */
    private function memory(): string
    {
        return storage_path('app/health-state.json');
    }

    public function handle(): int
    {
        $checks = HealthChecks::all();
        $failing = HealthChecks::failing($checks);
        $was = $this->remembered();

        foreach ($checks as $name => $check) {
            $this->line(($check['ok'] ? '  ok   ' : '  FAIL ').str_pad($name, 10).$check['detail']);
        }

        $this->remember($failing);

        // Only a change is worth a message. An alert that arrives every hour
        // for a week is one nobody reads by the end of it.
        if (! $this->option('force') && $failing === $was) {
            return self::SUCCESS;
        }

        if ($failing === [] && $was === null) {
            // First run on a healthy service: nothing has happened yet.
            return self::SUCCESS;
        }

        Telegram::send($this->message($checks, $failing, $was));

        return self::SUCCESS;
    }

    private function message(array $checks, array $failing, ?array $was): string
    {
        $where = config('app.url');

        if ($failing === []) {
            return "✅ The library is well again.\n{$where}";
        }

        $lines = ['⚠️ The library needs looking at.', $where, ''];

        foreach ($checks as $name => $check) {
            $lines[] = ($check['ok'] ? '✅' : '❌').' '.$name.': '.$check['detail'];
        }

        if ($was !== null && $was !== []) {
            $lines[] = '';
            $lines[] = 'Was already failing: '.implode(', ', $was);
        }

        return implode("\n", $lines);
    }

    /**
     * @return list<string>|null
     */
    private function remembered(): ?array
    {
        if (! is_file($this->memory())) {
            return null;
        }

        $stored = json_decode((string) file_get_contents($this->memory()), true);

        return is_array($stored) ? $stored : null;
    }

    private function remember(array $failing): void
    {
        File::ensureDirectoryExists(dirname($this->memory()));
        file_put_contents($this->memory(), json_encode(array_values($failing)));
    }
}
