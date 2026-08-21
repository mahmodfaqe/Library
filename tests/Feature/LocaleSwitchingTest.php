<?php

namespace Tests\Feature;

use App\Support\Locale;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class LocaleSwitchingTest extends TestCase
{
    use RefreshDatabase;

    public static function locales(): array
    {
        return array_map(fn (string $l) => [$l], Locale::SUPPORTED);
    }

    #[DataProvider('locales')]
    public function test_the_url_decides_the_language(string $locale): void
    {
        $this->get($this->homeUrl($locale))
            ->assertOk()
            ->assertSee(__('messages.hero.title', [], $locale), false);
    }

    #[DataProvider('locales')]
    public function test_the_switcher_links_to_every_other_language(string $locale): void
    {
        $response = $this->get($this->homeUrl($locale))->assertOk();

        foreach (Locale::SUPPORTED as $target) {
            $response->assertSee('href="'.Locale::url($target).'"', false);
        }
    }

    public function test_visiting_a_localised_url_remembers_the_choice(): void
    {
        // The admin panel has no locale prefix, so it reads the remembered one.
        $this->get('/fa')->assertSessionHas('locale', 'fa');
    }

    public function test_a_page_without_a_prefix_uses_the_remembered_locale(): void
    {
        $this->withSession(['locale' => 'tr'])
            ->get('/admin/login')
            ->assertOk()
            ->assertSee(__('admin.login.heading', [], 'tr'), false);
    }

    public function test_a_page_without_a_prefix_falls_back_to_the_default_locale(): void
    {
        $this->get('/admin/login')
            ->assertOk()
            ->assertSee(__('admin.login.heading', [], 'ku-sorani'), false);
    }

    public function test_a_nonsense_locale_in_the_session_is_ignored(): void
    {
        $this->withSession(['locale' => 'de'])
            ->get('/admin/login')
            ->assertOk()
            ->assertSee(__('admin.login.heading', [], 'ku-sorani'), false);
    }

    public function test_the_old_query_string_switcher_still_works(): void
    {
        $this->get('/language?locale=ar')->assertRedirect(url('/ar'))->assertStatus(301);
        $this->get('/language?locale=ku-sorani')->assertRedirect(url('/'))->assertStatus(301);
    }

    public function test_the_old_switcher_ignores_an_unsupported_locale(): void
    {
        $this->get('/language?locale=de')->assertRedirect(url('/'));
        $this->get('/language')->assertRedirect(url('/'));
    }
}
