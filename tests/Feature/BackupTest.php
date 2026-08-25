<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Support\BackupCipher;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class BackupTest extends TestCase
{
    private string $database;

    /**
     * The rest of the suite runs against an in-memory database, which cannot
     * be backed up — there is no file to copy. These tests need a real one, so
     * that what they exercise is the code that will run at half past two in
     * the morning rather than a stand-in for it.
     */
    protected function setUp(): void
    {
        $this->database = tempnam(sys_get_temp_dir(), 'library-test-').'.sqlite';
        touch($this->database);

        $_ENV['DB_DATABASE'] = $_SERVER['DB_DATABASE'] = $this->database;
        putenv('DB_DATABASE='.$this->database);

        parent::setUp();

        // Migrated by hand rather than by RefreshDatabase, which wraps each
        // test in a transaction — and a backup cannot be taken from inside
        // one. The file is new for every test, so there is nothing to undo.
        $this->artisan('migrate', ['--force' => true]);

        File::deleteDirectory(storage_path('backups'));
        config([
            'services.telegram.token' => 'test-token',
            'services.telegram.chat' => '12345',
        ]);
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => true])]);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory(storage_path('backups'));

        parent::tearDown();

        @unlink($this->database);
        $_ENV['DB_DATABASE'] = $_SERVER['DB_DATABASE'] = ':memory:';
        putenv('DB_DATABASE=:memory:');
    }

    private function aBookInTheLibrary(): void
    {
        Book::create([
            'title' => 'Molecular Biology of the Cell',
            'url' => 'https://drive.test/abc',
        ]);
    }

    private function backups(): array
    {
        return array_map(
            fn ($file) => $file->getPathname(),
            File::files(storage_path('backups'))
        );
    }

    public function test_it_writes_a_backup_of_the_library(): void
    {
        $this->aBookInTheLibrary();

        $this->artisan('backup:database')->assertSuccessful();

        $this->assertCount(1, $this->backups());
    }

    public function test_a_backup_that_leaves_the_server_is_locked(): void
    {
        // It holds staff accounts and visitors' messages, and the whole point
        // of it is to be copied somewhere else.
        config(['library.backup_key' => 'a-key-kept-in-a-password-manager']);
        $this->aBookInTheLibrary();

        $this->artisan('backup:database')->assertSuccessful();

        $file = $this->backups()[0];
        $this->assertStringEndsWith('.enc', $file);

        $raw = (string) file_get_contents($file);
        $this->assertStringNotContainsString('Molecular Biology', $raw);
    }

    public function test_it_says_plainly_when_a_backup_is_not_locked(): void
    {
        config(['library.backup_key' => '']);

        $this->artisan('backup:database')
            ->expectsOutputToContain('BACKUP_KEY is not set')
            ->assertSuccessful();
    }

    public function test_the_lock_is_one_any_machine_can_open(): void
    {
        // The day a backup is needed, this application is gone. Getting the
        // data back must not depend on it, so the format is the one the
        // openssl command line reads:
        //   openssl enc -d -aes-256-cbc -pbkdf2 -in f.enc -pass pass:KEY
        $sealed = BackupCipher::encrypt('the library', 'the-key');

        $this->assertStringStartsWith('Salted__', $sealed);
        $this->assertSame('the library', BackupCipher::decrypt($sealed, 'the-key'));
    }

    public function test_the_wrong_key_opens_nothing(): void
    {
        $sealed = BackupCipher::encrypt('the library', 'the-key');

        $this->expectException(\RuntimeException::class);

        BackupCipher::decrypt($sealed, 'not-the-key');
    }

    public function test_it_opens_the_backup_and_finds_the_library_inside(): void
    {
        $this->aBookInTheLibrary();
        $this->artisan('backup:database');

        $this->artisan('backup:verify')
            ->expectsOutputToContain('holds 1 books')
            ->assertSuccessful();
    }

    public function test_it_opens_a_locked_backup_too(): void
    {
        config(['library.backup_key' => 'a-key']);
        $this->aBookInTheLibrary();
        $this->artisan('backup:database');

        $this->artisan('backup:verify')
            ->expectsOutputToContain('decrypted')
            ->assertSuccessful();
    }

    public function test_a_backup_with_no_books_in_it_is_not_a_backup(): void
    {
        // It restores perfectly and is still a disaster.
        $this->artisan('backup:database');

        $this->artisan('backup:verify')->assertFailed();
    }

    public function test_a_damaged_backup_is_caught_and_reported(): void
    {
        $this->aBookInTheLibrary();
        $this->artisan('backup:database');

        file_put_contents($this->backups()[0], 'this is not a database');

        $this->artisan('backup:verify')->assertFailed();

        Http::assertSent(fn ($request) => str_contains($request['text'], 'did not verify'));
    }

    public function test_having_no_backup_at_all_is_reported(): void
    {
        $this->artisan('backup:verify')->assertFailed();

        Http::assertSent(fn ($request) => str_contains($request['text'], 'no backups'));
    }

    public function test_a_locked_backup_with_no_key_to_hand_is_reported(): void
    {
        config(['library.backup_key' => 'a-key']);
        $this->aBookInTheLibrary();
        $this->artisan('backup:database');

        // The key was lost, which is the same as having no backup.
        config(['library.backup_key' => '']);

        $this->artisan('backup:verify')->assertFailed();
    }

    public function test_old_copies_are_pruned_but_recent_ones_are_kept(): void
    {
        $dir = storage_path('backups');
        File::ensureDirectoryExists($dir);

        foreach (range(1, 5) as $day) {
            $file = $dir."/database-old-{$day}.sqlite";
            file_put_contents($file, 'x');
            touch($file, time() - $day * 86400);
        }

        $this->aBookInTheLibrary();
        $this->artisan('backup:database --keep=3')->assertSuccessful();

        $this->assertCount(3, $this->backups());
    }
}
