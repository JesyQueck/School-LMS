<?php

namespace App\Http;

use App\Http\Middleware\PasswordOnlyMiddleware;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Kernel as KernelBase;
use Illuminate\Foundation\Http\Middleware\ValidatePostSize;
use Illuminate\Http\Middleware\HandleCors;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class Kernel extends KernelBase
{
    protected $middleware = [
        HandleCors::class,
        AddQueuedCookiesToResponse::class,
        StartSession::class,
        ShareErrorsFromSession::class,
    ];

    protected $middlewareGroups = [
        'web' => [
            EncryptCookies::class,
            AddQueuedCookiesToResponse::class,
            StartSession::class,
            ShareErrorsFromSession::class,
            ValidatePostSize::class,
            SubstituteBindings::class,
        ],

        'api' => [
            ThrottleRequests::class,
            SubstituteBindings::class,
        ],
    ];

    protected $routeMiddleware = [
        'role' => RoleMiddleware::class,
        'password.only' => PasswordOnlyMiddleware::class,
    ];
}
