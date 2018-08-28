<?php

return [

    'scanner' => [
        'guessit' => [
            'path' => env('SCANNER_GUESSIT_PATH', '/usr/local/bin/guessit'),
        ],

        'ignore_after' => 3, // scan fails
    ],

    'users' => [
        'registration' => env('USERS_REGISTRATION_OPEN', false),
    ],

    'files' => [
        // files with these extensions will be added to files database
        'supported' => [
            'mkv',
            'mp4',
            'avi',
        ],

        // files with these extensions form database will be added to movies and episodes
        'videos'    => [
            'mkv',
            'mp4',
            'avi',
        ],
    ],

    'tmdb' => [
        'cache' => storage_path('app/cache/tmdb-api'),
    ],

    'downloads' => [
        'enabled' => env('DOWNLOADS_ENABLED', false),

        'enable_for_new_media' => env('DOWNLOADS_ENABLE_FOR_NEW_MEDIA', false),

        'folders' => [
            'movies' => sprintf('%s/movies', env('DOWNLOADS_MEDIA_ROOT', storage_path('app/media/'))),
            'shows'  => sprintf('%s/shows', env('DOWNLOADS_MEDIA_ROOT', storage_path('app/media/'))),
        ],

        'aria2' => [
            'host'  => env('DOWNLOADS_ARIA2_HOST', 'http://localhost:6800'),
            'token' => env('DOWNLOADS_ARIA2_TOKEN'),
        ],
    ],

    'artisan_path' => env('ARTISAN_PATH', null),
];
