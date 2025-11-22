<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check for Bearer token
        $token = $request->bearerToken();
        
        if (!$token) {
            return response()->json([
                'message' => 'Unauthenticated',
                'errors' => [
                    'auth' => ['Access token required']
                ]
            ], 401);
        }

        // Personal Access Token authentication
        $model = \Laravel\Sanctum\PersonalAccessToken::findToken($token);
        
        if (!$model) {
            return response()->json([
                'message' => 'Unauthenticated',
                'errors' => [
                    'auth' => ['Invalid access token']
                ]
            ], 401);
        }

        // Set authenticated user
        $user = $model->tokenable;
        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        return $next($request);
    }
}
