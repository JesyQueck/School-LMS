<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as BaseVerifier;

class VerifyCsrfToken extends BaseVerifier
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        //
    ];

    public function handle($request, \Closure $next)
    {
        // In testing environment, skip CSRF for bursar routes
        if ((app()->environment('testing') || env('APP_ENV') === 'testing' || env('PHPUNIT_TESTING') === '1')
            && str_starts_with($request->path(), 'bursar/')) {
            return $next($request);
        }

        return parent::handle($request, $next);
    }
}