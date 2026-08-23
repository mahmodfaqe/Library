<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchSuggestionsTest extends TestCase
{
    use RefreshDatabase;

    private function book(array $attributes = []): Book
    {
        return Book::create(array_merge([
            'title' => 'Molecular Biology of the Cell',
            'url' => 'https://drive.test/'.uniqid(),
        ], $attributes));
    }

    public function test_it_answers_as_the_visitor_types(): void
    {
        $this->book();

        $this->getJson('/search/suggest?q=molec')
            ->assertOk()
            ->assertJsonPath('books.0.title', 'Molecular Biology of the Cell');
    }

    public function test_one_letter_is_not_a_search(): void
    {
        $this->book();

        // Every keystroke reaches this endpoint; a single letter would match
        // half the catalogue and answer nobody's question.
        $this->getJson('/search/suggest?q=m')
            ->assertOk()
            ->assertExactJson(['books' => [], 'categories' => []]);
    }

    public function test_a_word_with_a_letter_out_of_place_still_finds_the_book(): void
    {
        $this->book(['title' => 'Organic Chemistry']);

        $this->getJson('/search/suggest?q=chemistrey')
            ->assertOk()
            ->assertJsonPath('books.0.title', 'Organic Chemistry');
    }

    public function test_kurdish_spelling_of_the_same_letter_still_matches(): void
    {
        // ك and ک, ي and ی: whichever the keyboard produces.
        $this->book(['title' => 'کیمیای ئەندامی']);

        $this->getJson('/search/suggest?q='.urlencode('كيميا'))
            ->assertOk()
            ->assertJsonPath('books.0.title', 'کیمیای ئەندامی');
    }

    public function test_the_book_that_starts_with_what_was_typed_comes_first(): void
    {
        $this->book(['title' => 'An introduction to organic chemistry']);
        $this->book(['title' => 'Chemistry for beginners']);

        $titles = $this->getJson('/search/suggest?q=chemistry')
            ->assertOk()
            ->json('books.*.title');

        $this->assertSame('Chemistry for beginners', $titles[0]);
    }

    public function test_matching_every_word_beats_matching_one(): void
    {
        $this->book(['title' => 'General chemistry']);
        $this->book(['title' => 'Organic chemistry']);

        $titles = $this->getJson('/search/suggest?q='.urlencode('organic chemistry'))
            ->assertOk()
            ->json('books.*.title');

        $this->assertSame('Organic chemistry', $titles[0]);
    }

    public function test_it_offers_the_subject_as_well_as_the_books(): void
    {
        $biology = Category::create([
            'name' => 'بایۆلۆجی',
            'translations' => ['en' => 'Biology'],
            'sort_order' => 1,
        ]);
        $this->book(['category_id' => $biology->id]);

        $this->getJson('/search/suggest?q=biolo')
            ->assertOk()
            ->assertJsonPath('categories.0.name', 'بایۆلۆجی')
            ->assertJsonPath('categories.0.count', 1);
    }

    public function test_an_empty_subject_is_not_offered(): void
    {
        Category::create(['name' => 'Biology', 'sort_order' => 1]);

        $this->getJson('/search/suggest?q=biolo')
            ->assertOk()
            ->assertJsonCount(0, 'categories');
    }

    public function test_the_subject_being_browsed_is_not_offered_again(): void
    {
        $biology = Category::create(['name' => 'Biology', 'sort_order' => 1]);
        $this->book(['category_id' => $biology->id]);

        $this->getJson('/search/suggest?q=biolo&category='.$biology->id)
            ->assertOk()
            ->assertJsonCount(0, 'categories');
    }

    public function test_a_search_inside_a_subject_stays_inside_it(): void
    {
        $biology = Category::create(['name' => 'Biology', 'sort_order' => 1]);
        $chemistry = Category::create(['name' => 'Chemistry', 'sort_order' => 2]);

        $this->book(['title' => 'Cell structure', 'category_id' => $biology->id]);
        $this->book(['title' => 'Cell chemistry', 'category_id' => $chemistry->id]);

        $titles = $this->getJson('/search/suggest?q=cell&category='.$biology->id)
            ->assertOk()
            ->json('books.*.title');

        $this->assertSame(['Cell structure'], $titles);
    }

    public function test_an_array_query_is_not_a_crash(): void
    {
        // Query parameters are attacker-controlled and can arrive as arrays.
        $this->getJson('/search/suggest?q[]=x')
            ->assertOk()
            ->assertExactJson(['books' => [], 'categories' => []]);
    }

    public function test_a_wildcard_is_still_not_a_wildcard(): void
    {
        $this->book(['title' => 'Biology']);

        $this->getJson('/search/suggest?q='.urlencode('B%iology'))
            ->assertOk()
            ->assertJsonCount(0, 'books');
    }

    public function test_suggestions_answer_in_the_language_they_were_asked_in(): void
    {
        $biology = Category::create([
            'name' => 'بایۆلۆجی',
            'translations' => ['en' => 'Biology', 'ar' => 'علم الأحياء'],
            'sort_order' => 1,
        ]);
        $this->book(['category_id' => $biology->id]);

        // An unprefixed address is Sorani, like every other page.
        $this->getJson('/search/suggest?q=biolo')
            ->assertJsonPath('categories.0.name', 'بایۆلۆجی')
            ->assertJsonPath('categories.0.url', url('/books').'?category='.$biology->id);

        $this->getJson('/en/search/suggest?q=biolo')
            ->assertJsonPath('categories.0.name', 'Biology')
            ->assertJsonPath('categories.0.url', url('/en/books').'?category='.$biology->id);

        $this->getJson('/ar/search/suggest?q=biolo')
            ->assertJsonPath('categories.0.name', 'علم الأحياء')
            ->assertJsonPath('categories.0.url', url('/ar/books').'?category='.$biology->id);
    }

    public function test_the_form_points_at_the_endpoint_for_its_own_language(): void
    {
        $this->get('/en/books')
            ->assertOk()
            ->assertSee('data-suggest="'.url('/en/search/suggest').'"', false);

        $this->get('/books')
            ->assertOk()
            ->assertSee('data-suggest="'.url('/search/suggest').'"', false);
    }

    public function test_a_renamed_book_does_not_keep_answering_under_its_old_title(): void
    {
        $book = $this->book(['title' => 'Molecular Biology of the Cell']);

        $this->getJson('/search/suggest?q=molecular')
            ->assertJsonPath('books.0.title', 'Molecular Biology of the Cell');

        // Suggestions are cached; an admin write has to clear them, or the
        // catalogue answers with a title that no longer exists.
        $this->actingAs($this->administrator())
            ->put("/admin/books/{$book->id}", [
                'title' => 'Cell Biology',
                'url' => $book->url,
            ])
            ->assertRedirect();

        $this->getJson('/search/suggest?q=molecular')->assertJsonCount(0, 'books');
        $this->getJson('/search/suggest?q=cell')
            ->assertJsonPath('books.0.title', 'Cell Biology');
    }

    private function administrator(): User
    {
        return User::create([
            'name' => 'Admin',
            'email' => 'admin@test.local',
            'password' => bcrypt('password-for-the-test'),
            'role' => 'admin',
        ]);
    }

    public function test_the_catalogue_still_works_without_any_of_this(): void
    {
        $biology = Category::create(['name' => 'Biology', 'sort_order' => 1]);
        $this->book(['category_id' => $biology->id]);

        // The form submits and the page renders, script or no script.
        $this->get('/books?q=molecular')
            ->assertOk()
            ->assertSee('Molecular Biology of the Cell')
            ->assertSee('data-suggest', false);
    }
}
