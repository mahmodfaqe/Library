<?php

/*
 * Validation messages.
 *
 * Only the rules this application actually uses. Laravel ships English ones,
 * but the fallback locale here is Kurdish, so without these files a form error
 * shows the raw key — "validation.required" — rather than a sentence.
 */

return [
    'required' => 'حقل :attribute مطلوب.',
    'string' => 'يجب أن يكون :attribute نصًا.',
    'integer' => 'يجب أن يكون :attribute رقمًا.',
    'email' => 'يجب أن يكون :attribute بريدًا إلكترونيًا صحيحًا.',
    'url' => 'يجب أن يكون :attribute رابطًا صحيحًا.',
    'confirmed' => 'تأكيد :attribute غير مطابق.',
    'exists' => ':attribute المحدد غير موجود.',
    'unique' => ':attribute مستخدم من قبل.',
    'file' => 'يجب أن يكون :attribute ملفًا.',
    'mimetypes' => 'يجب أن يكون :attribute من نوع مسموح به.',
    'in' => ':attribute المحدد غير صالح.',
    'current_password' => 'كلمة المرور غير صحيحة.',

    'max' => [
        'string' => 'يجب ألا يزيد :attribute عن :max حرفًا.',
        'numeric' => 'يجب ألا يزيد :attribute عن :max.',
        'file' => 'يجب ألا يزيد :attribute عن :max كيلوبايت.',
        'array' => 'يجب ألا يزيد :attribute عن :max عنصرًا.',
    ],

    'min' => [
        'string' => 'يجب ألا يقل :attribute عن :min حرفًا.',
        'numeric' => 'يجب ألا يقل :attribute عن :min.',
        'file' => 'يجب ألا يقل :attribute عن :min كيلوبايت.',
        'array' => 'يجب ألا يقل :attribute عن :min عنصرًا.',
    ],

    /*
     * Field names, so a message reads "The author field is required" rather
     * than naming the database column.
     */
    'attributes' => [
        'author' => 'المؤلف',
        'category_id' => 'الموضوع',
        'cover_url' => 'رابط الغلاف',
        'current_password' => 'كلمة المرور الحالية',
        'department_id' => 'القسم',
        'drive_url' => 'رابط Drive',
        'email' => 'البريد الإلكتروني',
        'file' => 'الملف',
        'icon' => 'الأيقونة',
        'language' => 'اللغة',
        'message' => 'الرسالة',
        'name' => 'الاسم',
        'password' => 'كلمة المرور',
        'role' => 'الدور',
        'sort_order' => 'الترتيب',
        'title' => 'العنوان',
        'url' => 'الرابط',
        'year' => 'السنة',
    ],
];
