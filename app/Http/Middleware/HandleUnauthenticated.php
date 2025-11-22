<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Auth\Middleware\Authenticate as Middleware;

class HandleUnauthenticated extends Middleware
{
    /**
     * Handle an unauthenticated request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string[]  ...$guards
     * @return mixed
     *
     * @throws \Illuminate\Auth\AuthenticationException
     */
    protected function unauthenticated($request, array $guards)
    {
        // For API requests, return JSON response instead of redirect
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Unauthenticated',
                'errors' => [
                    'auth' => ['Authentication required']
                ]
            ], 401);
        }

        // For web requests, use default behavior
        abort(403, 'Unauthorized');
    }
}
