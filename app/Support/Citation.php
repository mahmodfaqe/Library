<?php

namespace App\Support;

use App\Models\Book;
use App\Models\Thesis;
use Illuminate\Support\Str;

class Citation
{
    /**
     * A book written out for a reference manager.
     *
     * The citation shown on the page is for a reader copying it into a
     * document by hand. This is for the other half of them: the ones who keep
     * their reading in Zotero or Mendeley and expect a library to hand them a
     * file their software understands.
     */
    public const FORMATS = ['bib', 'ris'];

    public static function write(Book $book, string $format): string
    {
        return $format === 'ris' ? self::ris($book) : self::bibtex($book);
    }

    public static function filename(Book $book, string $format): string
    {
        $stem = Str::of($book->title)->limit(60, '')->slug();

        return ($stem->isEmpty() ? 'book-'.$book->id : (string) $stem).'.'.$format;
    }

    public static function contentType(string $format): string
    {
        return $format === 'ris'
            ? 'application/x-research-info-systems'
            : 'application/x-bibtex';
    }

    /**
     * The key a reader sees in their own bibliography: deacon2006fungal.
     */
    public static function key(Book $book): string
    {
        $surname = '';

        if ($book->author) {
            // "J. W. Deacon" and "Deacon, J. W." both give deacon.
            $first = trim(explode(',', $book->author)[0]);
            $words = preg_split('/\s+/u', $first) ?: [];
            $surname = (string) end($words);
        }

        $word = '';

        foreach (preg_split('/\s+/u', $book->title) ?: [] as $candidate) {
            // The first word that carries meaning, skipping articles.
            if (mb_strlen($candidate) > 3) {
                $word = $candidate;
                break;
            }
        }

        $key = mb_strtolower($surname.($book->year ?: '').$word);
        $key = preg_replace('/[^\p{L}\p{N}]+/u', '', $key) ?? '';

        return $key !== '' ? $key : 'book'.$book->id;
    }

    // ── A thesis, which is a different kind of thing ────────────────────

    /**
     * A thesis is not a book, and the formats know it: BibTeX has entry types
     * for a master's and a doctoral thesis, RIS has THES. A reference manager
     * told "book" prints the wrong thing in the bibliography.
     */
    public static function writeThesis(Thesis $thesis, string $format): string
    {
        return $format === 'ris' ? self::thesisRis($thesis) : self::thesisBibtex($thesis);
    }

    public static function thesisFilename(Thesis $thesis, string $format): string
    {
        $stem = Str::of($thesis->title)->limit(60, '')->slug();

        return ($stem->isEmpty() ? 'thesis-'.$thesis->id : (string) $stem).'.'.$format;
    }

    /**
     * The BibTeX entry type for a degree. A bachelor's thesis has no type of
     * its own, so it takes the master's type with the degree spelled out in
     * the type field, which is what the manuals recommend.
     */
    private static function entryType(Thesis $thesis): string
    {
        return $thesis->degree === 'phd' ? 'phdthesis' : 'mastersthesis';
    }

    private static function thesisBibtex(Thesis $thesis): string
    {
        $fields = array_filter([
            'author' => $thesis->author,
            'title' => $thesis->title,
            'school' => __('messages.university_name', [], 'en'),
            'year' => $thesis->year,
            'type' => __('theses.degrees.'.$thesis->degree, [], 'en'),
            'address' => 'Rania, Kurdistan Region, Iraq',
            'doi' => $thesis->doi,
            'pages' => $thesis->pages,
            'language' => $thesis->language,
            'keywords' => $thesis->keywords,
            'abstract' => $thesis->abstract_en ?: $thesis->abstract,
            'url' => $thesis->permanentUrl(),
        ], fn ($value) => filled($value));

        $lines = ['@'.self::entryType($thesis).'{'.self::thesisKey($thesis).','];

        foreach ($fields as $name => $value) {
            $lines[] = '  '.str_pad($name, 10).' = {'.self::escapeBibtex((string) $value).'},';
        }

        $lines[] = '}';

        return implode("\n", $lines)."\n";
    }

    private static function thesisRis(Thesis $thesis): string
    {
        $lines = [['TY', 'THES']];

        foreach (self::splitNames($thesis->author) as $author) {
            $lines[] = ['AU', $author];
        }

        // The supervisor is a secondary author in RIS, which is how reference
        // managers show "supervised by".
        foreach (array_filter([$thesis->supervisor, $thesis->co_supervisor]) as $supervisor) {
            $lines[] = ['A2', $supervisor];
        }

        foreach ([
            ['TI', $thesis->title],
            ['PY', $thesis->year],
            ['PB', __('messages.university_name', [], 'en')],
            ['M3', __('theses.degrees.'.$thesis->degree, [], 'en')],
            ['CY', 'Rania, Kurdistan Region, Iraq'],
            ['DO', $thesis->doi],
            ['SP', $thesis->pages],
            ['LA', $thesis->language],
            ['AB', $thesis->abstract_en ?: $thesis->abstract],
            ['UR', $thesis->permanentUrl()],
        ] as [$tag, $value]) {
            if (filled($value)) {
                $lines[] = [$tag, (string) $value];
            }
        }

        foreach ($thesis->keywordList() as $keyword) {
            $lines[] = ['KW', $keyword];
        }

        $lines[] = ['ER', ''];

        return implode("\r\n", array_map(
            fn ($line) => $line[0].'  - '.self::oneLine($line[1]),
            $lines
        ))."\r\n";
    }

    private static function thesisKey(Thesis $thesis): string
    {
        $words = preg_split('/\s+/u', trim($thesis->author)) ?: [];
        $surname = (string) end($words);

        $word = '';

        foreach (preg_split('/\s+/u', $thesis->title) ?: [] as $candidate) {
            if (mb_strlen($candidate) > 3) {
                $word = $candidate;
                break;
            }
        }

        $key = preg_replace('/[^\p{L}\p{N}]+/u', '', mb_strtolower($surname.$thesis->year.$word)) ?? '';

        return $key !== '' ? $key : 'thesis'.$thesis->id;
    }

    private static function bibtex(Book $book): string
    {
        $fields = array_filter([
            'author' => $book->author,
            'title' => $book->title,
            'publisher' => $book->publisher ?: __('messages.university_name', [], BookLanguage::citationLocale($book->language)),
            'year' => $book->year,
            'edition' => $book->edition,
            'isbn' => $book->isbn,
            'doi' => $book->doi,
            'pages' => $book->pages,
            'language' => $book->language,
            'keywords' => $book->keywords,
            'abstract' => $book->abstract,
            'url' => Locale::bookUrl($book->id, BookLanguage::citationLocale($book->language)),
        ], fn ($value) => filled($value));

        $lines = ['@book{'.self::key($book).','];

        foreach ($fields as $name => $value) {
            $lines[] = '  '.str_pad($name, 10).' = {'.self::escapeBibtex((string) $value).'},';
        }

        $lines[] = '}';

        return implode("\n", $lines)."\n";
    }

    private static function ris(Book $book): string
    {
        $lines = [['TY', 'BOOK']];

        // RIS wants one author per line, in the order they are credited.
        foreach (self::authors($book) as $author) {
            $lines[] = ['AU', $author];
        }

        foreach ([
            ['TI', $book->title],
            ['PY', $book->year],
            ['PB', $book->publisher ?: __('messages.university_name', [], BookLanguage::citationLocale($book->language))],
            ['ET', $book->edition],
            ['SN', $book->isbn],
            ['DO', $book->doi],
            ['SP', $book->pages],
            ['LA', $book->language],
            ['AB', $book->abstract],
            ['UR', Locale::bookUrl($book->id, BookLanguage::citationLocale($book->language))],
        ] as [$tag, $value]) {
            if (filled($value)) {
                $lines[] = [$tag, (string) $value];
            }
        }

        foreach ($book->keywordList() as $keyword) {
            $lines[] = ['KW', $keyword];
        }

        $lines[] = ['ER', ''];

        // RIS is a line-oriented format from the DOS era and is still read
        // that way: CRLF, and "TAG  - value".
        return implode("\r\n", array_map(
            fn ($line) => $line[0].'  - '.self::oneLine($line[1]),
            $lines
        ))."\r\n";
    }

    /**
     * @return list<string>
     */
    public static function authors(Book $book): array
    {
        return self::splitNames($book->author);
    }

    /**
     * One field holding names, as the list of people it means.
     *
     * @return list<string>
     */
    public static function splitNames(?string $written): array
    {
        if (! $written) {
            return [];
        }

        // "Alberts, Johnson, Lewis" is three people; "Deacon, J. W." is one.
        // A part of two letters or fewer with a full stop is an initial, so
        // the comma before it did not separate two names.
        $parts = array_map('trim', explode(',', $written));
        $names = [];

        foreach ($parts as $part) {
            if ($part === '') {
                continue;
            }

            $isInitials = (bool) preg_match('/^(\p{L}\.?\s*){1,3}$/u', $part) && str_contains($part, '.');

            if ($isInitials && $names !== []) {
                $names[count($names) - 1] .= ', '.$part;

                continue;
            }

            $names[] = $part;
        }

        return $names;
    }

    private static function escapeBibtex(string $value): string
    {
        $value = self::oneLine($value);

        return str_replace(
            ['\\', '{', '}', '&', '%', '$', '#', '_'],
            ['\\textbackslash{}', '\\{', '\\}', '\\&', '\\%', '\\$', '\\#', '\\_'],
            $value
        );
    }

    /**
     * Both formats break on a newline inside a value.
     */
    private static function oneLine(string $value): string
    {
        return trim((string) preg_replace('/\s+/u', ' ', $value));
    }
}
