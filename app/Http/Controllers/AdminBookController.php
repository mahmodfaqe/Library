<?php

namespace App\Http\Controllers;

use App\Http\Middleware\CachePage;
use App\Models\Activity;
use App\Models\Book;
use App\Models\Category;
use App\Models\Department;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AdminBookController extends Controller
{
    /**
     * Columns a librarian may sort by, and the direction each defaults to.
     *
     * An allowlist rather than whatever arrives in the query string: the value
     * goes into an ORDER BY.
     *
     * @var array<string, string>
     */
    private const SORTABLE = [
        'title' => 'asc',
        'author' => 'asc',
        'year' => 'desc',
        'language' => 'asc',
        'created_at' => 'desc',
    ];

    /**
     * What can be missing, and how to ask the database for it.
     *
     * @var array<string, string>
     */
    private const MISSING = [
        'author' => 'author',
        'year' => 'year',
        'language' => 'language',
        'category' => 'category_id',
    ];

    public function index(Request $request): View
    {
        $search = $this->textQuery($request, 'q');
        $missing = $this->textQuery($request, 'missing');
        $language = $this->textQuery($request, 'language');
        $category = $this->textQuery($request, 'category');

        $sort = $this->textQuery($request, 'sort');
        $sort = array_key_exists($sort, self::SORTABLE) ? $sort : 'title';
        $direction = $this->textQuery($request, 'dir') === 'desc' ? 'desc' : 'asc';

        return view('admin.books.index', [
            'books' => Book::with(['department', 'category'])
                ->matching($search)
                ->when($language, fn ($q) => $q->where('language', $language))
                ->when($category, fn ($q) => $q->where('category_id', $category))
                ->when($missing === 'link',
                    fn ($q) => $q->whereNull('url')->whereNull('file_path'))
                ->when(isset(self::MISSING[$missing]),
                    fn ($q) => $q->whereNull(self::MISSING[$missing]))
                ->orderBy($sort, $direction)
                ->paginate(20)
                ->withQueryString(),
            'search' => $search,
            'missing' => $missing,
            'language' => $language,
            'category' => $category,
            'sort' => $sort,
            'direction' => $direction,
            'sortable' => array_keys(self::SORTABLE),
            'categories' => Category::orderBy('sort_order')->orderBy('name')->get(),
            'languages' => Book::whereNotNull('language')
                ->distinct()
                ->orderBy('language')
                ->pluck('language'),
        ]);
    }

    public function create(): View
    {
        return view('admin.books.form', [
            'book' => new Book,
            'departments' => Department::orderBy('sort_order')->get(),
            'categories' => Category::orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $book = Book::create($this->validated($request));
        Activity::record('book.created', $book->title);
        CachePage::flush();

        return redirect()->route('admin.books')->with('status', __('admin.flash.book_created'));
    }

    public function edit(Book $book): View
    {
        return view('admin.books.form', [
            'book' => $book,
            'departments' => Department::orderBy('sort_order')->get(),
            'categories' => Category::orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    /**
     * Save the author and year typed straight into the list.
     *
     * The collection arrived from Drive folder names, so more than a thousand
     * books have neither. Opening a form, saving and coming back for each one
     * is a day's work; typing down a column is an afternoon's. Only those two
     * fields can be set this way — everything else still goes through the
     * form, where it can be validated properly and recorded.
     */
    public function quickUpdate(Request $request): RedirectResponse
    {
        $rows = $request->validate([
            'books' => ['array', 'max:100'],
            'books.*.author' => ['nullable', 'string', 'max:190'],
            'books.*.year' => ['nullable', 'integer', 'min:1400', 'max:'.((int) date('Y') + 1)],
        ])['books'] ?? [];

        $changed = 0;

        foreach (Book::findMany(array_keys($rows)) as $book) {
            $fields = [
                'author' => $this->blankToNull($rows[$book->id]['author'] ?? null),
                'year' => $this->blankToNull($rows[$book->id]['year'] ?? null),
            ];

            // Only touch what actually differs, so the activity log records
            // edits rather than every row that happened to be on screen.
            $fields = array_filter(
                $fields,
                fn ($value, $key) => $value !== $book->{$key},
                ARRAY_FILTER_USE_BOTH
            );

            if ($fields === []) {
                continue;
            }

            $book->update($fields);
            Activity::record('book.updated', $book->title);
            $changed++;
        }

        if ($changed > 0) {
            CachePage::flush();
        }

        return back()->with('status', __('admin.books.quick_saved', ['count' => $changed]));
    }

    private function blankToNull(mixed $value): mixed
    {
        return is_string($value) && trim($value) === '' ? null : $value;
    }

    public function update(Request $request, Book $book): RedirectResponse
    {
        $book->update($this->validated($request, $book));
        Activity::record('book.updated', $book->title);
        CachePage::flush();

        return redirect()->route('admin.books')->with('status', __('admin.flash.book_updated'));
    }

    public function destroy(Book $book): RedirectResponse
    {
        Activity::record('book.deleted', $book->title);

        if ($book->hasFile()) {
            Storage::disk('books')->delete($book->file_path);
        }

        $book->delete();
        CachePage::flush();

        return redirect()->route('admin.books')->with('status', __('admin.flash.book_deleted'));
    }

    private function validated(Request $request, ?Book $book = null): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'author' => ['nullable', 'string', 'max:190'],
            'year' => ['nullable', 'integer', 'min:1400', 'max:'.((int) date('Y') + 1)],
            'language' => ['nullable', 'string', 'max:40'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'category_id' => ['nullable', 'exists:categories,id'],
            // A book needs somewhere to be read: an uploaded file or a link.
            'url' => ['nullable', 'url', 'max:500'],
            'cover_url' => ['nullable', 'url', 'max:500'],
            'file' => [
                $book?->hasFile() || $request->filled('url') ? 'nullable' : 'required',
                'file', 'mimetypes:application/pdf', 'max:'.(int) config('library.max_upload_kb'),
            ],
        ], [
            'file.required' => __('admin.books.file_or_url_required'),
        ]);

        if ($file = $request->file('file')) {
            // Replacing a file removes the old one rather than leaving it
            // orphaned on disk.
            if ($book?->hasFile()) {
                Storage::disk('books')->delete($book->file_path);
            }

            $data['file_path'] = $file->store('', 'books');
            $data['file_size'] = $file->getSize();
        }

        unset($data['file']);

        return $data;
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
