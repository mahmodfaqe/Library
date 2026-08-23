<?php

/*
 * The pages a visitor sees when something goes wrong. Laravel's own are in
 * English only, and an official university site should not fall out of the
 * visitor's language at the moment it is least helpful to.
 */

return [
    '404' => ['title' => 'الصفحة غير موجودة', 'body' => 'الصفحة التي تبحث عنها انتقلت أو لم تكن موجودة.'],
    '500' => ['title' => 'خطأ في الخادم', 'body' => 'حدث خطأ ما. يرجى المحاولة لاحقًا.'],
    '403' => ['title' => 'غير مسموح', 'body' => 'لا تملك صلاحية لهذه الصفحة.'],
    '419' => ['title' => 'انتهت صلاحية الصفحة', 'body' => 'بقي النموذج مفتوحًا مدة طويلة. يرجى المحاولة مرة أخرى.'],
    '429' => ['title' => 'طلبات كثيرة', 'body' => 'لقد أرسلت طلبات كثيرة. يرجى الانتظار قليلاً.'],
];
