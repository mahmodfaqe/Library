<?php

namespace App\Support;

use Illuminate\Support\Facades\Http;

class OpenLibrary
{
    /**
     * A book's record in the world's catalogue, matched by its title.
     *
     * OpenLibrary is free, needs no key, and holds most of what has been
     * published in English. It holds very little in Kurdish, which is the
     * honest limit of this: the Kurdish half of the collection will still have
     * to be catalogued by the people who own it.
     *
     * Nothing here is trusted blindly. A title alone is a weak key — half the
     * books ever printed are called Biology — so a candidate is only returned
     * when the titles agree closely, and everything returned is recorded as
     * having come from a lookup rather than from a librarian.
     */
    private const ENDPOINT = 'https://openlibrary.org/search.json';

    /**
     * How alike two titles must be before they are held to be the same book.
     * Below this the guess is worse than no answer: a wrong author on a
     * catalogue record is harder to notice, and worse, than a missing one.
     */
    private const CONFIDENCE = 0.9;

    /**
     * Look a title up and return what is known about it, or null.
     *
     * @return array{title: string, author: ?string, year: ?int, publisher: ?string, isbn: ?string, pages: ?int, keywords: ?string, confidence: float}|null
     */
    public static function find(string $title, ?string $author = null): ?array
    {
        $query = ['title' => $title, 'limit' => 5, 'fields' => implode(',', [
            'title', 'author_name', 'first_publish_year', 'publisher',
            'isbn', 'number_of_pages_median', 'subject', 'language',
        ])];

        if ($author) {
            $query['author'] = $author;
        }

        try {
            $response = Http::timeout(20)
                ->withHeaders(['User-Agent' => self::agent()])
                ->get(self::ENDPOINT, $query);

            if ($response->failed()) {
                return null;
            }

            $found = $response->json('docs') ?? [];
        } catch (\Throwable $e) {
            return null;
        }

        foreach ($found as $doc) {
            $score = self::alike($title, (string) ($doc['title'] ?? ''));

            if ($score >= self::CONFIDENCE) {
                return self::record($doc, $score);
            }
        }

        return null;
    }

    /**
     * How alike two titles are, from 0 to 1.
     *
     * Compared after folding, because the catalogue's titles come from Drive
     * file names: "Fungal-biology" and "Fungal Biology" are the same book, and
     * so are "Molecular Biology of the Cell" and "Molecular biology of the
     * cell".
     */
    public static function alike(string $ours, string $theirs): float
    {
        $a = self::simplify($ours);
        $b = self::simplify($theirs);

        if ($a === '' || $b === '') {
            return 0.0;
        }

        if ($a === $b) {
            return 1.0;
        }

        // One title being the whole of the other's opening is the usual shape
        // of a subtitle, and is a match: "Fungal Biology" against "Fungal
        // Biology, 4th Edition".
        if (str_starts_with($b, $a) || str_starts_with($a, $b)) {
            return 0.95;
        }

        similar_text($a, $b, $percent);

        return $percent / 100;
    }

    private static function simplify(string $title): string
    {
        $title = ArabicText::fold($title);
        // Drive file names use hyphens and underscores where a title has
        // spaces, and often carry an extension.
        $title = preg_replace('/\.(pdf|djvu|epub)$/iu', '', $title) ?? $title;
        $title = preg_replace('/[-_]+/u', ' ', $title) ?? $title;
        $title = preg_replace('/[^\p{L}\p{N} ]+/u', '', $title) ?? $title;
        $title = preg_replace('/\s+/u', ' ', $title) ?? $title;

        return trim(mb_strtolower($title));
    }

    /**
     * The fields that describe the work rather than one printing of it, and
     * are therefore safe to write into a record without holding the book.
     *
     * An author does not change between editions. A publisher, a year, an
     * ISBN and a page count all do — a title search for "Molecular Biology of
     * the Cell" answers 1983 and "Omega", which is the first edition of a book
     * the library almost certainly holds the sixth of. Writing those would put
     * a wrong citation in a catalogue whose whole purpose is to be cited from,
     * so they are reported as suggestions and left for a librarian, or for the
     * ISBN read off the copy itself.
     *
     * @var list<string>
     */
    public const SAFE = ['author', 'keywords'];

    private static function record(array $doc, float $score): array
    {
        $isbns = array_filter((array) ($doc['isbn'] ?? []), fn ($i) => strlen((string) $i) === 13);

        return [
            'title' => (string) ($doc['title'] ?? ''),
            'author' => isset($doc['author_name'][0])
                ? implode(', ', array_slice((array) $doc['author_name'], 0, 3))
                : null,
            'year' => isset($doc['first_publish_year']) ? (int) $doc['first_publish_year'] : null,
            'publisher' => isset($doc['publisher'][0]) ? (string) $doc['publisher'][0] : null,
            'isbn' => $isbns ? (string) reset($isbns) : null,
            'pages' => isset($doc['number_of_pages_median'])
                ? (int) $doc['number_of_pages_median']
                : null,
            // The first few subjects only: OpenLibrary lists dozens for a
            // popular book, and a keyword field with sixty words in it helps
            // nobody.
            'keywords' => isset($doc['subject'])
                ? implode(', ', array_slice((array) $doc['subject'], 0, 8))
                : null,
            'confidence' => round($score, 3),
        ];
    }

    /**
     * OpenLibrary asks that a caller say who it is, so that they can get in
     * touch rather than block.
     */
    private static function agent(): string
    {
        return 'UoR-Library/1.0 (+'.config('app.url').')';
    }
}
