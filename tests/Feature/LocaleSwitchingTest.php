<?php

namespace Tests\Feature;

use App\Http\Middleware\SetLocale;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocaleSwitchingTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_stores_a_supported_locale_in_the_session(): void
    {
        $this->from('/')
            ->get('/language?locale=tr')
            ->assertRedirect('/')
            ->assertSessionHas('locale', 'tr');
    }

    public function test_it_ignores_an_unsupported_locale(): void
    {
        $this->withSession(['locale' => 'en'])
            ->from('/')
            ->get('/language?locale=de')
            ->assertRedirect('/')
            ->assertSessionHas('locale', 'en');
    }

    public function test_it_ignores_a_missing_locale(): void
    {
        $this->from('/')
            ->get('/language')
            ->assertRedirect('/')
            ->assertSessionMissing('locale');
    }

    public function test_every_advertised_locale_is_accepted(): void
    {
        foreach (SetLocale::LOCALES as $locale) {
            $this->from('/')
                ->get("/language?locale=$locale")
                ->assertSessionHas('locale', $locale);
        }
    }

    public function test_the_stored_locale_drives_the_rendered_page(): void
    {
        $this->withSession(['locale' => 'ar'])
            ->get('/')
            ->assertOk()
            ->assertSee(__('messages.hero.title', [], 'ar'), false);
    }
}
