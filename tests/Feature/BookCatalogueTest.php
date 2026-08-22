<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Category;
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

    public function test_the_catalogue_opens_on_the_subject_shelves(): void
    {
        // With over a thousand books, landing on a flat list is unusable.
        $biology = Category::create(['name' => 'بایۆلۆجی', 'sort_order' => 1]);
        $this->book(['category_id' => $biology->id]);

        $this->get('/en/books')
            ->assertOk()
            ->assertSee('بایۆلۆجی', false)
            ->assertDontSee('Molecular Biology of the Cell');
    }

    public function test_choosing_a_subject_lists_its_books(): void
    {
        $biology = Category::create(['name' => 'بایۆلۆجی', 'sort_order' => 1]);
        $this->book(['category_id' => $biology->id]);

        $this->get("/en/books?category={$biology->id}")
            ->assertOk()
            ->assertSee('Molecular Biology of the Cell')
            ->assertSee('Bruce Alberts')
            ->assertSee(__('books.back_to_subjects', [], 'en'));
    }

    public function test_searching_looks_across_every_subject(): void
    {
        $biology = Category::create(['name' => 'بایۆلۆجی', 'sort_order' => 1]);
        $chemistry = Category::create(['name' => 'کیمیا', 'sort_order' => 2]);
        $this->book(['category_id' => $biology->id]);
        $this->book(['title' => 'Molecular Orbitals', 'category_id' => $chemistry->id]);

        $this->get('/en/books?q=Molecular')
            ->assertOk()
            ->assertSee('Molecular Biology of the Cell')
            ->assertSee('Molecular Orbitals');
    }

    public function test_each_shelf_shows_how_many_books_it_holds(): void
    {
        $biology = Category::create(['name' => 'بایۆلۆجی', 'sort_order' => 1]);
        $this->book(['category_id' => $biology->id]);
        $this->book(['title' => 'Second', 'category_id' => $biology->id]);

        $this->get('/en/books')
            ->assertSee(trans_choice('books.results', 2, ['count' => 2], 'en'));
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

    public function test_the_unprefixed_catalogue_is_the_default_locale_whatever_was_viewed_before(): void
    {
        $biology = Category::create(['name' => 'بایۆلۆجی', 'translations' => ['en' => 'Biology'], 'sort_order' => 1]);
        $this->book(['category_id' => $biology->id]);

        // Look at the English catalogue first: that writes the session locale.
        $this->get('/en/books')->assertOk()->assertSee('Biology');

        // Switching back to Sorani lands on the unprefixed address. It has to
        // be Sorani — reading the session here would hand back English.
        $this->get('/books')
            ->assertOk()
            ->assertSee('بایۆلۆجی')
            ->assertDontSee('Biology');
    }

    public function test_the_language_switcher_keeps_the_visitor_on_the_page(): void
    {
        // Switching language on the catalogue must not drop the visitor back
        // on the home page.
        $this->get('/en/books')
            ->assertOk()
            ->assertSee(url('/ar/books'), false)
            ->assertSee(url('/books'), false);

        $this->get('/en/privacy')
            ->assertOk()
            ->assertSee(url('/ar/privacy'), false);
    }

    public function test_a_wildcard_is_not_a_wildcard(): void
    {
        $this->book(['title' => 'Biology']);

        // As a LIKE wildcard, "B%iology" would match "Biology". It must not:
        // the character carries no special meaning in a visitor's search.
        $this->get('/en/books?q=B%25iology')
            ->assertOk()
            ->assertDontSee('Biology');
    }

    public function test_it_filters_by_category(): void
    {
        // The catalogue is browsed by the library's own subject categories,
        // which is how the collection is actually organised.
        $biology = Category::create(['name' => 'بایۆلۆجی', 'sort_order' => 1]);

        $this->book(['category_id' => $biology->id]);
        $this->book(['title' => 'Organic Chemistry', 'category_id' => null]);

        $this->get("/en/books?category={$biology->id}")
            ->assertSee('Molecular Biology of the Cell')
            ->assertDontSee('Organic Chemistry');
    }

    public function test_the_category_appears_on_the_card(): void
    {
        $biology = Category::create(['name' => 'بایۆلۆجی', 'sort_order' => 1]);
        $this->book(['category_id' => $biology->id]);

        $this->get('/en/books')->assertSee('بایۆلۆجی', false);
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

    public function test_an_empty_subject_says_so(): void
    {
        $empty = Category::create(['name' => 'بەتاڵ', 'sort_order' => 1]);

        $this->get("/en/books?category={$empty->id}")->assertSee(__('books.empty', [], 'en'));
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
