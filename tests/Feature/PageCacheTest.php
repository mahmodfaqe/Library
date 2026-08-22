<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\User;
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

    /**
     * The cached copy for a locale, whatever source stamp it carries.
     */
    private function cachedCopies(string $locale): array
    {
        return glob($this->dir."/home-$locale-*.html") ?: [];
    }

    public function test_a_cached_page_is_never_served_to_a_different_host(): void
    {
        // The page is full of absolute URLs — stylesheet, fonts, icons,
        // canonical, hreflang. A copy warmed on one host once leaked all of
        // them to visitors arriving on another.
        $this->get('http://127.0.0.1/en')->assertOk();

        $html = $this->get('http://localhost/en')->assertOk()->getContent();

        $this->assertStringNotContainsString('http://127.0.0.1', $html);
        $this->assertStringContainsString('http://localhost/favicon.ico', $html);
    }

    public function test_each_host_keeps_its_own_copy(): void
    {
        $this->get('http://127.0.0.1/en');
        $this->get('http://localhost/en');

        $this->assertCount(2, $this->cachedCopies('en'));

        // And each host still serves its own copy from cache.
        $this->get('http://127.0.0.1/en')->assertHeader('X-Page-Cache', 'HIT');
        $this->get('http://localhost/en')->assertHeader('X-Page-Cache', 'HIT');
    }

    private function clearCacheDir(): void
    {
        foreach (glob($this->dir.'/*') ?: [] as $file) {
            @unlink($file);
        }
    }

    public function test_the_first_request_misses_and_the_next_one_hits(): void
    {
        $this->get('/en')->assertHeader('X-Page-Cache', 'MISS');
        $this->get('/en')->assertHeader('X-Page-Cache', 'HIT');

        $this->assertCount(1, $this->cachedCopies('en'));
    }

    public function test_a_returning_visitor_with_a_session_still_gets_the_cache(): void
    {
        $this->get('/en');

        // The old middleware bypassed the cache for anyone carrying a cookie,
        // which meant every visitor past their first request re-rendered.
        $this->withSession(['some_other_key' => 'value'])
            ->get('/en')
            ->assertHeader('X-Page-Cache', 'HIT');
    }

    public function test_each_locale_gets_its_own_copy(): void
    {
        $this->get('/en')->assertHeader('X-Page-Cache', 'MISS');
        $this->get('/ar')->assertHeader('X-Page-Cache', 'MISS');

        $this->assertCount(1, $this->cachedCopies('en'));
        $this->assertCount(1, $this->cachedCopies('ar'));

        $this->get('/ar')
            ->assertHeader('X-Page-Cache', 'HIT')
            ->assertSee(__('messages.hero.title', [], 'ar'), false);
    }

    public function test_a_cached_page_is_never_served_in_the_wrong_language(): void
    {
        $this->get('/en');

        $this->get('/tr')
            ->assertSee(__('messages.hero.title', [], 'tr'), false)
            ->assertDontSee(__('messages.hero.title', [], 'en'), false);
    }

    public function test_a_feedback_confirmation_is_neither_served_from_nor_written_to_the_cache(): void
    {
        $this->withSession(['feedback_sent' => true])
            ->get('/en')
            ->assertHeaderMissing('X-Page-Cache')
            ->assertSee(__('messages.feedback.success', [], 'en'));

        // A confirmation shown to one visitor must not end up in the shared copy.
        $this->assertSame([], $this->cachedCopies('en'));
    }

    public function test_the_admin_neither_reads_nor_warms_the_cache(): void
    {
        $this->actingAs(User::create([
            'name' => 'Library Administrator',
            'email' => 'admin@uor.edu.krd',
            'password' => 'correct-horse-battery-staple',
            'role' => User::ROLE_ADMIN,
        ]))->get('/en')->assertOk();

        $this->assertSame([], $this->cachedCopies('en'));
    }

    public function test_a_stale_copy_is_ignored_once_it_expires(): void
    {
        $this->get('/en');

        touch($this->cachedCopies('en')[0], time() - 3601);

        $this->get('/en')->assertHeader('X-Page-Cache', 'MISS');
    }

    public function test_a_cached_page_carries_the_current_visitors_csrf_token(): void
    {
        $this->get('/en');

        $response = $this->get('/en');
        $response->assertHeader('X-Page-Cache', 'HIT');

        $this->assertStringContainsString(
            'value="'.session()->token().'"',
            $response->getContent()
        );
    }

    public function test_a_department_added_after_the_cache_was_warmed_is_not_shown_until_it_is_cleared(): void
    {
        $this->get('/en');

        // A title that appears nowhere else on the page, so seeing it can only
        // mean the department list was re-rendered.
        Department::create([
            'sort_order' => 1,
            'icon' => '🔭',
            'drive_url' => 'https://drive.google.com/drive/folders/abc',
            'translations' => ['en' => ['title' => 'Astrophysics', 'desc' => 'd', 'button' => 'b']],
        ]);

        $this->get('/en')->assertDontSee('Astrophysics');

        $this->clearCacheDir();

        $this->get('/en')->assertSee('Astrophysics');
    }

    public function test_only_the_home_page_is_cached(): void
    {
        $this->get('/admin/login')->assertHeaderMissing('X-Page-Cache');

        $this->assertSame([], glob($this->dir.'/*') ?: []);
    }
}
