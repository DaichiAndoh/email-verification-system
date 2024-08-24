<?php

return [
    'global'=>[
        \Middleware\SessionsSetupMiddleware::class,
        \Middleware\CSRFMiddleware::class,
    ],
    'aliases'=>[
        'login'=>\Middleware\LoginMiddleware::class,
        'notVerified'=>\Middleware\NotVerifiedMiddleware::class,
        'auth'=>\Middleware\AuthenticatedMiddleware::class,
        'guest'=>\Middleware\GuestMiddleware::class,
        'signature'=>\Middleware\SignatureValidationMiddleware::class,
    ]
];
