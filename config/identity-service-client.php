<?php

use SMSkin\IdentityServiceClient\Enums\Scope;
use SMSkin\IdentityServiceClient\Models\User;

return [
    'host' => env('IDENTITY_SERVICE_CLIENT_HOST'),
    'debug' => env('IDENTITY_SERVICE_CLIENT_DEBUG', false),
    'parser' => [
        'cookies' => [
            'decrypt' => env('IDENTITY_SERVICE_CLIENT_PARSER_COOKIES_DECRYPT', false)
        ]
    ],
    'classes' => [
        'models' => [
            'user' => User::class
        ]
    ],
    'scopes' => [
        'initial' => Scope::SYSTEM_CHANGE_SCOPES,
        'uses' => [
            Scope::IDENTITY_SERVICE_LOGIN
        ]
    ],
    'guards' => [
        'jwt' => [
            'name' => 'identity-service-client-jwt-guard',
            'driver' => [
                'name' => 'identity-service-client-jwt'
            ]
        ],
        'session' => [
            'name' => 'identity-service-client-session-guard',
            'driver' => [
                'name' => 'identity-service-client-session'
            ]
        ]
    ],
    'api' => [
        'token' => env('IDENTITY_SERVICE_CLIENT_API_TOKEN')
    ]
];
