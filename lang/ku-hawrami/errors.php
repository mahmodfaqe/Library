<?php

/*
 * The pages a visitor sees when something goes wrong. Laravel's own are in
 * English only, and an official university site should not fall out of the
 * visitor's language at the moment it is least helpful to.
 */

return [
    '404' => ['title' => 'لاپەڕە نەدیاری', 'body' => 'ئا لاپەڕەی تۆ گێڵی پەیش نەمەنێ یان هەرگیز نەبییەن.'],
    '500' => ['title' => 'هەڵەیۆ سێرڤەری', 'body' => 'چیۆ هەڵە لوا. تکا دمای هەوڵ دەرەوە.'],
    '403' => ['title' => 'ڕێپەینەدریا', 'body' => 'مۆڵەتت نییەن پەی ئی لاپەڕەی.'],
    '419' => ['title' => 'وەخت بەسەرشی', 'body' => 'فۆڕم وەختێوی زیات واز بێ. تکا دووبارە هەوڵ دە.'],
    '429' => ['title' => 'داواکاری زۊر', 'body' => 'زۊر داواکاری کەردەن. تکا کەمێو چەوەڕێ کەرە.'],
];
