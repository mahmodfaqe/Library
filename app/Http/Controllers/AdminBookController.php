<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Book;
use App\Models\Department;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminBookController extends Controller
{
    public function index(Request $request): View
    {
        $search = $this->textQuery($request, 'q');

        return view('admin.books.index', [
            'books' => Book::with('department')
                ->matching($search)
                ->orderBy('title')
                ->paginate(20)
                ->withQueryString(),
            'search' => $search,
        ]);
    }

    public function create(): View
    {
        return view('admin.books.form', [
            'book' => new Book,
            'departments' => Department::orderBy('sort_order')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $book = Book::create($this->validated($request));
        Activity::record('book.created', $book->title);
        $this->clearPageCache();

        return redirect()->route('admin.books')->with('status', __('admin.flash.book_created'));
    }

    public function edit(Book $book): View
    {
        return view('admin.books.form', [
            'book' => $book,
            'departments' => Department::orderBy('sort_order')->get(),
        ]);
    }

    public function update(Request $request, Book $book): RedirectResponse
    {
        $book->update($this->validated($request));
        Activity::record('book.updated', $book->title);
        $this->clearPageCache();

        return redirect()->route('admin.books')->with('status', __('admin.flash.book_updated'));
    }

    public function destroy(Book $book): RedirectResponse
    {
        Activity::record('book.deleted', $book->title);
        $book->delete();
        $this->clearPageCache();

        return redirect()->route('admin.books')->with('status', __('admin.flash.book_deleted'));
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'author' => ['nullable', 'string', 'max:190'],
            'year' => ['nullable', 'integer', 'min:1400', 'max:'.(date('Y') + 1)],
            'language' => ['nullable', 'string', 'max:40'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'url' => ['required', 'url', 'max:500'],
            'cover_url' => ['nullable', 'url', 'max:500'],
        ]);
    }

    private function clearPageCache(): void
    {
        foreach (glob(storage_path('framework/pagecache').'/*') ?: [] as $file) {
            @unlink($file);
        }
    }

    /**
     * A query-string value as a string, or null.
     *
     * Query parameters are attacker-controlled and can arrive as arrays
     * (?q[]=x), which would otherwise reach the view and blow up on
     * "Array to string conversion".
     */
    private function textQuery(Request $request, string $key): ?string
    {
        $value = $request->query($key);

        if (! is_string($value)) {
            return null;
        }

        return trim($value) === '' ? null : trim($value);
    }
}
