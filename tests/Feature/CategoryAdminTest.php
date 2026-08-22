<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Category;
use App\Models\User;
use App\Support\Locale;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryAdminTest extends TestCase
{
    use RefreshDatabase;

    private function staff(): User
    {
        return User::create([
            'name' => 'Library Staff', 'email' => 'staff@uor.edu.krd',
            'password' => 'correct-horse-battery-staple', 'role' => User::ROLE_STAFF,
        ]);
    }

    public function test_a_guest_cannot_manage_subjects(): void
    {
        $this->get('/admin/categories')->assertRedirect(route('admin.login'));
    }

    public function test_staff_can_add_a_subject_in_every_language(): void
    {
        $this->actingAs($this->staff())->post('/admin/categories', [
            'name' => 'بایۆلۆجی',
            'icon' => '🧬',
            'sort_order' => 1,
            'translations' => ['en' => 'Biology', 'ar' => 'علم الأحياء', 'tr' => 'Biyoloji'],
        ])->assertRedirect(route('admin.categories'));

        $category = Category::firstOrFail();

        $this->assertSame('Biology', $category->localName('en'));
        $this->assertSame('علم الأحياء', $category->localName('ar'));
        $this->assertDatabaseHas('activity_log', ['action' => 'category.created']);
    }

    public function test_a_blank_language_falls_back_to_sorani(): void
    {
        $this->actingAs($this->staff())->post('/admin/categories', [
            'name' => 'بایۆلۆجی', 'sort_order' => 1,
            'translations' => ['en' => 'Biology', 'fa' => '', 'tr' => null],
        ]);

        $category = Category::firstOrFail();

        $this->assertSame('Biology', $category->localName('en'));
        $this->assertSame('بایۆلۆجی', $category->localName('fa'));
        $this->assertSame('بایۆلۆجی', $category->localName('ku-badini'));
    }

    public function test_the_name_must_be_unique(): void
    {
        Category::create(['name' => 'بایۆلۆجی', 'sort_order' => 1]);

        $this->actingAs($this->staff())
            ->from(route('admin.categories.create'))
            ->post('/admin/categories', ['name' => 'بایۆلۆجی', 'sort_order' => 2])
            ->assertSessionHasErrors('name');

        $this->assertDatabaseCount('categories', 1);
    }

    public function test_a_subject_keeps_its_own_name_when_edited(): void
    {
        $category = Category::create(['name' => 'بایۆلۆجی', 'sort_order' => 1]);

        $this->actingAs($this->staff())
            ->put("/admin/categories/{$category->id}", [
                'name' => 'بایۆلۆجی', 'sort_order' => 5, 'icon' => '🧬',
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame(5, $category->fresh()->sort_order);
    }

    public function test_deleting_a_subject_keeps_its_books(): void
    {
        $category = Category::create(['name' => 'بایۆلۆجی', 'sort_order' => 1]);
        $book = Book::create(['title' => 'A book', 'url' => 'https://example.test/x', 'category_id' => $category->id]);

        $this->actingAs($this->staff())->delete("/admin/categories/{$category->id}");

        $this->assertDatabaseCount('categories', 0);
        // Losing a shelf must never lose the books on it.
        $this->assertDatabaseHas('books', ['id' => $book->id, 'category_id' => null]);
    }

    public function test_the_public_pages_follow_the_visitor_language(): void
    {
        Category::create([
            'name' => 'بایۆلۆجی', 'icon' => '🧬', 'sort_order' => 1,
            'translations' => ['en' => 'Biology', 'ar' => 'علم الأحياء'],
        ]);

        $this->get('/en')->assertSee('Biology')->assertDontSee('بایۆلۆجی');
        $this->get('/ar')->assertSee('علم الأحياء', false);
        // Kurdish variants have no translation of their own and fall back.
        $this->get('/ku-badini')->assertSee('بایۆلۆجی', false);
    }

    public function test_every_supported_locale_has_a_field_on_the_form(): void
    {
        $response = $this->actingAs($this->staff())->get('/admin/categories/create')->assertOk();

        foreach (Locale::SUPPORTED as $locale) {
            $response->assertSee('name-'.$locale, false);
        }
    }
}
