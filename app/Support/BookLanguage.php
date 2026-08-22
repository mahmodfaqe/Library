<?php

namespace App\Support;

class BookLanguage
{
    /**
     * The collection separates each subject into language folders, but those
     * folders are spelled inconsistently — Arabic alone appears as عربي,
     * عرەبي, عرەبی, عەرەبی and عربی. Everything is folded to one canonical
     * name so the catalogue can filter and display it.
     */
    private const CANONICAL = [
        'کوردی' => ['کوردی', 'kurdi', 'kurdish', 'sorani', 'badini', 'kurmanci'],
        'عەرەبی' => ['عربی', 'عربي', 'arabi', 'arabic'],
        // انگلیزی without the hamza is as common as ئینگلیزی in folder names.
        'English' => ['english', 'ingilizi', 'ئینگلیزی', 'انگلیزی', 'engilizi', 'inglizi'],
        'فارسی' => ['فارسی', 'farsi', 'persian'],
        'Türkçe' => ['turkce', 'turkish', 'تورکی', 'تركي'],
    ];

    /**
     * Short forms that are a whole folder name on their own. They are matched
     * exactly, not as a substring: "en" appears inside far too many words to
     * be safe anywhere else.
     */
    private const ABBREVIATIONS = [
        'en' => 'English',
        'eng' => 'English',
        'ar' => 'عەرەبی',
        'ku' => 'کوردی',
        'krd' => 'کوردی',
        'fa' => 'فارسی',
        'tr' => 'Türkçe',
    ];

    /**
     * The order the languages are shelved in on the catalogue page. Kurdish
     * leads because it is the university's own language; anything not listed
     * here follows, in alphabetical order.
     *
     * @var list<string>
     */
    public const ORDER = ['کوردی', 'English', 'عەرەبی', 'فارسی', 'Türkçe'];

    /**
     * Where a language sits in the shelving order. Unlisted languages sort
     * after every listed one.
     */
    public static function rank(?string $language): int
    {
        $position = array_search($language, self::ORDER, true);

        return $position === false ? count(self::ORDER) : $position;
    }

    /**
     * The canonical language for a folder name, or null when the folder mixes
     * several — a few do, and guessing one would be wrong.
     */
    public static function fromFolder(?string $name): ?string
    {
        $folded = ArabicText::fold($name);

        if ($folded === '') {
            return null;
        }

        // "EN", "1-AR": a bare abbreviation, once the ordering number is gone.
        $bare = trim(preg_replace('/^\d+\s*/u', '', $folded));

        if (isset(self::ABBREVIATIONS[$bare])) {
            return self::ABBREVIATIONS[$bare];
        }

        // "عربی-ئینگلیزی-کوردی" and the like hold more than one language.
        $matches = [];

        foreach (self::CANONICAL as $canonical => $spellings) {
            foreach ($spellings as $spelling) {
                if (str_contains(self::squeeze($folded), self::squeeze(ArabicText::fold($spelling)))) {
                    $matches[$canonical] = true;
                    break;
                }
            }
        }

        return count($matches) === 1 ? array_key_first($matches) : null;
    }

    /**
     * Arabic is written both with and without the vowel between letters, so
     * dropping it lets عربی, عرهبی and عهرهبی compare equal.
     */
    private static function squeeze(string $value): string
    {
        return str_replace(['ه', ' '], '', $value);
    }
}
