<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],
    'news_keywords' => env('NEWS_API_KEYWORDS'),
    'news_lang' => env('NEWS_API_LANGUAGE'),
    'news_api' => [
        'api_key' => env('NEWS_API_KEY'),
        'daily_api_limit' => env('NEWS_API_DAILY_LIMIT', 100),
        'sources' => env('NEWS_API_SOURCES'),
    ],
    'guardian_api' => [
        'api_key' => env('GUARDIAN_API_KEY'),
        'daily_api_limit' => env('GUARDIAN_API_DAILY_LIMIT'),
    ],
    'nytimes_api' => [
        'api_key' => env('NY_TIMES_API_KEY'),
        'daily_api_limit' => env('NY_TIMES_API_DAILY_LIMIT',100),
        'api_rate_per_minute' => env('NY_TIMES_API_RATE_PER_MINUTE', 10),
    ]

];
