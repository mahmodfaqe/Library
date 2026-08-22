<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    private function admin(string $email = 'admin@uor.edu.krd'): User
    {
        return User::create([
            'name' => 'Library Administrator',
            'email' => $email,
            'password' => 'correct-horse-battery-staple',
            'role' => User::ROLE_ADMIN,
        ]);
    }

    public function test_an_administrator_can_add_an_account(): void
    {
        $this->actingAs($this->admin())
            ->post('/admin/users', [
                'name' => 'New Librarian',
                'email' => 'librarian@uor.edu.krd',
                'password' => 'a-long-enough-passphrase',
                'role' => User::ROLE_STAFF,
            ])
            ->assertRedirect(route('admin.users'));

        $user = User::where('email', 'librarian@uor.edu.krd')->firstOrFail();

        $this->assertSame(User::ROLE_STAFF, $user->role);
        $this->assertTrue(Hash::check('a-long-enough-passphrase', $user->password));
        $this->assertDatabaseHas('activity_log', ['action' => 'user.created', 'subject' => $user->email]);
    }

    public function test_a_short_password_is_refused(): void
    {
        $this->actingAs($this->admin())
            ->from(route('admin.users'))
            ->post('/admin/users', [
                'name' => 'New Librarian',
                'email' => 'librarian@uor.edu.krd',
                'password' => 'short',
                'role' => User::ROLE_STAFF,
            ])
            ->assertSessionHasErrors('password');

        $this->assertDatabaseCount('users', 1);
    }

    public function test_an_invented_role_is_refused(): void
    {
        $this->actingAs($this->admin())
            ->from(route('admin.users'))
            ->post('/admin/users', [
                'name' => 'New Librarian',
                'email' => 'librarian@uor.edu.krd',
                'password' => 'a-long-enough-passphrase',
                'role' => 'superuser',
            ])
            ->assertSessionHasErrors('role');

        $this->assertDatabaseCount('users', 1);
    }

    public function test_an_email_cannot_be_reused(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->from(route('admin.users'))
            ->post('/admin/users', [
                'name' => 'Impostor',
                'email' => $admin->email,
                'password' => 'a-long-enough-passphrase',
                'role' => User::ROLE_STAFF,
            ])
            ->assertSessionHasErrors('email');

        $this->assertDatabaseCount('users', 1);
    }

    public function test_the_last_administrator_cannot_be_deleted(): void
    {
        // Otherwise the panel locks everybody out permanently.
        $admin = $this->admin();
        $second = $this->admin('second@uor.edu.krd');
        $second->update(['role' => User::ROLE_STAFF]);

        $this->actingAs($second)->delete("/admin/users/{$admin->id}")->assertForbidden();

        $this->actingAs($admin)->delete("/admin/users/{$second->id}");
        $this->assertDatabaseMissing('users', ['id' => $second->id]);

        $this->actingAs($admin)
            ->delete("/admin/users/{$admin->id}")
            ->assertSessionHasErrors('user');

        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_an_administrator_cannot_delete_their_own_account(): void
    {
        $admin = $this->admin();
        $this->admin('second@uor.edu.krd');

        $this->actingAs($admin)
            ->delete("/admin/users/{$admin->id}")
            ->assertSessionHasErrors('user');

        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_deleting_an_account_is_recorded(): void
    {
        $admin = $this->admin();
        $other = $this->admin('other@uor.edu.krd');

        $this->actingAs($admin)->delete("/admin/users/{$other->id}");

        $this->assertDatabaseHas('activity_log', ['action' => 'user.deleted', 'subject' => 'other@uor.edu.krd']);
    }

    public function test_the_audit_trail_survives_the_account_being_deleted(): void
    {
        $admin = $this->admin();
        $other = $this->admin('other@uor.edu.krd');

        $this->actingAs($other)->post('/admin/users', [
            'name' => 'Someone', 'email' => 'someone@uor.edu.krd',
            'password' => 'a-long-enough-passphrase', 'role' => User::ROLE_STAFF,
        ]);

        $this->actingAs($admin)->delete("/admin/users/{$other->id}");

        // The name stays on the record even though the account is gone.
        $this->assertDatabaseHas('activity_log', [
            'action' => 'user.created',
            'subject' => 'someone@uor.edu.krd',
            'actor_name' => 'Library Administrator',
            'user_id' => null,
        ]);
    }
}
