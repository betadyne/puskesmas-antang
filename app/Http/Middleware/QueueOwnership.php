<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class QueueOwnership
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Allow admin to access all queues
        if ($user && $user->hasRole('admin')) {
            return $next($request);
        }

        // For petugas, check if they can manipulate the queue
        if ($user && $user->hasRole('petugas')) {
            // Get queue ID from route parameters
            $queueId = $request->route('queue') ?? $request->route('id');

            // For queue list endpoints, controller will handle filtering by user's poli
            if (!$queueId) {
                return $next($request);
            }

            // Load queue with its relationships
            $queue = \App\Models\Queue::find($queueId);
            
            if (!$queue) {
                return response()->json([
                    'message' => 'Antrean tidak ditemukan',
                    'errors' => [
                        'queue' => ['Queue not found']
                    ]
                ], 404);
            }

            // Check if user has access to this queue's poli
            if ($user->poli_id && $user->poli_id == $queue->poli_id) {
                // Store the queue in the request for later use
                $request->merge(['validated_queue' => $queue]);
                return $next($request);
            }

            return response()->json([
                'message' => 'Anda tidak memiliki akses untuk mengelola antrean ini',
                'errors' => [
                    'poli' => ['Unauthorized access to queue from different poli']
                ]
            ], 403);
        }

        return $next($request);
    }
}
