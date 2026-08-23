<?php

/*
 * Validation messages.
 *
 * Only the rules this application actually uses. Laravel ships English ones,
 * but the fallback locale here is Kurdish, so without these files a form error
 * shows the raw key — "validation.required" — rather than a sentence.
 */

return [
    'required' => 'خانەی :attribute پێویەنە.',
    'string' => 'پەڕێو :attribute دەق بۆ.',
    'integer' => 'پەڕێو :attribute ژمارە بۆ.',
    'email' => 'پەڕێو :attribute ئیمەیلێوی دروست بۆ.',
    'url' => 'پەڕێو :attribute بەستەرێوی دروست بۆ.',
    'confirmed' => 'دووبارەکەردەی :attribute یەک نەگێرا.',
    'exists' => ':attribute هەڵبژیای نەدیاری.',
    'unique' => ':attribute پەی وەختی وەکار گێڵیان.',
    'file' => 'پەڕێو :attribute فایل بۆ.',
    'mimetypes' => 'پەڕێو :attribute جۊرێوی ڕێپەیدریا بۆ.',
    'in' => ':attribute هەڵبژیای دروست نییەن.',
    'current_password' => 'ووشەی نهێنی هەڵەیەن.',

    'max' => [
        'string' => 'نەبۆ :attribute لە :max پیتی زیاتەر بۆ.',
        'numeric' => 'نەبۆ :attribute لە :max زیاتەر بۆ.',
        'file' => 'نەبۆ :attribute لە :max کیلۆبایتی زیاتەر بۆ.',
        'array' => 'نەبۆ :attribute لە :max بڕگەی زیاتەر بۆ.',
    ],

    'min' => [
        'string' => 'پەڕێو :attribute کەمتەرین :min پیت بۆ.',
        'numeric' => 'پەڕێو :attribute کەمتەرین :min بۆ.',
        'file' => 'پەڕێو :attribute کەمتەرین :min کیلۆبایت بۆ.',
        'array' => 'پەڕێو :attribute کەمتەرین :min بڕگە بۆ.',
    ],

    /*
     * Field names, so a message reads "The author field is required" rather
     * than naming the database column.
     */
    'attributes' => [
        'author' => 'نویسەر',
        'category_id' => 'بابەت',
        'cover_url' => 'بەستەری بەرگی',
        'current_password' => 'ووشەی نهێنی ئیسە',
        'department_id' => 'بەش',
        'drive_url' => 'بەستەری درایڤی',
        'email' => 'ئیمەیل',
        'file' => 'فایل',
        'icon' => 'ئایکۆن',
        'language' => 'زوان',
        'message' => 'نامە',
        'name' => 'نام',
        'password' => 'ووشەی نهێنی',
        'role' => 'ڕۆڵ',
        'sort_order' => 'ڕیزبەندی',
        'title' => 'ناونیشان',
        'url' => 'بەستەر',
        'year' => 'ساڵ',
    ],
];
