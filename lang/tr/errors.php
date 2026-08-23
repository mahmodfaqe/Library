<?php

/*
 * The pages a visitor sees when something goes wrong. Laravel's own are in
 * English only, and an official university site should not fall out of the
 * visitor's language at the moment it is least helpful to.
 */

return [
    '404' => ['title' => 'Sayfa bulunamadı', 'body' => 'Aradığınız sayfa taşınmış veya hiç var olmamış.'],
    '500' => ['title' => 'Sunucu hatası', 'body' => 'Bir şeyler ters gitti. Lütfen daha sonra tekrar deneyin.'],
    '403' => ['title' => 'İzin yok', 'body' => 'Bu sayfa için yetkiniz yok.'],
    '419' => ['title' => 'Sayfanın süresi doldu', 'body' => 'Form çok uzun süre açık kaldı. Lütfen tekrar deneyin.'],
    '429' => ['title' => 'Çok fazla istek', 'body' => 'Çok fazla istek gönderdiniz. Lütfen biraz bekleyin.'],
];
