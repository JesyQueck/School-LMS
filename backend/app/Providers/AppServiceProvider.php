<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Blade components are auto-discovered in Laravel 13+
        // No explicit registration needed for view components
    }
}
