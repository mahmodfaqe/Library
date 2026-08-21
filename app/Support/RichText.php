<?php

namespace App\Support;

use Illuminate\Support\HtmlString;

class RichText
{
    /**
     * Splice trusted HTML fragments into a translated line's :placeholders.
     *
     * Translations hold text only, so the line is escaped first and only the
     * fragments passed here can emit markup. This keeps layout in Blade while
     * still allowing a link or an emphasised word inside a sentence.
     */
    public static function make(string $text, array $fragments): HtmlString
    {
        $escaped = e($text);

        foreach ($fragments as $token => $html) {
            $escaped = str_replace(':'.$token, $html, $escaped);
        }

        return new HtmlString($escaped);
    }
}
