<?php

return [
    'global'=>[
        \Middleware\SessionsSetupMiddleware::class,
    ],
    'aliases'=>[
        'auth'=>\Middleware\AuthenticatedMiddleware::class,
        'guest'=>\Middleware\GuestMiddleware::class,
    ]
];
