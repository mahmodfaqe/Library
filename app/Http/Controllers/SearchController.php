<?php

namespace App\Http\Controllers;

use App\Support\BookLanguage;
use App\Support\BookSearch;
use App\Support\Locale;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;

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
        $category = is_string($category) ? $category : null;

        // Typing "chemistry" asks for "ch", "che", "chem" and so on, and the
        // next visitor asks for the same. The catalogue changes rarely, so a
        // short cache turns most keystrokes into no work at all.
        return response()->json(Cache::remember(
            'suggest:'.App::getLocale().':'.$category.':'.mb_strtolower($term),
            now()->addMinutes(10),
            fn () => $this->results($term, $category),
        ));
    }

    /**
     * @return array{books: mixed, categories: mixed}
     */
    private function results(string $term, ?string $category): array
    {
        $search = new BookSearch($term);

        $books = $search->books(8, $category)
            ->map(fn ($book) => [
                'title' => $book->title,
                'author' => $book->author,
                'year' => $book->year,
                'language' => BookLanguage::name($book->language),
                'subject' => $book->category?->localName(),
                // The page, not the file: a suggestion goes where a card
                // goes, and the file is one button further on.
                'url' => Locale::bookUrl($book->id),
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

        return [
            'books' => $books,
            'categories' => $categories,
        ];
    }
}
