<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PoliAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Allow admin to access all poli
        if ($user && $user->hasRole('admin')) {
            return $next($request);
        }

        // For petugas, check if they have access to the requested poli
        if ($user && $user->hasRole('petugas')) {
            // Get poli ID from route parameters or request data
            $poliId = $this->getPoliIdFromRequest($request);

            // If no poli ID in request, allow access to view their own poli only
            if (!$poliId) {
                // Allow access to dashboard, etc. - the controller will-filter by user's poli
                return $next($request);
            }

            // Check if user has access to the requested poli
            if ($user->poli_id && $user->poli_id == $poliId) {
                return $next($request);
            }

            return response()->json([
                'message' => 'Anda tidak memiliki akses ke poli ini',
                'errors' => [
                    'poli' => ['Unauthorized access to poli']
                ]
            ], 403);
        }

        return $next($request);
    }

    private function getPoliIdFromRequest(Request $request): ?int
    {
        // Try to get from route parameter
        $routePoliId = $request->route('poli_id');
        if ($routePoliId) {
            return (int) $routePoliId;
        }

        // Try to get from queue ID parameter
        $queueId = $request->route('queue') ?? $request->route('id');
        if ($queueId) {
            $queue = \App\Models\Queue::find($queueId);
            if ($queue) {
                return $queue->poli_id;
            }
        }

        // Try to get from request body (for POST/PUT requests)
        return $request->input('poli_id') ?? $request->input('poli');
    }
}
