<?php

/*
 * Validation messages.
 *
 * Only the rules this application actually uses. Laravel ships English ones,
 * but the fallback locale here is Kurdish, so without these files a form error
 * shows the raw key — "validation.required" — rather than a sentence.
 */

return [
    'required' => 'Xana :attribute pêdivî ye.',
    'string' => 'Divê :attribute deq be.',
    'integer' => 'Divê :attribute jimare be.',
    'email' => 'Divê :attribute e-nameyeke rast be.',
    'url' => 'Divê :attribute girêdaneke rast be.',
    'confirmed' => 'Dubarekirina :attribute êk nagirin.',
    'exists' => ':attribute ya hilbijartî nehate dîtin.',
    'unique' => ':attribute berî niha hatiye bikaranîn.',
    'file' => 'Divê :attribute fayl be.',
    'mimetypes' => 'Divê :attribute cûreyekê rêpêdayî be.',
    'in' => ':attribute ya hilbijartî ne rast e.',
    'current_password' => 'Peyva nihênî şaş e.',

    'max' => [
        'string' => 'Nabe :attribute ji :max tîpan zêdetir be.',
        'numeric' => 'Nabe :attribute ji :max zêdetir be.',
        'file' => 'Nabe :attribute ji :max kîlobaytan zêdetir be.',
        'array' => 'Nabe :attribute ji :max bircan zêdetir be.',
    ],

    'min' => [
        'string' => 'Divê :attribute bi kêmî :min tîp be.',
        'numeric' => 'Divê :attribute bi kêmî :min be.',
        'file' => 'Divê :attribute bi kêmî :min kîlobayt be.',
        'array' => 'Divê :attribute bi kêmî :min birc be.',
    ],

    /*
     * Field names, so a message reads "The author field is required" rather
     * than naming the database column.
     */
    'attributes' => [
        'author' => 'Nivîser',
        'category_id' => 'Babet',
        'cover_url' => 'Girêdana bergî',
        'current_password' => 'Peyva nihênî ya niha',
        'department_id' => 'Beş',
        'drive_url' => 'Girêdana Drive',
        'email' => 'E-name',
        'file' => 'Fayl',
        'icon' => 'Îkon',
        'language' => 'Ziman',
        'message' => 'Name',
        'name' => 'Nav',
        'password' => 'Peyva nihênî',
        'role' => 'Rol',
        'sort_order' => 'Rêzbendî',
        'title' => 'Navnîşan',
        'url' => 'Girêdan',
        'year' => 'Sal',
    ],
];
