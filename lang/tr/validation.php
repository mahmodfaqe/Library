<?php

/*
 * Validation messages.
 *
 * Only the rules this application actually uses. Laravel ships English ones,
 * but the fallback locale here is Kurdish, so without these files a form error
 * shows the raw key — "validation.required" — rather than a sentence.
 */

return [
    'required' => ':attribute alanı zorunludur.',
    'string' => ':attribute metin olmalıdır.',
    'integer' => ':attribute bir sayı olmalıdır.',
    'email' => ':attribute geçerli bir e-posta olmalıdır.',
    'url' => ':attribute geçerli bir bağlantı olmalıdır.',
    'confirmed' => ':attribute doğrulaması eşleşmiyor.',
    'exists' => 'Seçilen :attribute bulunamadı.',
    'unique' => 'Bu :attribute zaten kullanılıyor.',
    'file' => ':attribute bir dosya olmalıdır.',
    'mimetypes' => ':attribute izin verilen bir türde olmalıdır.',
    'in' => 'Seçilen :attribute geçerli değil.',
    'current_password' => 'Parola yanlış.',

    'max' => [
        'string' => ':attribute en fazla :max karakter olabilir.',
        'numeric' => ':attribute en fazla :max olabilir.',
        'file' => ':attribute en fazla :max kilobayt olabilir.',
        'array' => ':attribute en fazla :max öğe olabilir.',
    ],

    'min' => [
        'string' => ':attribute en az :min karakter olmalıdır.',
        'numeric' => ':attribute en az :min olmalıdır.',
        'file' => ':attribute en az :min kilobayt olmalıdır.',
        'array' => ':attribute en az :min öğe olmalıdır.',
    ],

    /*
     * Field names, so a message reads "The author field is required" rather
     * than naming the database column.
     */
    'attributes' => [
        'author' => 'yazar',
        'category_id' => 'konu',
        'cover_url' => 'kapak bağlantısı',
        'current_password' => 'mevcut parola',
        'department_id' => 'bölüm',
        'drive_url' => 'Drive bağlantısı',
        'email' => 'e-posta',
        'file' => 'dosya',
        'icon' => 'simge',
        'language' => 'dil',
        'message' => 'mesaj',
        'name' => 'ad',
        'password' => 'parola',
        'role' => 'rol',
        'sort_order' => 'sıra',
        'title' => 'başlık',
        'url' => 'bağlantı',
        'year' => 'yıl',
    ],
];
