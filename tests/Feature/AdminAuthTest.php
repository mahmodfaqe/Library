<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AdminAuthTest extends TestCase
{
    use RefreshDatabase;

    private const PASSWORD = 'correct-horse-battery-staple';

    private function admin(array $overrides = []): User
    {
        return User::create(array_replace([
            'name' => 'Library Administrator',
            'email' => 'admin@uor.edu.krd',
            'password' => self::PASSWORD,
            'role' => User::ROLE_ADMIN,
        ], $overrides));
    }

    private function staff(): User
    {
        return $this->admin([
            'email' => 'staff@uor.edu.krd',
            'name' => 'Library Staff',
            'role' => User::ROLE_STAFF,
        ]);
    }

    public static function guardedRoutes(): array
    {
        return [
            ['get', '/admin'],
            ['get', '/admin/departments/create'],
            ['get', '/admin/feedback'],
            ['get', '/admin/users'],
            ['get', '/admin/activity'],
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

    public function test_the_right_credentials_sign_the_user_in(): void
    {
        $user = $this->admin();

        $this->post('/admin/login', ['email' => $user->email, 'password' => self::PASSWORD])
            ->assertRedirect(route('admin.index'));

        $this->assertAuthenticatedAs($user);
        $this->assertNotNull($user->fresh()->last_login_at);
    }

    public function test_a_wrong_password_is_rejected(): void
    {
        $user = $this->admin();

        $this->from(route('admin.login'))
            ->post('/admin/login', ['email' => $user->email, 'password' => 'wrong'])
            ->assertRedirect(route('admin.login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_an_unknown_email_is_rejected(): void
    {
        $this->from(route('admin.login'))
            ->post('/admin/login', ['email' => 'nobody@uor.edu.krd', 'password' => self::PASSWORD])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_a_failed_attempt_is_recorded(): void
    {
        $this->post('/admin/login', ['email' => 'nobody@uor.edu.krd', 'password' => 'wrong']);

        $this->assertDatabaseHas('activity_log', ['action' => 'auth.failed', 'subject' => 'nobody@uor.edu.krd']);
    }

    public function test_signing_in_and_out_is_recorded(): void
    {
        $user = $this->admin();

        $this->post('/admin/login', ['email' => $user->email, 'password' => self::PASSWORD]);
        $this->post('/admin/logout');

        $this->assertGuest();
        $this->assertSame(['auth.signed_in', 'auth.signed_out'],
            Activity::orderBy('id')->pluck('action')->all());
    }

    public function test_the_login_form_is_rate_limited(): void
    {
        foreach (range(1, 5) as $attempt) {
            $this->post('/admin/login', ['email' => 'nobody@uor.edu.krd', 'password' => 'wrong'])->assertRedirect();
        }

        $this->post('/admin/login', ['email' => 'nobody@uor.edu.krd', 'password' => 'wrong'])->assertStatus(429);
    }

    public function test_staff_may_manage_content(): void
    {
        $staff = $this->staff();

        $this->actingAs($staff)->get('/admin')->assertOk();
        $this->actingAs($staff)->get('/admin/feedback')->assertOk();
    }

    public function test_staff_may_not_reach_accounts_or_the_audit_trail(): void
    {
        $staff = $this->staff();

        $this->actingAs($staff)->get('/admin/users')->assertForbidden();
        $this->actingAs($staff)->get('/admin/activity')->assertForbidden();
    }

    public function test_an_administrator_reaches_accounts_and_the_audit_trail(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->get('/admin/users')->assertOk();
        $this->actingAs($admin)->get('/admin/activity')->assertOk();
    }

    public function test_the_admin_pages_are_not_indexable(): void
    {
        $this->get('/admin/login')->assertSee('noindex, nofollow', false);
    }
}
