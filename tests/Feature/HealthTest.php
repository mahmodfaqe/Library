<?php

namespace Tests\Feature;

use App\Support\Telegram;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class HealthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        File::deleteDirectory(storage_path('backups'));
        @unlink(storage_path('app/health-state.json'));

        config([
            'services.telegram.token' => 'test-token',
            'services.telegram.chat' => '12345',
        ]);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory(storage_path('backups'));
        @unlink(storage_path('app/health-state.json'));

        parent::tearDown();
    }

    private function backupTakenHoursAgo(int $hours): void
    {
        $dir = storage_path('backups');
        File::ensureDirectoryExists($dir);

        $file = $dir.'/database-test.sqlite';
        file_put_contents($file, 'x');
        touch($file, time() - $hours * 3600);
    }

    public function test_a_well_service_answers_that_it_is_well(): void
    {
        $this->backupTakenHoursAgo(2);

        $this->get('/health')
            ->assertOk()
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('checks.database.ok', true)
            ->assertJsonPath('checks.backup.ok', true);
    }

    public function test_a_backup_that_stopped_running_fails_the_check(): void
    {
        // The point of the check: backups stop quietly, and nobody notices
        // until the day they are needed.
        $this->backupTakenHoursAgo(40);

        $this->get('/health')
            ->assertStatus(503)
            ->assertJsonPath('status', 'failing')
            ->assertJsonPath('checks.backup.ok', false);
    }

    public function test_a_backup_that_never_ran_fails_the_check(): void
    {
        $this->get('/health')
            ->assertStatus(503)
            ->assertJsonPath('checks.backup.ok', false)
            ->assertJsonPath('checks.backup.detail', 'never run');
    }

    public function test_it_gives_a_monitor_no_reason_to_cache_the_answer(): void
    {
        $this->backupTakenHoursAgo(1);

        $header = $this->get('/health')->headers->get('Cache-Control');

        $this->assertStringContainsString('no-store', (string) $header);
    }

    public function test_the_answer_names_no_paths_or_secrets(): void
    {
        $this->backupTakenHoursAgo(1);

        $body = $this->get('/health')->getContent();

        // It is public, because only a monitor outside the server can tell you
        // the server has gone.
        $this->assertStringNotContainsString(base_path(), $body);
        $this->assertStringNotContainsString(storage_path(), $body);
        $this->assertStringNotContainsString(config('database.connections.mysql.password') ?: '@@none@@', $body);
    }

    public function test_up_stays_simple_so_docker_does_not_restart_on_a_stale_backup(): void
    {
        // /up is the container's own healthcheck. If a backup that has not run
        // for two days made it fail, Docker would restart the site over and
        // over for a problem restarting cannot fix.
        $this->get('/up')->assertOk();
    }

    public function test_it_sends_word_when_something_breaks(): void
    {
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => true])]);

        $this->artisan('health:report')->assertSuccessful();

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'sendMessage')
                && str_contains($request['text'], 'backup');
        });
    }

    public function test_it_does_not_say_the_same_thing_every_hour(): void
    {
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => true])]);

        $this->artisan('health:report')->assertSuccessful();
        Http::assertSentCount(1);

        // Nothing has changed, so there is nothing to say. An alert that
        // arrives hourly for a week is one nobody reads by the end of it.
        $this->artisan('health:report')->assertSuccessful();
        Http::assertSentCount(1);
    }

    public function test_it_says_so_when_the_trouble_is_over(): void
    {
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => true])]);

        $this->artisan('health:report');
        $this->backupTakenHoursAgo(1);
        $this->artisan('health:report');

        Http::assertSentCount(2);
        Http::assertSent(fn ($request) => str_contains($request['text'], 'well again'));
    }

    public function test_a_healthy_service_says_nothing_on_the_first_run(): void
    {
        Http::fake();
        $this->backupTakenHoursAgo(1);

        $this->artisan('health:report')->assertSuccessful();

        Http::assertNothingSent();
    }

    public function test_a_machine_with_no_telegram_configured_stays_quiet(): void
    {
        Http::fake();
        config(['services.telegram.token' => null, 'services.telegram.chat' => null]);

        $this->assertFalse(Telegram::configured());
        $this->assertFalse(Telegram::send('anything'));

        Http::assertNothingSent();
    }

    public function test_telegram_being_down_does_not_take_the_library_with_it(): void
    {
        // An alert that breaks the thing it was reporting on is worse than no
        // alert at all.
        Http::fake(['api.telegram.org/*' => fn () => throw new \RuntimeException('network is unreachable')]);

        $this->assertFalse(Telegram::send('the disk is full'));

        $this->artisan('health:report')->assertSuccessful();
    }
}
