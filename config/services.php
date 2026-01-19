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

    /*
    |--------------------------------------------------------------------------
    | Career Matcher NLP Service
    |--------------------------------------------------------------------------
    |
    | Configuración del microservicio Python que usa NLP para comparar
    | carreras académicas. Se usa para validar carreras afines declaradas
    | por postulantes que no están en el catálogo de carreras mapeadas.
    |
    */
    'career_matcher' => [
        'url' => env('CAREER_MATCHER_URL', 'http://localhost:8000'),
        'threshold' => env('CAREER_MATCHER_THRESHOLD', 0.75),
        'timeout' => env('CAREER_MATCHER_TIMEOUT', 10),
        'cache_enabled' => env('CAREER_MATCHER_CACHE_ENABLED', true),
        'cache_ttl' => env('CAREER_MATCHER_CACHE_TTL', 86400), // 24 horas
    ],

];
