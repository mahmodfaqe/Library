<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BookController extends Controller
{
    /**
     * The public catalogue: search by title or author, narrow by department.
     */
    public function index(Request $request): View
    {
        $search = $this->textQuery($request, 'q');
        $category = $this->textQuery($request, 'category');

        $selected = $category ? Category::find($category) : null;

        // With over a thousand books a flat list is unusable, so the catalogue
        // opens on the subject shelves and only lists books once the visitor
        // has picked one or searched.
        $browsing = $search === null && $selected === null;

        return view('books.index', [
            'browsing' => $browsing,
            'books' => $browsing ? null : Book::with('category')
                ->matching($search)
                ->inCategory($selected?->id)
                ->orderBy('title')
                ->paginate(24)
                ->withQueryString(),
            'categories' => Category::withCount('books')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
            'search' => $search,
            'selected' => $selected,
        ]);
    }

    /**
     * Stream a locally held PDF.
     *
     * Files sit outside public/ so every download goes through here, which is
     * what makes the counter possible and leaves room to restrict access later.
     */
    public function download(Book $book): StreamedResponse
    {
        abort_unless($book->hasFile() && Storage::disk('books')->exists($book->file_path), 404);

        $book->incrementQuietly('downloads');

        return Storage::disk('books')->response(
            $book->file_path,
            Str::of($book->title)->limit(80, '')->slug().'.pdf',
            ['Content-Type' => 'application/pdf']
        );
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
