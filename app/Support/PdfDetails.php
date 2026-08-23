<?php

namespace App\Support;

use RuntimeException;
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
        // Typing shops and internet cafés credit themselves in the files they
        // produce: "Asia Computer" came back as an author from the collection.
        'computer', 'computers', 'center', 'centre', 'net', 'cafe', 'print',
        'printing', 'press center', 'design', 'graphics', 'studio',
        // The last few that got through a run over the whole collection.
        'nitro', 'nitro pro', 'prinect', 'prinect printready', 'scansnap',
        'scansnap manager', 'view apart', 'foxit', 'nuance', 'scansoft',
        'primopdf', 'dopdf', 'cutepdf', 'bullzip', 'novapdf', 'pdfsam',
    ];

    /**
     * The earliest publication year worth believing. Anything older is a
     * misread number rather than a date, for this collection.
     */
    private const EARLIEST_YEAR = 1400;

    /**
     * Read what the file can tell us, from its bytes rather than from a path:
     * the books are never stored on this server, only passed through.
     *
     * Every field is optional — a scanned book with no text layer yields
     * nothing, which is a fine answer.
     *
     * @return array{author: ?string, year: ?int, language: ?string}
     */
    public static function read(string $contents, int $seconds = 60): array
    {
        $nothing = ['author' => null, 'year' => null, 'language' => null];

        // One malformed PDF in the collection sends the parser into a loop it
        // never comes out of — an hour of one core at full tilt on a single
        // book, with the rest of the catalogue waiting behind it. An alarm
        // turns that into an exception like any other failure to read.
        $alarm = function_exists('pcntl_alarm') && function_exists('pcntl_signal');

        if ($alarm) {
            pcntl_async_signals(true);
            pcntl_signal(SIGALRM, function () {
                throw new RuntimeException('Timed out reading the PDF.');
            });
            pcntl_alarm($seconds);
        }

        try {
            $pdf = (new Parser)->parseContent($contents);
        } catch (Throwable) {
            // A corrupt or encrypted file tells us nothing; that is not an
            // error worth stopping an import of a thousand books for.
            return $nothing;
        } finally {
            if ($alarm) {
                pcntl_alarm(0);
            }
        }

        $details = $pdf->getDetails();

        // Only the opening pages: a title page carries the author and the
        // copyright year, and reading the whole book to find them would be
        // slow and would pick up every year mentioned in the text.
        $opening = '';

        if ($alarm) {
            pcntl_alarm($seconds);
        }

        try {
            foreach (array_slice($pdf->getPages(), 0, 4) as $page) {
                $opening .= "\n".$page->getText();
            }
        } catch (Throwable) {
            // Some PDFs parse but cannot render text, and some take forever
            // trying. The metadata may still say something.
        } finally {
            if ($alarm) {
                pcntl_alarm(0);
            }
        }

        return [
            // The file's own metadata first, then what the title page says.
            'author' => self::author($details) ?? self::creditedAuthor($opening),
            'year' => self::year($opening),
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

            $value = self::trimScanJunk(self::tidy($value));

            if ($value === '' || mb_strlen($value) > 120) {
                continue;
            }

            if (! self::looksLikeAPerson($value)) {
                continue;
            }

            return $value;
        }

        return null;
    }

    /**
     * The author as the title page credits them.
     *
     * Most of the collection is scanned, and a scan carries no /Author field —
     * but the title page names the author in the text, if there is a text
     * layer at all. Only an explicit credit counts: "by", "تأليف", "نووسینی"
     * and the like. Taking the second line of the page instead would pick up
     * the subtitle, the university, the department and the year with roughly
     * equal frequency.
     */
    private static function creditedAuthor(string $opening): ?string
    {
        $markers = 'by|written by|authored by|edited by|author'
            .'|تأليف|تاليف|إعداد|اعداد|بقلم|المؤلف'
            .'|نووسینی|نوسینی|نووسەر|ئامادەکردنی';

        if (! preg_match('/(?:^|\n)\s*(?:'.$markers.')\s*[:：\-–—]?\s*(.+)/iu', $opening, $m)) {
            return null;
        }

        // A credit is one line; anything past the end of it belongs to
        // something else on the page.
        $value = self::trimScanJunk(self::tidy(explode("\n", $m[1])[0]));

        // Titles of address are part of how an author is credited, but on
        // their own they name nobody.
        $value = trim(preg_replace('/^(?:dr|prof|professor|mr|mrs|ms|د|أ\.د|الدكتور|الأستاذ)\.?\s+/iu', '', $value));

        return $value !== '' && self::looksLikeAPerson($value) ? $value : null;
    }

    /**
     * Whether this is plausibly a person's name.
     *
     * The /Author field of a scanned book is, far more often than not, the
     * program that made the file or the Windows account of whoever ran it.
     * Tested against the real collection, this field yielded "Adobe InDesign
     * 16.0 (Windows)", "husam" and "khaled" — a name, a name and a name, none
     * of them the author of anything. A wrong author in a university
     * catalogue is worse than an empty one, so the bar is set high: what gets
     * through has to look like how an author is actually credited.
     */
    private static function looksLikeAPerson(string $value): bool
    {
        if (in_array(mb_strtolower($value), self::NOT_A_PERSON, true)) {
            return false;
        }

        // A file path, a filename, or a version string.
        if (preg_match('/[\\\\\/]|\.(pdf|docx?|indd|tex)$|\d+\.\d+|\((?:Windows|Macintosh|Linux)\)/iu', $value)) {
            return false;
        }

        // Any known tool anywhere in the string, not only as the whole of it.
        foreach (self::NOT_A_PERSON as $tool) {
            if (preg_match('/(?:^|\s)'.preg_quote($tool, '/').'(?:\s|$)/iu', $value)) {
                return false;
            }
        }

        // An author is credited with at least two words — a given name and a
        // family name. "husam" on its own is a login, not a credit.
        if (! preg_match('/\p{L}\s+\p{L}/u', $value)) {
            return false;
        }

        // And it is mostly letters: "DR.Ahmed Saker 2O11" is a scan artefact.
        $letters = preg_match_all('/\p{L}/u', $value);

        return $letters >= 5 && $letters >= mb_strlen($value) * 0.6;
    }

    /**
     * The year the book was published.
     *
     * Only what the opening pages actually say. The file's own dates are not
     * used at all — see below.
     */
    private static function year(string $opening): ?int
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

        // Nothing else. Taking the newest four-digit number on the page reads
        // 1860 off a nursing textbook and 1930 off another — page numbers,
        // figure labels and phone numbers all look like years. Only a date the
        // page marks as one is a date.
        //
        // Deliberately not /CreationDate either: that is when the file was made, and
        // for a scanned book it is the year somebody put it on the scanner.
        // Across a thousand books that would fill the catalogue with dates
        // that look right and are not. A blank the librarian fills in is
        // worth more than a plausible wrong answer.
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

    /**
     * Drop trailing debris from a credit line.
     *
     * OCR turns a year into things like "2O11" (letter O for zero) and leaves
     * it hanging off the end of the name. "DR.Ahmed Saker 2O11" is a real
     * author with rubbish attached, not rubbish.
     */
    private static function trimScanJunk(string $value): string
    {
        // Trailing tokens that mix digits with letters, or are just digits.
        while (preg_match('/^(.*\p{L})\s+[\p{Nd}][\p{L}\p{Nd}]*$/u', $value, $m)) {
            $value = $m[1];
        }

        return trim($value, " \t.,-–—_");
    }

    private static function tidy(string $value): string
    {
        $value = preg_replace('/[\x{0000}-\x{001F}\x{200B}-\x{200F}]/u', '', $value);

        return trim(preg_replace('/\s+/u', ' ', $value));
    }
}
