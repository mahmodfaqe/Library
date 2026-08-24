<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDepartmentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::create([
            'name' => 'Library Staff',
            'email' => 'staff@uor.edu.krd',
            'password' => 'correct-horse-battery-staple',
            'role' => User::ROLE_STAFF,
        ]));
    }

    /**
     * A full set of translations, as the form requires every configured locale.
     */
    private function payload(array $overrides = []): array
    {
        $translations = [];

        foreach (config('departments.locales') as $locale) {
            $translations[$locale['lang']] = [
                'title' => 'Title '.$locale['lang'],
                'desc' => 'Description '.$locale['lang'],
                'button' => 'Button '.$locale['lang'],
            ];
        }

        return array_replace([
            'sort_order' => 1,
            'icon' => '🧬',
            'drive_url' => 'https://drive.google.com/drive/folders/abc',
            'translations' => $translations,
        ], $overrides);
    }

    public function test_an_admin_can_create_a_department(): void
    {
        $this->post('/admin/departments', $this->payload())
            ->assertRedirect(route('admin.index'))
            ->assertSessionHas('status');

        $this->assertDatabaseCount('departments', 1);
        $this->assertSame('Title en', Department::first()->translation('en', 'title'));
    }

    public function test_an_admin_can_update_a_department(): void
    {
        $department = Department::create($this->payload());

        $this->put("/admin/departments/{$department->id}", $this->payload(['sort_order' => 9]))
            ->assertRedirect(route('admin.index'));

        $this->assertSame(9, $department->fresh()->sort_order);
    }

    public function test_an_admin_can_delete_a_department(): void
    {
        $department = Department::create($this->payload());

        $this->delete("/admin/departments/{$department->id}")
            ->assertRedirect(route('admin.index'));

        $this->assertDatabaseCount('departments', 0);
    }

    public function test_a_department_needs_a_valid_drive_url(): void
    {
        $this->from(route('admin.departments.create'))
            ->post('/admin/departments', $this->payload(['drive_url' => 'not-a-url']))
            ->assertSessionHasErrors('drive_url');

        $this->assertDatabaseCount('departments', 0);
    }

    public function test_a_department_needs_a_translation_for_every_locale(): void
    {
        $payload = $this->payload();
        unset($payload['translations']['ku-hawrami']);

        $this->from(route('admin.departments.create'))
            ->post('/admin/departments', $payload)
            ->assertSessionHasErrors('translations.ku-hawrami.title');

        $this->assertDatabaseCount('departments', 0);
    }

    public function test_writing_a_department_clears_the_page_cache(): void
    {
        $file = storage_path('framework/pagecache/home-en-abc12345.html');
        @mkdir(dirname($file), 0775, true);
        file_put_contents($file, '<html>stale</html>');

        $this->post('/admin/departments', $this->payload());

        $this->assertFileDoesNotExist($file);
    }

    public function test_the_department_list_is_shown(): void
    {
        Department::create($this->payload());

        $this->get('/admin/departments')
            ->assertOk()
            ->assertSee('Title ku-sorani')
            ->assertSee('Title en');
    }
}
