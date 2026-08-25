<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminHtmlTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Every page of the panel opens, and none of them is a broken document.
     *
     * A view that will not render is a five hundred to whoever is running the
     * library, and the pages nobody visits daily — a book with no link, the
     * new-book form — are the ones that break unnoticed.
     */
    public function test_every_admin_page_opens(): void
    {
        $staff = User::create([
            'name' => 'Library Staff',
            'email' => 'staff@uor.edu.krd',
            'password' => 'correct-horse-battery-staple',
            'role' => User::ROLE_ADMIN,
        ]);

        $category = Category::create(['name' => 'Biology', 'sort_order' => 1]);
        $linked = Book::create([
            'title' => 'Fungal Biology',
            'url' => 'https://drive.test/a',
            'category_id' => $category->id,
        ]);

        // A book with nowhere to go takes the other branch of the row cover.
        Book::create(['title' => 'No link at all', 'category_id' => $category->id]);

        $pages = [
            '/admin',
            '/admin/books',
            "/admin/books/{$linked->id}/edit",
            '/admin/books/create',
            '/admin/categories',
            '/admin/users',
        ];

        foreach ($pages as $url) {
            $html = $this->actingAs($staff)->get($url)->assertOk()->getContent();

            $this->assertNotEmpty($html, "{$url} rendered nothing.");

            // A key printed where a word should be is what a missing
            // translation looks like to whoever is using the panel.
            $this->assertDoesNotMatchRegularExpression(
                '/>\s*admin\.[a-z_.]+\s*</',
                $html,
                "{$url} is showing a translation key instead of a word."
            );
        }
    }

    public function test_the_row_cover_is_a_link_only_when_there_is_somewhere_to_go(): void
    {
        $staff = User::create([
            'name' => 'Library Staff',
            'email' => 'staff@uor.edu.krd',
            'password' => 'correct-horse-battery-staple',
            'role' => User::ROLE_ADMIN,
        ]);

        $category = Category::create(['name' => 'Biology', 'sort_order' => 1]);
        Book::create(['title' => 'Linked', 'url' => 'https://drive.test/a', 'category_id' => $category->id]);
        Book::create(['title' => 'No link at all', 'category_id' => $category->id]);

        $html = $this->actingAs($staff)->get('/admin/books')->assertOk()->getContent();

        // Two whole elements, not one whose tag name is decided at run time:
        // that reads as broken HTML to everything that is not Blade.
        $this->assertStringContainsString('<a class="row-cover"', $html);
        $this->assertStringContainsString('<span class="row-cover">', $html);
        $this->assertStringNotContainsString('<{{', $html);
    }
}
