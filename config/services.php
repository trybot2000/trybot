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

    'mailgun'   => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
    ],

    'ses'       => [
        'key'    => env('SES_KEY'),
        'secret' => env('SES_SECRET'),
        'region' => 'us-east-1',
    ],

    'sparkpost' => [
        'secret' => env('SPARKPOST_SECRET'),
    ],

    'stripe'    => [
        'model'  => App\User::class,
        'key'    => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
    ],

    'slack'     => [
        'legacy_token' => env('SLACK_LEGACY_TOKEN'),
        'users'        => [
            'trybot' => env('SLACK_APP_TRYBOT_OAUTH_ACCESS_TOKEN'),
        ],
    ],

    'google'    => [
        'knowledge_graph' => env('GOOGLE_KNOWLEDGE_GRAPH_TOKEN'),
        'time_zone_api'   => env('GOOGLE_TIME_ZONE_API_KEY'),
        'geocoding'   => env('GOOGLE_GEOCODING_API_KEY'),
    ],

    'api_ai'    => [
        'trybot' => env('API_AI_CLIENT_ACCESS_TOKEN'),
    ],

];
