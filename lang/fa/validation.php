<?php

/*
 * Validation messages.
 *
 * Only the rules this application actually uses. Laravel ships English ones,
 * but the fallback locale here is Kurdish, so without these files a form error
 * shows the raw key — "validation.required" — rather than a sentence.
 */

return [
    'required' => 'فیلد :attribute الزامی است.',
    'string' => ':attribute باید متن باشد.',
    'integer' => ':attribute باید عدد باشد.',
    'email' => ':attribute باید یک ایمیل معتبر باشد.',
    'url' => ':attribute باید یک پیوند معتبر باشد.',
    'confirmed' => 'تأیید :attribute مطابقت ندارد.',
    'exists' => ':attribute انتخاب‌شده یافت نشد.',
    'unique' => 'این :attribute قبلاً استفاده شده است.',
    'file' => ':attribute باید یک فایل باشد.',
    'mimetypes' => ':attribute باید از نوع مجاز باشد.',
    'in' => ':attribute انتخاب‌شده معتبر نیست.',
    'current_password' => 'گذرواژه نادرست است.',

    'max' => [
        'string' => ':attribute نباید بیش از :max نویسه باشد.',
        'numeric' => ':attribute نباید بیش از :max باشد.',
        'file' => ':attribute نباید بیش از :max کیلوبایت باشد.',
        'array' => ':attribute نباید بیش از :max مورد باشد.',
    ],

    'min' => [
        'string' => ':attribute باید حداقل :min نویسه باشد.',
        'numeric' => ':attribute باید حداقل :min باشد.',
        'file' => ':attribute باید حداقل :min کیلوبایت باشد.',
        'array' => ':attribute باید حداقل :min مورد باشد.',
    ],

    /*
     * Field names, so a message reads "The author field is required" rather
     * than naming the database column.
     */
    'attributes' => [
        'author' => 'نویسنده',
        'category_id' => 'موضوع',
        'cover_url' => 'پیوند جلد',
        'current_password' => 'گذرواژهٔ فعلی',
        'department_id' => 'بخش',
        'drive_url' => 'پیوند Drive',
        'email' => 'ایمیل',
        'file' => 'فایل',
        'icon' => 'نماد',
        'language' => 'زبان',
        'message' => 'پیام',
        'name' => 'نام',
        'password' => 'گذرواژه',
        'role' => 'نقش',
        'sort_order' => 'ترتیب',
        'title' => 'عنوان',
        'url' => 'پیوند',
        'year' => 'سال',
    ],
];
