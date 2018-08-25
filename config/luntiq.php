<?php

return [
    'users' => [
        'registration' => env('REGISTRATION_OPEN', false),
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

    'downloads' => [
        'enable_for_new_media' => env('DOWNLOADS_ENABLE_FOR_NEW_MEDIA', false),

        'folders' => [
            'movies' => sprintf('%s/movies', env('DOWNLOADS_MEDIA_ROOT', storage_path('app/media/'))),
            'shows'  => sprintf('%s/shows', env('DOWNLOADS_MEDIA_ROOT', storage_path('app/media/'))),
        ],
    ],
];
