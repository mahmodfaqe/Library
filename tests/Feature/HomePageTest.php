<?php

namespace Tests\Feature;

use App\Http\Middleware\SetLocale;
use App\Models\Department;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    use RefreshDatabase;

    public static function locales(): array
    {
        return array_map(fn (string $l) => [$l], SetLocale::LOCALES);
    }

    #[DataProvider('locales')]
    public function test_the_home_page_renders_in_every_locale(string $locale): void
    {
        $this->withSession(['locale' => $locale])
            ->get('/')
            ->assertOk()
            ->assertSee(__('messages.hero.title', [], $locale), false)
            ->assertSee(__('messages.intro.heading', [], $locale), false);
    }

    #[DataProvider('locales')]
    public function test_no_translation_key_leaks_into_the_page(string $locale): void
    {
        $html = $this->withSession(['locale' => $locale])->get('/')->getContent();

        // A missing key renders as the key itself, e.g. "messages.hero.title".
        $this->assertDoesNotMatchRegularExpression('/\bmessages\.[a-z_]+/', $html);
        $this->assertStringNotContainsString('admin.', $html);
    }

    public function test_it_defaults_to_kurdish_sorani(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee(__('messages.hero.title', [], 'ku-sorani'), false)
            ->assertSee('<html lang="ku" dir="rtl"', false);
    }

    public function test_latin_locales_render_left_to_right(): void
    {
        $this->withSession(['locale' => 'en'])
            ->get('/')
            ->assertSee('<html lang="en" dir="ltr"', false)
            ->assertSee('footer-year-en', false);
    }

    public function test_the_opening_date_is_emphasised_inside_the_history_paragraph(): void
    {
        $this->withSession(['locale' => 'en'])
            ->get('/')
            ->assertSee('<strong>'.__('messages.history.opening_date', [], 'en').'</strong>', false);
    }

    public function test_the_qr_objective_renders_as_a_link(): void
    {
        $this->withSession(['locale' => 'en'])
            ->get('/')
            ->assertSee('https://scence-bio.github.io/Qr-Code/', false)
            ->assertSee('>'.__('messages.intro.qr_label', [], 'en').'</a>', false);
    }

    public function test_it_lists_the_departments_in_sort_order(): void
    {
        Department::create([
            'sort_order' => 2,
            'icon' => '🧪',
            'drive_url' => 'https://drive.google.com/drive/folders/second',
            'translations' => ['en' => ['title' => 'Chemistry', 'desc' => 'Chem desc', 'button' => 'Open']],
        ]);
        Department::create([
            'sort_order' => 1,
            'icon' => '🧬',
            'drive_url' => 'https://drive.google.com/drive/folders/first',
            'translations' => ['en' => ['title' => 'Biology', 'desc' => 'Bio desc', 'button' => 'Open']],
        ]);

        $html = $this->withSession(['locale' => 'en'])->get('/')->assertOk()->getContent();

        $this->assertLessThan(strpos($html, 'Chemistry'), strpos($html, 'Biology'));
        $this->assertStringContainsString('https://drive.google.com/drive/folders/first', $html);
    }

    public function test_it_shows_the_empty_state_when_there_are_no_departments(): void
    {
        $this->withSession(['locale' => 'en'])
            ->get('/')
            ->assertSee(__('messages.no_departments', [], 'en'));
    }
}
