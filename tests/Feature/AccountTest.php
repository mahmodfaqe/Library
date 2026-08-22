<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AccountTest extends TestCase
{
    use RefreshDatabase;

    private const PASSWORD = 'correct-horse-battery-staple';

    private function user(string $role = User::ROLE_STAFF): User
    {
        return User::create([
            'name' => 'Library Staff',
            'email' => 'staff@uor.edu.krd',
            'password' => self::PASSWORD,
            'role' => $role,
        ]);
    }

    public function test_a_guest_cannot_reach_the_account_page(): void
    {
        $this->get('/admin/account')->assertRedirect(route('admin.login'));
    }

    public function test_staff_can_reach_their_own_account(): void
    {
        // Not just administrators — everyone manages their own details.
        $this->actingAs($this->user())->get('/admin/account')->assertOk();
    }

    public function test_a_user_can_correct_their_name(): void
    {
        $user = $this->user();

        $this->actingAs($user)
            ->put('/admin/account', ['name' => 'بەڕێوەبەری کتێبخانە', 'email' => $user->email])
            ->assertRedirect(route('admin.account'));

        $this->assertSame('بەڕێوەبەری کتێبخانە', $user->fresh()->name);
        $this->assertDatabaseHas('activity_log', ['action' => 'account.updated']);
    }

    public function test_an_email_already_in_use_is_refused(): void
    {
        $user = $this->user();
        User::create([
            'name' => 'Someone', 'email' => 'taken@uor.edu.krd',
            'password' => self::PASSWORD, 'role' => User::ROLE_STAFF,
        ]);

        $this->actingAs($user)
            ->from(route('admin.account'))
            ->put('/admin/account', ['name' => 'X', 'email' => 'taken@uor.edu.krd'])
            ->assertSessionHasErrors('email');
    }

    public function test_keeping_your_own_email_is_not_a_conflict(): void
    {
        $user = $this->user();

        $this->actingAs($user)
            ->put('/admin/account', ['name' => 'New Name', 'email' => $user->email])
            ->assertSessionHasNoErrors();
    }

    public function test_a_user_can_change_their_password(): void
    {
        $user = $this->user();

        $this->actingAs($user)
            ->put('/admin/account/password', [
                'current_password' => self::PASSWORD,
                'password' => 'a-brand-new-long-passphrase',
                'password_confirmation' => 'a-brand-new-long-passphrase',
            ])
            ->assertRedirect(route('admin.account'));

        $this->assertTrue(Hash::check('a-brand-new-long-passphrase', $user->fresh()->password));
        $this->assertDatabaseHas('activity_log', ['action' => 'account.password_changed']);
    }

    public function test_the_current_password_must_be_right(): void
    {
        // Otherwise an unattended session could lock the owner out.
        $user = $this->user();

        $this->actingAs($user)
            ->from(route('admin.account'))
            ->put('/admin/account/password', [
                'current_password' => 'wrong',
                'password' => 'a-brand-new-long-passphrase',
                'password_confirmation' => 'a-brand-new-long-passphrase',
            ])
            ->assertSessionHasErrors('current_password');

        $this->assertTrue(Hash::check(self::PASSWORD, $user->fresh()->password));
    }

    public function test_the_new_password_must_be_confirmed_and_long_enough(): void
    {
        $user = $this->user();

        $this->actingAs($user)->from(route('admin.account'))
            ->put('/admin/account/password', [
                'current_password' => self::PASSWORD,
                'password' => 'a-brand-new-long-passphrase',
                'password_confirmation' => 'something-else-entirely',
            ])->assertSessionHasErrors('password');

        $this->actingAs($user)->from(route('admin.account'))
            ->put('/admin/account/password', [
                'current_password' => self::PASSWORD,
                'password' => 'short',
                'password_confirmation' => 'short',
            ])->assertSessionHasErrors('password');

        $this->assertTrue(Hash::check(self::PASSWORD, $user->fresh()->password));
    }
}
