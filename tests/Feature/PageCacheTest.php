<?php

namespace Tests\Feature;

use App\Models\Department;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageCacheTest extends TestCase
{
    use RefreshDatabase;

    private string $dir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dir = storage_path('framework/pagecache');
        $this->clearCacheDir();
    }

    protected function tearDown(): void
    {
        $this->clearCacheDir();

        parent::tearDown();
    }

    private function clearCacheDir(): void
    {
        foreach (glob($this->dir.'/*') ?: [] as $file) {
            @unlink($file);
        }
    }

    public function test_the_first_request_misses_and_the_next_one_hits(): void
    {
        $this->withSession(['locale' => 'en'])->get('/')->assertHeader('X-Page-Cache', 'MISS');
        $this->withSession(['locale' => 'en'])->get('/')->assertHeader('X-Page-Cache', 'HIT');

        $this->assertFileExists($this->dir.'/home-en.html');
    }

    public function test_a_returning_visitor_with_a_session_still_gets_the_cache(): void
    {
        $this->withSession(['locale' => 'en'])->get('/');

        // The old middleware bypassed the cache for anyone carrying a cookie,
        // which meant every visitor past their first request re-rendered.
        $this->withSession(['locale' => 'en', 'some_other_key' => 'value'])
            ->get('/')
            ->assertHeader('X-Page-Cache', 'HIT');
    }

    public function test_each_locale_gets_its_own_copy(): void
    {
        $this->withSession(['locale' => 'en'])->get('/')->assertHeader('X-Page-Cache', 'MISS');
        $this->withSession(['locale' => 'ar'])->get('/')->assertHeader('X-Page-Cache', 'MISS');

        $this->assertFileExists($this->dir.'/home-en.html');
        $this->assertFileExists($this->dir.'/home-ar.html');

        $this->withSession(['locale' => 'ar'])
            ->get('/')
            ->assertHeader('X-Page-Cache', 'HIT')
            ->assertSee(__('messages.hero.title', [], 'ar'), false);
    }

    public function test_a_cached_page_is_never_served_in_the_wrong_language(): void
    {
        $this->withSession(['locale' => 'en'])->get('/');

        $this->withSession(['locale' => 'tr'])
            ->get('/')
            ->assertSee(__('messages.hero.title', [], 'tr'), false)
            ->assertDontSee(__('messages.hero.title', [], 'en'), false);
    }

    public function test_a_feedback_confirmation_is_neither_served_from_nor_written_to_the_cache(): void
    {
        $this->withSession(['locale' => 'en', 'feedback_sent' => true])
            ->get('/')
            ->assertHeaderMissing('X-Page-Cache')
            ->assertSee(__('messages.feedback.success', [], 'en'));

        // A confirmation shown to one visitor must not end up in the shared copy.
        $this->assertFileDoesNotExist($this->dir.'/home-en.html');
    }

    public function test_the_admin_neither_reads_nor_warms_the_cache(): void
    {
        $this->withSession(['locale' => 'en', 'admin_authenticated' => true])->get('/')->assertOk();

        $this->assertFileDoesNotExist($this->dir.'/home-en.html');
    }

    public function test_a_stale_copy_is_ignored_once_it_expires(): void
    {
        $this->withSession(['locale' => 'en'])->get('/');

        touch($this->dir.'/home-en.html', time() - 3601);

        $this->withSession(['locale' => 'en'])->get('/')->assertHeader('X-Page-Cache', 'MISS');
    }

    public function test_a_cached_page_carries_the_current_visitors_csrf_token(): void
    {
        $this->withSession(['locale' => 'en'])->get('/');

        $response = $this->withSession(['locale' => 'en'])->get('/');
        $response->assertHeader('X-Page-Cache', 'HIT');

        $this->assertStringContainsString(
            'value="'.session()->token().'"',
            $response->getContent()
        );
    }

    public function test_a_department_added_after_the_cache_was_warmed_is_not_shown_until_it_is_cleared(): void
    {
        $this->withSession(['locale' => 'en'])->get('/');

        Department::create([
            'sort_order' => 1,
            'icon' => '🧬',
            'drive_url' => 'https://drive.google.com/drive/folders/abc',
            'translations' => ['en' => ['title' => 'Biology', 'desc' => 'd', 'button' => 'b']],
        ]);

        $this->withSession(['locale' => 'en'])->get('/')->assertDontSee('Biology');

        $this->clearCacheDir();

        $this->withSession(['locale' => 'en'])->get('/')->assertSee('Biology');
    }

    public function test_only_the_home_page_is_cached(): void
    {
        $this->get('/admin/login')->assertHeaderMissing('X-Page-Cache');

        $this->assertSame([], glob($this->dir.'/*') ?: []);
    }
}
