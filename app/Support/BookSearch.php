<?php

namespace App\Support;

use App\Models\Book;
use App\Models\Category;
use Illuminate\Support\Collection;

/**
 * Searching the catalogue, ranked, with near matches.
 *
 * The catalogue is small enough — a few thousand books — that candidates can
 * be gathered with one broad query and scored in PHP. That buys behaviour a
 * LIKE cannot manage on its own: a book whose title merely starts with what
 * has been typed outranks one that mentions it in passing, and a word with a
 * letter out of place still finds its book.
 */
class BookSearch
{
    /**
     * How close a word has to be to count as the same word.
     *
     * Two edits on a long word, one on a short one — enough for a slip of the
     * finger or a spelling nobody agrees on, not enough for a different word.
     */
    private const NEAR_ENOUGH = 0.72;

    /**
     * How many characters of a word are used to look for candidates.
     */
    private const PROBE = 4;

    public function __construct(private readonly string $term) {}

    /**
     * @return Collection<int, Book>
     */
    public function books(int $limit = 8, ?string $categoryId = null): Collection
    {
        $words = $this->words();

        if ($words->isEmpty()) {
            return collect();
        }

        return Book::query()
            ->with('category')
            ->when($categoryId, fn ($q) => $q->where('category_id', $categoryId))
            ->where(function ($query) use ($words) {
                // Any word, not all of them: a book that matches most of what
                // was typed is still worth showing, and the ranking below
                // decides how far up it goes.
                //
                // Candidates are gathered on the opening of each word rather
                // than the whole of it. A misspelling never matches a LIKE, so
                // scoring alone could never rescue it — "chemistrey" has to
                // reach the scorer as a candidate before being recognised as
                // "chemistry".
                foreach ($words as $word) {
                    $query->orWhere('search_text', 'like', '%'.self::escape(self::opening($word)).'%');
                }
            })
            ->limit(400)
            ->get()
            ->map(fn (Book $book) => tap($book, fn ($b) => $b->relevance = $this->score($b, $words)))
            ->filter(fn (Book $book) => $book->relevance > 0)
            ->sortByDesc('relevance')
            ->take($limit)
            ->values();
    }

    /**
     * Subjects whose name matches, so a search for "chemistry" offers the
     * shelf as well as the books on it.
     *
     * @return Collection<int, Category>
     */
    public function categories(int $limit = 3): Collection
    {
        $words = $this->words();

        if ($words->isEmpty()) {
            return collect();
        }

        return Category::withCount('books')
            ->get()
            ->map(function (Category $category) use ($words) {
                $names = collect([$category->name])
                    ->merge(array_values($category->translations ?? []))
                    ->map(fn ($n) => ArabicText::fold((string) $n))
                    ->filter();

                $category->relevance = $names
                    ->map(fn (string $name) => $this->matchStrength($name, $words))
                    ->max() ?? 0.0;

                return $category;
            })
            ->filter(fn (Category $category) => $category->relevance > 0 && $category->books_count > 0)
            ->sortByDesc('relevance')
            ->take($limit)
            ->values();
    }

    /**
     * The words typed, folded, with anything too short to be meaningful on
     * its own dropped — unless that is all there is.
     *
     * @return Collection<int, string>
     */
    private function words(): Collection
    {
        $words = collect(preg_split('/\s+/u', $this->term, -1, PREG_SPLIT_NO_EMPTY) ?: [])
            ->map(fn (string $word) => ArabicText::fold($word))
            ->filter()
            ->values();

        $long = $words->filter(fn (string $w) => mb_strlen($w) > 1)->values();

        return $long->isNotEmpty() ? $long : $words;
    }

    /**
     * How well a book answers the search, as a number to sort by.
     *
     * @param  Collection<int, string>  $words
     */
    private function score(Book $book, Collection $words): float
    {
        $haystack = (string) $book->search_text;
        $score = $this->matchStrength($haystack, $words);

        if ($score <= 0) {
            return 0;
        }

        // The whole phrase, in order, is what somebody typing a title means.
        $phrase = $words->implode(' ');

        if (str_contains($haystack, $phrase)) {
            $score += str_starts_with($haystack, $phrase) ? 6 : 3;
        }

        // A book that can actually be opened is more use than one that cannot.
        return $book->readUrl() ? $score + 0.1 : $score;
    }

    /**
     * How much of what was typed appears in this text.
     *
     * @param  Collection<int, string>  $words
     */
    private function matchStrength(string $haystack, Collection $words): float
    {
        $tokens = preg_split('/\s+/u', $haystack, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $score = 0.0;
        $matched = 0;

        foreach ($words as $word) {
            $best = 0.0;

            foreach ($tokens as $token) {
                $best = max($best, $this->wordScore($word, $token));

                if ($best >= 3) {
                    break;
                }
            }

            if ($best > 0) {
                $matched++;
                $score += $best;
            }
        }

        if ($matched === 0) {
            return 0;
        }

        // Matching every word typed counts for much more than matching one of
        // them: "organic chemistry" should not be answered by every book with
        // "chemistry" in the title before the one that is actually about it.
        return $score * (1 + $matched / max(1, $words->count()));
    }

    /**
     * How well one typed word matches one word of a title.
     */
    private function wordScore(string $word, string $token): float
    {
        if ($token === $word) {
            return 3;
        }

        // Still being typed: "chem" should already be finding "chemistry".
        if (str_starts_with($token, $word)) {
            return 2.5;
        }

        if (str_contains($token, $word)) {
            return 1.5;
        }

        // A near miss — a letter dropped, doubled or swapped. Only worth
        // testing on words long enough for the comparison to mean anything.
        if (mb_strlen($word) < 4 || abs(mb_strlen($token) - mb_strlen($word)) > 3) {
            return 0;
        }

        similar_text($word, $token, $percent);

        return $percent / 100 >= self::NEAR_ENOUGH ? $percent / 100 : 0;
    }

    /**
     * The opening of a word, used to find candidates.
     *
     * Short enough to survive a typo anywhere after it, long enough that the
     * catalogue is not returned wholesale.
     */
    private static function opening(string $word): string
    {
        return mb_strlen($word) <= self::PROBE ? $word : mb_substr($word, 0, self::PROBE);
    }

    private static function escape(string $value): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);
    }
}
