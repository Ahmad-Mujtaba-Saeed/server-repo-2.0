<?php
// config/auth.php

return [

    'defaults' => [
        'guard' => 'web',
        'passwords' => 'users',
    ],

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],
        'users' => [
            'driver' => 'session',
            'provider' => 'user',
        ],
        'api' => [
            'driver' => 'token',
            'provider' => 'users',
            'hash' => false,
        ]
        ],

        'providers' => [
            'users' => [
                'driver' => 'eloquent',
                'model' => App\Models\User::class,
            ],
           'user' => [
                'driver' => 'eloquent',
                'model' => App\Models\users::class,
            ]
           ],

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => 'password_resets',
            'expire' => 60,
        ],

        'user' => [
            'provider' => 'user',
            'table' => 'password_resets',
            'expire' => 60,
        ],
    ],
];
