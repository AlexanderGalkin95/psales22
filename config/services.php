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

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'google' => [
        'enabled' => env('GOOGLE_APPLICATION_ENABLED', false),
        'service_account_auth' => json_decode(env('GOOGLE_SERVICE_CREDENTIALS_JSON', "{}"), true),
        'spreadsheet' => env('GOOGLE_SPREADSHEET', ''),
        'application_name' => env('GOOGLE_APPLICATION_NAME', ''),
    ],

    'amoCRM' => [
        'redirect_uri' => env('AMOCRM_REDIRECT_URI', ''),
    ],

    'smsru' => [
        'baseUrl' => env('SMSRU_API_URL', ''),
        'api_id' => env('SMSRU_API_ID', 'none'),
        'sender' => env('SMSRU_API_SENDER', ''),
    ],
    'telegram-bot-api' => [
        'token' => env('TELEGRAM_BOT_TOKEN', 'YOUR BOT TOKEN HERE'),
    ],
];
