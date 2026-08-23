<?php

/*
 * The pages a visitor sees when something goes wrong. Laravel's own are in
 * English only, and an official university site should not fall out of the
 * visitor's language at the moment it is least helpful to.
 */

return [
    '404' => ['title' => 'رویپەل نەهاتە دیتن', 'body' => 'ئەو رویپەلا تو ل دویڤ دگەڕی نەما یان چ دەم نەبوو.'],
    '500' => ['title' => 'خەلەتیەکا سێرڤەری', 'body' => 'تشتەک خەلەت چوو. ژ کەرەما خۆ پاشی هەول بدە.'],
    '403' => ['title' => 'ڕێپێنەدای', 'body' => 'تە دەستویری نینە بۆ ڤێ رویپەلێ.'],
    '419' => ['title' => 'دەم بهورت', 'body' => 'فۆرم دەمەکێ درێژ ڤەکری بوو. ژ کەرەما خۆ دیسا هەول بدە.'],
    '429' => ['title' => 'داخوازێن پر', 'body' => 'تە پر داخواز شاندن. ژ کەرەما خۆ هندەک بسەکنە.'],
];
