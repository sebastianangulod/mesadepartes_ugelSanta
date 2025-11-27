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
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    # Servicios de APIs Peruanas
    'reniec' => [
        'api_url' => env('RENIEC_API_URL', 'https://api.apis.net.pe/v1/dni'),
        'api_token' => env('RENIEC_API_TOKEN'),
        'enabled' => env('RENIEC_API_ENABLED', false),
        'timeout' => 30,
        'retry_attempts' => 3,
        'cache_ttl' => 3600, // 1 hora
    ],

    'sunat' => [
        'api_url' => env('SUNAT_API_URL', 'https://api.apis.net.pe/v1/ruc'),
        'api_token' => env('SUNAT_API_TOKEN'),
        'enabled' => env('SUNAT_API_ENABLED', false),
        'timeout' => 30,
        'retry_attempts' => 3,
        'cache_ttl' => 3600, // 1 hora
    ],

    'recaptcha' => [
        'site_key' => env('RECAPTCHA_SITE_KEY'),
        'secret_key' => env('RECAPTCHA_SECRET_KEY'),
        'enabled' => env('RECAPTCHA_ENABLED', false),
        'score_threshold' => 0.5,
        'timeout' => 10,
    ],

];
