<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
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

        // Blade components are auto-discovered in Laravel 13+
        // No explicit registration needed for view components
    }
}
