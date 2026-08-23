<?php

/*
 * The pages a visitor sees when something goes wrong. Laravel's own are in
 * English only, and an official university site should not fall out of the
 * visitor's language at the moment it is least helpful to.
 */

return [
    '404' => ['title' => 'Page not found', 'body' => 'The page you are looking for has moved or never existed.'],
    '500' => ['title' => 'Server error', 'body' => 'Something went wrong. Please try again later.'],
    '403' => ['title' => 'Not allowed', 'body' => 'You do not have permission for this page.'],
    '419' => ['title' => 'The page expired', 'body' => 'The form was open too long. Please try again.'],
    '429' => ['title' => 'Too many requests', 'body' => 'You have sent too many requests. Please wait a moment.'],
];
