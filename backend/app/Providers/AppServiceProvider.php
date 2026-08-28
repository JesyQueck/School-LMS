<?php

namespace App\Providers;

use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        RateLimiter::for('login', function ($request) {
            return Limit::perMinute(5)->by($request->email.$request->ip())
                ->response(function () {
                    $retryAfter = 60;

                    return response()->make(
                        __('auth.throttle', ['seconds' => $retryAfter]),
                        429,
                        ['Retry-After' => $retryAfter]
                    );
                });
        });

        Authenticate::redirectUsing(function ($request) {
            return $request->expectsJson() ? null : (Route::has('login') ? route('login') : '/login');
        });

        // Blade components are auto-discovered in Laravel 13+
        // No explicit registration needed for view components
    }
}
