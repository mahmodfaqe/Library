<?php

namespace App\Support;

class ArabicText
{
    /**
     * Letters that differ between Arabic and Kurdish/Persian keyboards but
     * mean the same thing to a reader. Book titles in the collection use both,
     * so search has to treat them as one.
     */
    private const LETTERS = [
        'ك' => 'ک',   // Arabic kaf  -> keheh
        'ي' => 'ی',   // Arabic yeh  -> farsi yeh
        'ى' => 'ی',   // alef maksura
        'ئ' => 'ی',
        'ة' => 'ه',   // teh marbuta
        'أ' => 'ا',
        'إ' => 'ا',
        'آ' => 'ا',
        'ٱ' => 'ا',
        'ؤ' => 'و',
        'ۆ' => 'و',
        'ڕ' => 'ر',
        'ڵ' => 'ل',
        'ێ' => 'ی',
        'ە' => 'ه',
        'ھ' => 'ه',
        'ۊ' => 'و',
        'ﻻ' => 'لا',
    ];

    private const DIGITS = [
        '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
        '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
        '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
        '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
    ];

    /**
     * Fold a title or a search term to one comparable form: a single spelling
     * per letter, no diacritics, ASCII digits, lower case, tidy spacing.
     */
    public static function fold(?string $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        // Harakat, tatweel and the presentation-form joiners carry no meaning
        // for matching and appear inconsistently in scanned filenames.
        $value = preg_replace('/[\x{064B}-\x{065F}\x{0670}\x{0640}\x{200C}\x{200D}\x{200E}\x{200F}]/u', '', $value);

        $value = strtr($value, self::LETTERS);
        $value = strtr($value, self::DIGITS);
        $value = mb_strtolower($value, 'UTF-8');

        // Punctuation varies between scans of the same book.
        $value = preg_replace('/[^\p{L}\p{N}\s]+/u', ' ', $value);

        return trim(preg_replace('/\s+/u', ' ', $value));
    }
}
