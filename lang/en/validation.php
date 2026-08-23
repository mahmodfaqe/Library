<?php

/*
 * Validation messages.
 *
 * Only the rules this application actually uses. Laravel ships English ones,
 * but the fallback locale here is Kurdish, so without these files a form error
 * shows the raw key — "validation.required" — rather than a sentence.
 */

return [
    'required' => 'The :attribute field is required.',
    'string' => 'The :attribute must be text.',
    'integer' => 'The :attribute must be a number.',
    'email' => 'The :attribute must be a valid email address.',
    'url' => 'The :attribute must be a valid link.',
    'confirmed' => 'The :attribute confirmation does not match.',
    'exists' => 'The selected :attribute was not found.',
    'unique' => 'That :attribute is already taken.',
    'file' => 'The :attribute must be a file.',
    'mimetypes' => 'The :attribute must be an accepted file type.',
    'in' => 'The selected :attribute is not valid.',
    'current_password' => 'The password is incorrect.',

    'max' => [
        'string' => 'The :attribute may not be longer than :max characters.',
        'numeric' => 'The :attribute may not be greater than :max.',
        'file' => 'The :attribute may not be larger than :max kilobytes.',
        'array' => 'The :attribute may not have more than :max items.',
    ],

    'min' => [
        'string' => 'The :attribute must be at least :min characters.',
        'numeric' => 'The :attribute must be at least :min.',
        'file' => 'The :attribute must be at least :min kilobytes.',
        'array' => 'The :attribute must have at least :min items.',
    ],

    /*
     * Field names, so a message reads "The author field is required" rather
     * than naming the database column.
     */
    'attributes' => [
        'author' => 'author',
        'category_id' => 'subject',
        'cover_url' => 'cover link',
        'current_password' => 'current password',
        'department_id' => 'department',
        'drive_url' => 'Drive link',
        'email' => 'email',
        'file' => 'file',
        'icon' => 'icon',
        'language' => 'language',
        'message' => 'message',
        'name' => 'name',
        'password' => 'password',
        'role' => 'role',
        'sort_order' => 'order',
        'title' => 'title',
        'url' => 'link',
        'year' => 'year',
    ],
];
