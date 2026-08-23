<?php

/*
 * The pages a visitor sees when something goes wrong. Laravel's own are in
 * English only, and an official university site should not fall out of the
 * visitor's language at the moment it is least helpful to.
 */

return [
    '404' => ['title' => 'پەڕەکە نەدۆزرایەوە', 'body' => 'ئەو پەڕەیەی بەدوایدا دەگەڕێیت نەماوە یان هەرگیز نەبووە.'],
    '500' => ['title' => 'هەڵەیەکی سێرڤەر', 'body' => 'شتێک هەڵەی تێکەوت. تکایە دواتر هەوڵ بدەرەوە.'],
    '403' => ['title' => 'ڕێگەپێنەدراو', 'body' => 'مۆڵەتت نییە بۆ ئەم پەڕەیە.'],
    '419' => ['title' => 'کاتەکە بەسەرچوو', 'body' => 'فۆڕمەکە زۆر ماوەیەک کراوە بوو. تکایە دووبارە هەوڵ بدە.'],
    '429' => ['title' => 'داواکاری زۆر', 'body' => 'زۆر داواکاریت ناردووە. تکایە کەمێک چاوەڕێ بکە.'],
];
