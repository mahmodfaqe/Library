<?php

/*
 * Validation messages.
 *
 * Only the rules this application actually uses. Laravel ships English ones,
 * but the fallback locale here is Kurdish, so without these files a form error
 * shows the raw key — "validation.required" — rather than a sentence.
 */

return [
    'required' => 'خانا :attribute پێدڤییە.',
    'string' => 'دڤێت :attribute دەق بیت.',
    'integer' => 'دڤێت :attribute ژمارە بیت.',
    'email' => 'دڤێت :attribute ئیمەیلەکێ دروست بیت.',
    'url' => 'دڤێت :attribute گرێدانەکا دروست بیت.',
    'confirmed' => 'دووبارەکرنا :attribute ئێک ناگرن.',
    'exists' => ':attribute یا هەلبژارتی نەهاتە دیتن.',
    'unique' => ':attribute بەری نوکە هاتیە بکارئینان.',
    'file' => 'دڤێت :attribute فایل بیت.',
    'mimetypes' => 'دڤێت :attribute جورەکێ ڕێپێدای بیت.',
    'in' => ':attribute یا هەلبژارتی نە دروستە.',
    'current_password' => 'پەیڤا نهێنی یا شاشە.',

    'max' => [
        'string' => 'نابیت :attribute ژ :max پیتان زێدەتر بیت.',
        'numeric' => 'نابیت :attribute ژ :max زێدەتر بیت.',
        'file' => 'نابیت :attribute ژ :max کیلۆبایتان زێدەتر بیت.',
        'array' => 'نابیت :attribute ژ :max بڕگەیان زێدەتر بیت.',
    ],

    'min' => [
        'string' => 'دڤێت :attribute بەلاکێمی :min پیت بیت.',
        'numeric' => 'دڤێت :attribute بەلاکێمی :min بیت.',
        'file' => 'دڤێت :attribute بەلاکێمی :min کیلۆبایت بیت.',
        'array' => 'دڤێت :attribute بەلاکێمی :min بڕگە بیت.',
    ],

    /*
     * Field names, so a message reads "The author field is required" rather
     * than naming the database column.
     */
    'attributes' => [
        'author' => 'نڤیسەر',
        'category_id' => 'بابەت',
        'cover_url' => 'گرێدانا بەرگی',
        'current_password' => 'پەیڤا نهێنی یا نوکە',
        'department_id' => 'بەش',
        'drive_url' => 'گرێدانا درایڤی',
        'email' => 'ئیمەیل',
        'file' => 'فایل',
        'icon' => 'ئایکۆن',
        'language' => 'زمان',
        'message' => 'نامە',
        'name' => 'ناڤ',
        'password' => 'پەیڤا نهێنی',
        'role' => 'ڕۆل',
        'sort_order' => 'ڕێزبەندی',
        'title' => 'ناڤنیشان',
        'url' => 'گرێدان',
        'year' => 'سال',
    ],
];
