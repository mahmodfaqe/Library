<?php

/*
 * The pages a visitor sees when something goes wrong. Laravel's own are in
 * English only, and an official university site should not fall out of the
 * visitor's language at the moment it is least helpful to.
 */

return [
    '404' => ['title' => 'صفحه یافت نشد', 'body' => 'صفحه‌ای که دنبال آن هستید جابه‌جا شده یا هرگز وجود نداشته است.'],
    '500' => ['title' => 'خطای سرور', 'body' => 'مشکلی پیش آمد. لطفاً بعداً دوباره تلاش کنید.'],
    '403' => ['title' => 'مجاز نیست', 'body' => 'شما اجازهٔ دسترسی به این صفحه را ندارید.'],
    '419' => ['title' => 'صفحه منقضی شد', 'body' => 'فرم مدت زیادی باز بود. لطفاً دوباره تلاش کنید.'],
    '429' => ['title' => 'درخواست‌های بیش از حد', 'body' => 'درخواست‌های زیادی فرستاده‌اید. لطفاً کمی صبر کنید.'],
];
