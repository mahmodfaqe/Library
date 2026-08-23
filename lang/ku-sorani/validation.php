<?php

/*
 * Validation messages.
 *
 * Only the rules this application actually uses. Laravel ships English ones,
 * but the fallback locale here is Kurdish, so without these files a form error
 * shows the raw key — "validation.required" — rather than a sentence.
 */

return [
    'required' => 'خانەی :attribute پێویستە.',
    'string' => 'دەبێت :attribute دەق بێت.',
    'integer' => 'دەبێت :attribute ژمارە بێت.',
    'email' => 'دەبێت :attribute ئیمەیلێکی دروست بێت.',
    'url' => 'دەبێت :attribute بەستەرێکی دروست بێت.',
    'confirmed' => 'دووبارەکردنەوەی :attribute یەک ناگرنەوە.',
    'exists' => ':attribute ی هەڵبژێردراو نەدۆزرایەوە.',
    'unique' => ':attribute پێشتر بەکارهێنراوە.',
    'file' => 'دەبێت :attribute فایل بێت.',
    'mimetypes' => 'دەبێت :attribute جۆرێکی ڕێپێدراو بێت.',
    'in' => ':attribute ی هەڵبژێردراو دروست نییە.',
    'current_password' => 'وشەی نهێنی هەڵەیە.',

    'max' => [
        'string' => 'نابێت :attribute لە :max پیت زیاتر بێت.',
        'numeric' => 'نابێت :attribute لە :max زیاتر بێت.',
        'file' => 'نابێت :attribute لە :max کیلۆبایت زیاتر بێت.',
        'array' => 'نابێت :attribute لە :max بڕگە زیاتر بێت.',
    ],

    'min' => [
        'string' => 'دەبێت :attribute لانیکەم :min پیت بێت.',
        'numeric' => 'دەبێت :attribute لانیکەم :min بێت.',
        'file' => 'دەبێت :attribute لانیکەم :min کیلۆبایت بێت.',
        'array' => 'دەبێت :attribute لانیکەم :min بڕگە بێت.',
    ],

    /*
     * Field names, so a message reads "The author field is required" rather
     * than naming the database column.
     */
    'attributes' => [
        'author' => 'نووسەر',
        'category_id' => 'بابەت',
        'cover_url' => 'بەستەری بەرگ',
        'current_password' => 'وشەی نهێنی ئێستا',
        'department_id' => 'بەش',
        'drive_url' => 'بەستەری درایڤ',
        'email' => 'ئیمەیل',
        'file' => 'فایل',
        'icon' => 'ئایکۆن',
        'language' => 'زمان',
        'message' => 'نامە',
        'name' => 'ناو',
        'password' => 'وشەی نهێنی',
        'role' => 'ڕۆڵ',
        'sort_order' => 'ڕیزبەندی',
        'title' => 'ناونیشان',
        'url' => 'بەستەر',
        'year' => 'ساڵ',
    ],
];
