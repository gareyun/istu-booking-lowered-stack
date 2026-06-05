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

    'vk' => [
        'access_token' => env('VK_ACCESS_TOKEN'),
        'community_id' => env('VK_COMMUNITY_ID'),
    ],
    'google_service_account' => [
        'path' => env('GOOGLE_SERVICE_ACCOUNT_PATH'),
    ],

    'calendar_ids' => [
        '9-2' => env('GOOGLE_CALENDAR_ROOM_9_2'),
        'floor_2' => env('GOOGLE_CALENDAR_ROOM_floor_2'),
    ],

];
