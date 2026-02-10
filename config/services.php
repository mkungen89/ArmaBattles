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

    'steam' => [
        'client_id' => null,
        'client_secret' => env('STEAM_CLIENT_SECRET'),
        'redirect' => env('APP_URL').'/auth/steam/callback',
        'allowed_hosts' => [
            env('APP_URL'),
        ],
    ],

    'battlemetrics' => [
        'token' => env('BATTLEMETRICS_API_TOKEN'),
        'server_id' => env('BATTLEMETRICS_SERVER_ID', '0'),
    ],

    'armabattles' => [
        'api_token' => env('ARMABATTLES_API_TOKEN'),
    ],

    'rcon' => [
        'api_url' => env('RCON_API_URL', 'http://game.armabattles.com:3001'),
        'api_key' => env('RCON_API_KEY'),
    ],

    'gameserver' => [
        'url' => env('GAMESERVER_MANAGER_URL'),
        'key' => env('GAMESERVER_MANAGER_KEY'),
    ],

];
