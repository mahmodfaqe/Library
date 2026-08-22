<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Department;
use App\Models\User;
use App\Support\Locale;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookCatalogueTest extends TestCase
{
    use RefreshDatabase;

    private function department(string $title = 'Biology'): Department
    {
        return Department::create([
            'sort_order' => 1,
            'icon' => '🧬',
            'drive_url' => 'https://drive.google.com/drive/folders/bio',
            'translations' => ['en' => ['title' => $title, 'desc' => 'd', 'button' => 'b']],
        ]);
    }

    private function book(array $overrides = []): Book
    {
        return Book::create(array_replace([
            'title' => 'Molecular Biology of the Cell',
            'author' => 'Bruce Alberts',
            'year' => 2015,
            'language' => 'English',
            'url' => 'https://drive.google.com/file/d/abc',
        ], $overrides));
    }

    public function test_the_catalogue_lists_books(): void
    {
        $this->book();

        $this->get('/en/books')
            ->assertOk()
            ->assertSee('Molecular Biology of the Cell')
            ->assertSee('Bruce Alberts');
    }

    public function test_it_searches_by_title_and_author(): void
    {
        $this->book();
        $this->book(['title' => 'Organic Chemistry', 'author' => 'Paula Bruice']);

        $this->get('/en/books?q=Alberts')
            ->assertSee('Molecular Biology of the Cell')
            ->assertDontSee('Organic Chemistry');

        $this->get('/en/books?q=Organic')
            ->assertSee('Organic Chemistry')
            ->assertDontSee('Molecular Biology of the Cell');
    }

    public function test_a_wildcard_in_the_search_is_treated_as_text(): void
    {
        $this->book();

        // '%' must not act as "match everything".
        $this->get('/en/books?q=%')->assertDontSee('Molecular Biology of the Cell');
    }

    public function test_it_filters_by_department(): void
    {
        $bio = $this->department();
        $this->book(['department_id' => $bio->id]);
        $this->book(['title' => 'Organic Chemistry', 'department_id' => null]);

        $this->get("/en/books?department={$bio->id}")
            ->assertSee('Molecular Biology of the Cell')
            ->assertDontSee('Organic Chemistry');
    }

    public function test_an_array_query_parameter_does_not_crash_the_page(): void
    {
        // ?q[]=x reached the view and failed on "Array to string conversion".
        $this->book();

        $this->get('/en/books?q[]=x')->assertOk();
        $this->get('/en/books?department[]=x')->assertOk();
        $this->get('/en/books?q[]=x&department[]=y')->assertOk();
    }

    public function test_the_catalogue_is_reachable_in_every_locale(): void
    {
        foreach (Locale::SUPPORTED as $locale) {
            $uri = $locale === Locale::DEFAULT ? '/books' : "/$locale/books";

            $this->get($uri)
                ->assertOk()
                ->assertSee(__('books.title', [], $locale), false);
        }
    }

    public function test_it_shows_an_empty_state(): void
    {
        $this->get('/en/books')->assertSee(__('books.empty', [], 'en'));
    }

    public function test_staff_can_add_a_book(): void
    {
        $staff = User::create([
            'name' => 'Library Staff', 'email' => 'staff@uor.edu.krd',
            'password' => 'correct-horse-battery-staple', 'role' => User::ROLE_STAFF,
        ]);

        $this->actingAs($staff)->post('/admin/books', [
            'title' => 'Principles of Genetics',
            'author' => 'Snustad',
            'year' => 2020,
            'url' => 'https://drive.google.com/file/d/xyz',
        ])->assertRedirect(route('admin.books'));

        $this->assertDatabaseHas('books', ['title' => 'Principles of Genetics']);
        $this->assertDatabaseHas('activity_log', ['action' => 'book.created', 'subject' => 'Principles of Genetics']);
    }

    public function test_a_book_needs_a_valid_link(): void
    {
        $staff = User::create([
            'name' => 'Library Staff', 'email' => 'staff@uor.edu.krd',
            'password' => 'correct-horse-battery-staple', 'role' => User::ROLE_STAFF,
        ]);

        $this->actingAs($staff)
            ->from(route('admin.books.create'))
            ->post('/admin/books', ['title' => 'Something', 'url' => 'not-a-url'])
            ->assertSessionHasErrors('url');

        $this->assertDatabaseCount('books', 0);
    }

    public function test_a_guest_cannot_manage_books(): void
    {
        $this->get('/admin/books')->assertRedirect(route('admin.login'));
        $this->post('/admin/books', ['title' => 'X', 'url' => 'https://example.com'])
            ->assertRedirect(route('admin.login'));

        $this->assertDatabaseCount('books', 0);
    }
}
