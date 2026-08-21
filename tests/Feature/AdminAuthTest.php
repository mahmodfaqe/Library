<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AdminAuthTest extends TestCase
{
    use RefreshDatabase;

    private const PASSWORD = 'correct-horse-battery-staple';

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.admin_password_hash' => Hash::make(self::PASSWORD)]);
    }

    public static function guardedRoutes(): array
    {
        return [
            ['get', '/admin'],
            ['get', '/admin/departments/create'],
            ['get', '/admin/feedback'],
        ];
    }

    #[DataProvider('guardedRoutes')]
    public function test_a_guest_is_sent_to_the_login_page(string $method, string $uri): void
    {
        $this->$method($uri)->assertRedirect(route('admin.login'));
    }

    public function test_the_login_page_is_public(): void
    {
        $this->get('/admin/login')
            ->assertOk()
            ->assertSee(__('admin.login.heading', [], 'ku-sorani'), false);
    }

    public function test_the_correct_password_signs_the_admin_in(): void
    {
        $this->post('/admin/login', ['password' => self::PASSWORD])
            ->assertRedirect(route('admin.index'))
            ->assertSessionHas('admin_authenticated', true);
    }

    public function test_a_wrong_password_is_rejected(): void
    {
        $this->from(route('admin.login'))
            ->post('/admin/login', ['password' => 'wrong'])
            ->assertRedirect(route('admin.login'))
            ->assertSessionHasErrors('password')
            ->assertSessionMissing('admin_authenticated');
    }

    public function test_no_password_signs_anyone_in_when_no_hash_is_configured(): void
    {
        config(['app.admin_password_hash' => '']);

        $this->from(route('admin.login'))
            ->post('/admin/login', ['password' => ''])
            ->assertSessionMissing('admin_authenticated');
    }

    public function test_logging_out_clears_the_session_flag(): void
    {
        $this->withSession(['admin_authenticated' => true])
            ->post('/admin/logout')
            ->assertRedirect(route('home'))
            ->assertSessionMissing('admin_authenticated');
    }

    public function test_the_login_form_is_rate_limited(): void
    {
        foreach (range(1, 5) as $attempt) {
            $this->post('/admin/login', ['password' => 'wrong'])->assertRedirect();
        }

        $this->post('/admin/login', ['password' => 'wrong'])->assertStatus(429);
    }

    public function test_the_admin_pages_are_not_indexable(): void
    {
        $this->get('/admin/login')->assertSee('noindex, nofollow', false);
    }
}
