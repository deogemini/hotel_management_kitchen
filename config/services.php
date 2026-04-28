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

    'flex_sms' => [
        'base_url' => env('FLEX_SMS_BASE_URL', 'https://sms.flex.co.tz'),
        'client_id' => env('FLEX_SMS_CLIENT_ID', 'F00102'),
        'client_secret' => env('FLEX_SMS_CLIENT_SECRET', '41274e60-a864-46e9-9ef6-12rf54tg'),
        'sender_id' => env('FLEX_SMS_SENDER_ID', 'Flex'),
    ],

];
