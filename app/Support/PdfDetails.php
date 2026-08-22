<?php

namespace App\Support;

use Smalot\PdfParser\Parser;
use Throwable;

/**
 * What a PDF says about itself.
 *
 * The catalogue was imported from folder names, which carry a title and
 * nothing else. Author and year have to come out of the files themselves.
 */
class PdfDetails
{
    /**
     * Values that appear in the /Author field but name nobody: the scanner,
     * the word processor, or whoever was logged into the machine.
     *
     * @var list<string>
     */
    private const NOT_A_PERSON = [
        'user', 'users', 'admin', 'administrator', 'owner', 'guest', 'pc', 'hp', 'dell',
        'acer', 'asus', 'toshiba', 'lenovo', 'samsung', 'windows', 'microsoft', 'word',
        'microsoft word', 'microsoft office word', 'office', 'adobe', 'acrobat',
        'adobe acrobat', 'acrobat distiller', 'pdfcreator', 'pdf', 'scanner', 'scan',
        'canon', 'epson', 'xerox', 'ricoh', 'kyocera', 'unknown', 'none', 'null',
        'default', 'anonymous', 'test', 'temp', 'my computer', 'home', 'lap', 'laptop',
        'calibre', 'ghostscript', 'latex', 'pdftex', 'tex', 'abbyy', 'finereader',
    ];

    /**
     * The earliest publication year worth believing. Anything older is a
     * misread number rather than a date, for this collection.
     */
    private const EARLIEST_YEAR = 1400;

    /**
     * Read what the file can tell us. Every field is optional: a scanned book
     * with no text layer yields nothing, which is a fine answer.
     *
     * @return array{author: ?string, year: ?int, language: ?string}
     */
    public static function read(string $path): array
    {
        try {
            $pdf = (new Parser)->parseFile($path);
        } catch (Throwable) {
            // A corrupt or encrypted file tells us nothing; that is not an
            // error worth stopping an import of a thousand books for.
            return ['author' => null, 'year' => null, 'language' => null];
        }

        $details = $pdf->getDetails();

        // Only the opening pages: a title page carries the author and the
        // copyright year, and reading the whole book to find them would be
        // slow and would pick up every year mentioned in the text.
        $opening = '';

        try {
            foreach (array_slice($pdf->getPages(), 0, 4) as $page) {
                $opening .= "\n".$page->getText();
            }
        } catch (Throwable) {
            // Some PDFs parse but cannot render text. The metadata still can.
        }

        return [
            'author' => self::author($details),
            'year' => self::year($details, $opening),
            'language' => self::language($opening),
        ];
    }

    /**
     * The author, if the file names a person rather than the software that
     * produced it.
     *
     * @param  array<string, mixed>  $details
     */
    private static function author(array $details): ?string
    {
        foreach (['Author', 'dc:creator', 'Creator'] as $field) {
            $value = $details[$field] ?? null;

            if (is_array($value)) {
                $value = reset($value);
            }

            if (! is_string($value)) {
                continue;
            }

            $value = self::tidy($value);

            if ($value === '' || mb_strlen($value) > 120) {
                continue;
            }

            if (in_array(mb_strtolower($value), self::NOT_A_PERSON, true)) {
                continue;
            }

            // A name has letters in it. "C:\\Users\\Ali\\book.doc" does not
            // count, nor does a version string.
            if (! preg_match('/\p{L}{2}/u', $value) || preg_match('/[\\\\\/]|\.(pdf|docx?|indd)$/iu', $value)) {
                continue;
            }

            return $value;
        }

        return null;
    }

    /**
     * The year the book was published.
     *
     * The text is preferred over the file's own dates: /CreationDate is when
     * somebody scanned or typeset the file, which for an older book is not
     * the same thing at all. The dates are a last resort.
     *
     * @param  array<string, mixed>  $details
     */
    private static function year(array $details, string $opening): ?int
    {
        $latest = (int) date('Y') + 1;

        // "© 2019", "Copyright 2019", "First published 2019", "الطبعة ٢٠١٩"
        $marked = [];
        preg_match_all(
            '/(?:©|\(c\)|copyright|published|edition|طبع|نشر|چاپ|بڵاوکراوەتەوە)[^0-9\x{0660}-\x{0669}]{0,24}([0-9\x{0660}-\x{0669}]{4})/iu',
            $opening,
            $marked
        );

        foreach ($marked[1] ?? [] as $candidate) {
            $year = (int) ArabicText::fold($candidate);

            if ($year >= self::EARLIEST_YEAR && $year <= $latest) {
                return $year;
            }
        }

        // Otherwise the most recent believable year on the opening pages: a
        // reprint line lists several, and the newest is the edition in hand.
        $loose = [];
        preg_match_all('/\b([0-9\x{0660}-\x{0669}]{4})\b/u', $opening, $loose);

        $years = collect($loose[1] ?? [])
            ->map(fn (string $v) => (int) ArabicText::fold($v))
            ->filter(fn (int $y) => $y >= 1800 && $y <= $latest);

        if ($years->isNotEmpty()) {
            return $years->max();
        }

        foreach (['CreationDate', 'ModDate'] as $field) {
            if (preg_match('/(\d{4})/', (string) ($details[$field] ?? ''), $m)) {
                $year = (int) $m[1];

                if ($year >= self::EARLIEST_YEAR && $year <= $latest) {
                    return $year;
                }
            }
        }

        return null;
    }

    /**
     * The script the book is written in, for the few books whose Drive folder
     * did not say. Arabic script covers Kurdish, Arabic and Persian alike, so
     * this only separates it from Latin — the folder is the authority on
     * which of the three a book actually is.
     */
    private static function language(string $opening): ?string
    {
        $arabic = preg_match_all('/\p{Arabic}/u', $opening);
        $latin = preg_match_all('/\p{Latin}/u', $opening);

        if ($arabic + $latin < 60) {
            return null;
        }

        return $latin > $arabic * 2 ? 'English' : null;
    }

    private static function tidy(string $value): string
    {
        $value = preg_replace('/[\x{0000}-\x{001F}\x{200B}-\x{200F}]/u', '', $value);

        return trim(preg_replace('/\s+/u', ' ', $value));
    }
}
