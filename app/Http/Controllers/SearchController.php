<?php

namespace App\Http\Controllers;

use App\Support\BookSearch;
use App\Support\Locale;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /**
     * Suggestions for the catalogue's search box, as the visitor types.
     *
     * The page itself still works without this — the form submits and the
     * catalogue renders as it always did. This only makes the answer arrive
     * sooner.
     */
    public function suggest(Request $request): JsonResponse
    {
        $term = $request->query('q');
        $term = is_string($term) ? trim($term) : '';

        if (mb_strlen($term) < 2) {
            return response()->json(['books' => [], 'categories' => []]);
        }

        $category = $request->query('category');
        $search = new BookSearch($term);

        $books = $search->books(8, is_string($category) ? $category : null)
            ->map(fn ($book) => [
                'title' => $book->title,
                'author' => $book->author,
                'year' => $book->year,
                'language' => $book->language,
                'subject' => $book->category?->localName(),
                'url' => $book->readUrl(),
                'download' => $book->hasFile(),
                'cover' => $book->coverUrl(),
            ]);

        // Only offer a shelf when it is not the one already being browsed.
        $categories = $search->categories()
            ->reject(fn ($shelf) => (string) $shelf->id === (string) $category)
            ->map(fn ($shelf) => [
                'name' => $shelf->localName(),
                'count' => $shelf->books_count,
                'url' => Locale::booksUrl().'?category='.$shelf->id,
            ])
            ->values();

        return response()->json([
            'books' => $books,
            'categories' => $categories,
        ]);
    }
}
