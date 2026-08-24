<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\Book;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    private function administrator(): User
    {
        return User::create([
            'name' => 'Librarian',
            'email' => 'librarian@test.local',
            'password' => bcrypt('a-password-for-the-test'),
            'role' => 'admin',
        ]);
    }

    public function test_the_panel_opens_on_the_overview(): void
    {
        $this->actingAs($this->administrator())
            ->get('/admin')
            ->assertOk()
            ->assertSee(__('admin.dashboard.gaps'));
    }

    public function test_it_counts_what_the_collection_holds(): void
    {
        $biology = Category::create(['name' => 'Biology', 'sort_order' => 1]);
        Book::create(['title' => 'One', 'category_id' => $biology->id, 'url' => 'https://x.test/1']);
        Book::create(['title' => 'Two', 'category_id' => $biology->id, 'url' => 'https://x.test/2']);

        $this->actingAs($this->administrator())
            ->get('/admin')
            ->assertOk()
            ->assertSeeInOrder(['2', __('admin.nav.books')]);
    }

    public function test_it_counts_what_is_missing_and_links_to_it(): void
    {
        Book::create(['title' => 'No author', 'url' => 'https://x.test/1']);
        Book::create(['title' => 'Has one', 'author' => 'Somebody', 'url' => 'https://x.test/2']);

        $this->actingAs($this->administrator())
            ->get('/admin')
            ->assertOk()
            ->assertSee(__('admin.dashboard.no_author'))
            // The count is a link to the books it counted, not just a number.
            ->assertSee(route('admin.books', ['missing' => 'author']), false);
    }

    public function test_a_complete_collection_reports_nothing_missing(): void
    {
        $biology = Category::create(['name' => 'Biology', 'sort_order' => 1]);
        Book::create([
            'title' => 'Complete',
            'author' => 'Somebody',
            'year' => 2020,
            'language' => 'English',
            'category_id' => $biology->id,
            'url' => 'https://x.test/1',
        ]);

        $this->actingAs($this->administrator())
            ->get('/admin')
            ->assertOk()
            ->assertSee(__('admin.dashboard.complete'));
    }

    public function test_the_book_list_can_be_narrowed_to_what_is_missing(): void
    {
        Book::create(['title' => 'Without an author', 'url' => 'https://x.test/1']);
        Book::create(['title' => 'With an author', 'author' => 'Somebody', 'url' => 'https://x.test/2']);

        $this->actingAs($this->administrator())
            ->get('/admin/books?missing=author')
            ->assertOk()
            ->assertSee('Without an author')
            ->assertDontSee('With an author');
    }

    public function test_the_book_list_can_be_narrowed_by_subject_and_language(): void
    {
        $biology = Category::create(['name' => 'Biology', 'sort_order' => 1]);
        $chemistry = Category::create(['name' => 'Chemistry', 'sort_order' => 2]);

        Book::create(['title' => 'Kurdish biology', 'language' => 'کوردی',
            'category_id' => $biology->id, 'url' => 'https://x.test/1']);
        Book::create(['title' => 'English biology', 'language' => 'English',
            'category_id' => $biology->id, 'url' => 'https://x.test/2']);
        Book::create(['title' => 'Kurdish chemistry', 'language' => 'کوردی',
            'category_id' => $chemistry->id, 'url' => 'https://x.test/3']);

        $this->actingAs($this->administrator())
            ->get('/admin/books?category='.$biology->id.'&language='.urlencode('کوردی'))
            ->assertOk()
            ->assertSee('Kurdish biology')
            ->assertDontSee('English biology')
            ->assertDontSee('Kurdish chemistry');
    }

    public function test_the_list_can_be_sorted(): void
    {
        Book::create(['title' => 'Older', 'year' => 1990, 'url' => 'https://x.test/1']);
        Book::create(['title' => 'Newer', 'year' => 2020, 'url' => 'https://x.test/2']);

        $librarian = $this->administrator();

        $this->actingAs($librarian)
            ->get('/admin/books?sort=year&dir=desc')
            ->assertOk()
            ->assertSeeInOrder(['Newer', 'Older']);

        $this->actingAs($librarian)
            ->get('/admin/books?sort=year&dir=asc')
            ->assertOk()
            ->assertSeeInOrder(['Older', 'Newer']);
    }

    public function test_an_invented_sort_column_is_ignored(): void
    {
        // The value reaches an ORDER BY, so only an allowlisted column may.
        Book::create(['title' => 'Anything', 'url' => 'https://x.test/1']);

        $this->actingAs($this->administrator())
            ->get('/admin/books?sort=password&dir=asc')
            ->assertOk()
            ->assertSee('Anything');
    }

    public function test_filters_survive_a_change_of_sort(): void
    {
        $biology = Category::create(['name' => 'Biology', 'sort_order' => 1]);
        Book::create(['title' => 'Filed', 'category_id' => $biology->id, 'url' => 'https://x.test/1']);

        $html = $this->actingAs($this->administrator())
            ->get('/admin/books?missing=author&category='.$biology->id)
            ->assertOk()
            ->getContent();

        // A sort link keeps the filters, or sorting would silently widen the
        // list back out to the whole catalogue.
        $this->assertStringContainsString('missing=author', $html);
        $this->assertStringContainsString('sort=year', $html);
    }

    public function test_the_author_and_year_can_be_typed_into_the_list(): void
    {
        $a = Book::create(['title' => 'One', 'url' => 'https://x.test/1']);
        $b = Book::create(['title' => 'Two', 'url' => 'https://x.test/2']);

        $this->actingAs($this->administrator())
            ->from('/admin/books')
            ->put('/admin/books/quick', ['books' => [
                $a->id => ['author' => 'Bruce Alberts', 'year' => '2015'],
                $b->id => ['author' => 'Jane Doe', 'year' => ''],
            ]])
            ->assertRedirect('/admin/books');

        $this->assertSame('Bruce Alberts', $a->refresh()->author);
        $this->assertSame(2015, $a->year);
        $this->assertSame('Jane Doe', $b->refresh()->author);
        $this->assertNull($b->year);
    }

    public function test_clearing_a_field_in_the_list_empties_it(): void
    {
        $book = Book::create([
            'title' => 'One',
            'author' => 'Wrong Name',
            'year' => 1999,
            'url' => 'https://x.test/1',
        ]);

        $this->actingAs($this->administrator())
            ->put('/admin/books/quick', ['books' => [
                $book->id => ['author' => '  ', 'year' => ''],
            ]]);

        $book->refresh();

        $this->assertNull($book->author);
        $this->assertNull($book->year);
    }

    public function test_only_the_rows_that_changed_are_recorded(): void
    {
        $untouched = Book::create(['title' => 'Same', 'author' => 'Keep', 'url' => 'https://x.test/1']);
        $edited = Book::create(['title' => 'Changed', 'url' => 'https://x.test/2']);

        $this->actingAs($this->administrator())
            ->put('/admin/books/quick', ['books' => [
                $untouched->id => ['author' => 'Keep', 'year' => ''],
                $edited->id => ['author' => 'New', 'year' => ''],
            ]]);

        // Every row on the page is submitted, but the log is for edits.
        $this->assertSame(1, Activity::where('action', 'book.updated')->count());
    }

    public function test_a_year_the_column_cannot_hold_is_refused(): void
    {
        $book = Book::create(['title' => 'One', 'url' => 'https://x.test/1']);

        $this->actingAs($this->administrator())
            ->from('/admin/books')
            ->put('/admin/books/quick', ['books' => [
                $book->id => ['author' => '', 'year' => '3500'],
            ]])
            ->assertSessionHasErrors();

        $this->assertNull($book->refresh()->year);
    }

    public function test_the_list_editor_cannot_reach_other_fields(): void
    {
        $book = Book::create(['title' => 'Real title', 'url' => 'https://x.test/1']);

        $this->actingAs($this->administrator())
            ->put('/admin/books/quick', ['books' => [
                $book->id => ['author' => 'Somebody', 'title' => 'Rewritten', 'url' => 'https://evil.test'],
            ]]);

        $book->refresh();

        // Only author and year are editable here; the rest goes through the
        // form, where it is validated and recorded properly.
        $this->assertSame('Real title', $book->title);
        $this->assertSame('https://x.test/1', $book->url);
        $this->assertSame('Somebody', $book->author);
    }

    public function test_a_visitor_cannot_edit_the_list(): void
    {
        $book = Book::create(['title' => 'One', 'url' => 'https://x.test/1']);

        $this->put('/admin/books/quick', ['books' => [
            $book->id => ['author' => 'Intruder'],
        ]])->assertRedirect(route('admin.login'));

        $this->assertNull($book->refresh()->author);
    }

    public function test_a_member_of_staff_does_not_see_the_activity_log(): void
    {
        $staff = User::create([
            'name' => 'Assistant',
            'email' => 'assistant@test.local',
            'password' => bcrypt('a-password-for-the-test'),
            'role' => 'staff',
        ]);

        $this->actingAs($staff)
            ->get('/admin')
            ->assertOk()
            ->assertDontSee(__('admin.dashboard.recent_activity'));
    }
}
