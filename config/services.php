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

    'quick_books'=> [
        'client_id' => env('QUICK_BOOKS_CLIENT_ID',''),
        'client_secret' => env('QUICK_BOOKS_CLIENT_SECRET',''),
        'auth_redirect_uri' => env('QUICK_BOOKS_AUTH_REDIRECT_URI',''),
        'ouath_scope' => env('QUICK_BOOKS_OAUTH_SCOPE','com.intuit.quickbooks.accounting openid profile email phone address'),
        'mode' => env('QUICK_BOOKS_MODE','development'),
        'webhook_verify_token' => env('QUICK_BOOKS_WEB_HOOKS_VERIFY_TOKEN','')
    ]

];
