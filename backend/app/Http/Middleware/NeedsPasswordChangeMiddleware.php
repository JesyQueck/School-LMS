<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class NeedsPasswordChangeMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()) {
            return redirect('/login');
        }

        if ($request->user()->needs_password_change && $request->user()->role !== 'admin' && ! $request->routeIs('password.change', 'password.change.update')) {
            return redirect()->route('password.change');
        }

        return $next($request);
    }
}
