<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Stripe, Mailgun, SparkPost and others. This file provides a sane
    | default location for this type of information, allowing packages
    | to have a conventional place to find your various credentials.
    |
    */

    'prem' => [
        'id'  => env('PREM_CUSTOMER_ID'),
        'pin' => env('PREM_PIN'),
    ],

    'tmdb' => [
        'api_key' => env('TMDB_API_KEY'),
    ],
];
