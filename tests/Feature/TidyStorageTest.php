<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class TidyStorageTest extends TestCase
{
    private array $made = [];

    protected function tearDown(): void
    {
        foreach ($this->made as $path) {
            @unlink($path);
        }

        parent::tearDown();
    }

    private function file(string $path, int $daysOld = 0, string $contents = 'x'): string
    {
        File::ensureDirectoryExists(dirname($path));
        file_put_contents($path, $contents);

        if ($daysOld > 0) {
            touch($path, now()->subDays($daysOld)->getTimestamp());
        }

        $this->made[] = $path;

        return $path;
    }

    public function test_it_deletes_nothing_unless_asked(): void
    {
        $log = $this->file(storage_path('logs/laravel-2020-01-01.log'), 400);

        $this->artisan('library:tidy')
            ->expectsOutputToContain('Nothing was deleted')
            ->assertSuccessful();

        $this->assertFileExists($log);
    }

    public function test_it_removes_old_rotated_logs(): void
    {
        $old = $this->file(storage_path('logs/laravel-2020-01-01.log'), 400);
        $recent = $this->file(storage_path('logs/laravel-2026-08-25.log'), 1);

        $this->artisan('library:tidy --apply')->assertSuccessful();

        $this->assertFileDoesNotExist($old);
        $this->assertFileExists($recent);
    }

    public function test_it_never_touches_the_log_being_written_to(): void
    {
        // Deleting it out from under a running process loses everything
        // written between now and the next restart.
        $current = $this->file(storage_path('logs/laravel.log'), 400);

        $this->artisan('library:tidy --apply')->assertSuccessful();

        $this->assertFileExists($current);
    }

    public function test_it_keeps_the_credentials_and_the_monitors_memory(): void
    {
        // Everything in storage/app that the library needs to work.
        $secret = $this->file(storage_path('app/google-service-account.json'), 400);
        $state = $this->file(storage_path('app/health-state.json'), 400);

        $this->artisan('library:tidy --apply')->assertSuccessful();

        $this->assertFileExists($secret);
        $this->assertFileExists($state);
    }

    public function test_it_keeps_the_backups(): void
    {
        // backup:database keeps a known number of these. Two commands with
        // an opinion about the same files is how a backup goes missing.
        $backup = $this->file(storage_path('backups/database-2020-01-01.sqlite'), 400);

        $this->artisan('library:tidy --apply')->assertSuccessful();

        $this->assertFileExists($backup);
    }

    public function test_it_keeps_the_books_staff_uploaded(): void
    {
        $book = $this->file(storage_path('app/books/a-real-book.pdf'), 400);

        $this->artisan('library:tidy --apply')->assertSuccessful();

        $this->assertFileExists($book);
    }

    public function test_it_removes_old_run_reports_but_not_recent_ones(): void
    {
        $old = $this->file(storage_path('app/lookups-2020-01-01.json'), 400);
        $recent = $this->file(storage_path('app/lookups-2026-08-25.json'), 1);

        $this->artisan('library:tidy --apply')->assertSuccessful();

        $this->assertFileDoesNotExist($old);
        $this->assertFileExists($recent);
    }

    public function test_how_long_to_keep_things_can_be_changed(): void
    {
        $log = $this->file(storage_path('logs/laravel-2026-08-01.log'), 20);

        $this->artisan('library:tidy --apply --days=30')->assertSuccessful();
        $this->assertFileExists($log);

        $this->artisan('library:tidy --apply --days=10')->assertSuccessful();
        $this->assertFileDoesNotExist($log);
    }

    public function test_it_looks_nowhere_but_the_librarys_own_storage(): void
    {
        // The server carries another project. This one names every directory
        // it will look in, and they are all inside here.
        $this->artisan('library:tidy')
            ->expectsOutputToContain(storage_path())
            ->assertSuccessful();
    }
}
