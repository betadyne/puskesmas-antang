<?php

namespace App\Http\Controllers;

use App\Events\QueueCalled;
use App\Events\QueueFinished;
use App\Events\QueueRecalled;
use App\Events\QueueSkipped;
use App\Events\QueueUpdated;
use App\Http\Resources\QueueResource;
use App\Http\Requests\StoreQueueRequest;
use App\Models\Queue;
use App\Models\QueueHistory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QueueController extends Controller
{
    /**
     * Display dashboard with queue data
     */
    public function dashboard(Request $request)
    {
        $user = $request->user();
        $poliId = $user->hasRole('admin') ? null : $user->poli_id;
        
        // Build base query
        $baseQuery = Queue::with(['registration.patient', 'poli'])->today();
        
        if ($poliId) {
            $baseQuery = $baseQuery->where('poli_id', $poliId);
        }

        // Get current queue being served (dipanggil or sedang dilayani)
        $currentQueue = (clone $baseQuery)
            ->whereIn('status', ['dipanggil', 'sedang dilayani'])
            ->orderBy('called_at', 'desc')
            ->first();

        // Get waiting queues
        $waitingQueues = (clone $baseQuery)
            ->where('status', 'menunggu')
            ->orderBy('created_at')
            ->get();

        // Get recent history (completed or skipped)
        $historyQueues = (clone $baseQuery)
            ->whereIn('status', ['selesai', 'dilewati'])
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get();

        // Get stats
        $stats = [
            'total_waiting' => (clone $baseQuery)->where('status', 'menunggu')->count(),
            'total_served' => (clone $baseQuery)->where('status', 'selesai')->count(),
            'total_skipped' => (clone $baseQuery)->where('status', 'dilewati')->count(),
        ];

        return response()->json([
            'message' => 'Queue data retrieved successfully',
            'data' => [
                'current_queue' => $currentQueue ? new QueueResource($currentQueue) : null,
                'waiting_queues' => QueueResource::collection($waitingQueues),
                'history_queues' => QueueResource::collection($historyQueues),
                'statistics' => $stats,
            ]
        ]);
    }

    /**
     * Get queue statistics
     */
    public function stats(Request $request)
    {
        $user = $request->user();
        $today = Carbon::today();
        
        if ($user->hasRole('admin')) {
            $stats = [
                'total_waiting' => Queue::today()->where('status', 'menunggu')->count(),
                'total_being_served' => Queue::today()->where('status', 'sedang dilayani')->count(),
                'total_finished' => Queue::today()->where('status', 'selesai')->count(),
                'total_skipped' => Queue::today()->where('status', 'dilewati')->count(),
                'avg_wait_time' => Queue::today()->whereNotNull('wait_time')->avg('wait_time') ?? 0,
            ];
        } else {
            // Petugas only sees their poli stats
            $poliQueues = Queue::today()->where('poli_id', $user->poli_id);
            $stats = [
                'total_waiting' => $poliQueues->where('status', 'menunggu')->count(),
                'total_being_served' => $poliQueues->where('status', 'sedang dilayani')->count(),
                'total_finished' => $poliQueues->where('status', 'selesai')->count(),
                'total_skipped' => $poliQueues->where('status', 'dilewati')->count(),
                'avg_wait_time' => $poliQueues->whereNotNull('wait_time')->avg('wait_time') ?? 0,
            ];
        }

        return response()->json([
            'message' => 'Statistics retrieved successfully',
            'data' => $stats
        ]);
    }

    /**
     * Call next queue number
     */
    public function callNext(Request $request)
    {
        $user = $request->user();
        
        // Get next waiting queue for petugas's poli (or all for admin)
        $nextQueue = Queue::with(['registration.patient', 'poli'])
            ->today()
            ->where('status', 'menunggu');
            
        if (!$user->hasRole('admin')) {
            $nextQueue = $nextQueue->where('poli_id', $user->poli_id);
        }
        
        $nextQueue = $nextQueue->orderBy('created_at')->first();

        if (!$nextQueue) {
            return response()->json([
                'message' => 'No queues waiting',
                'data' => null
            ], 404);
        }

        return $this->callQueue($nextQueue, $user);
    }

    /**
     * Call specific queue
     */
    public function call(Request $request, $id)
    {
        $user = $request->user();
        $queue = Queue::findOrFail($id);
        return $this->callQueue($queue, $user);
    }

    /**
     * Handle queue calling logic
     */
    private function callQueue(Queue $queue, $user)
    {
        // Update queue status and timestamp
        $queue->update([
            'status' => 'dipanggil',
            'called_at' => now(),
            'petugas_id' => $user->id,
        ]);

        // Create history record
        QueueHistory::create([
            'queue_id' => $queue->id,
            'action' => 'called',
            'user_id' => $user->id,
        ]);

        // Broadcast with simplified data (temporarily disabled due to queue size issue)
        // broadcast(new QueueCalled($queue));

        return response()->json([
            'message' => 'Queue called successfully',
            'data' => new QueueResource($queue)
        ]);
    }

    /**
     * Skip queue
     */
    public function skip(Request $request, $id)
    {
        $queue = Queue::findOrFail($id);
        
        $queue->update([
            'status' => 'dilewati',
        ]);

        QueueHistory::create([
            'queue_id' => $queue->id,
            'action' => 'skipped',
            'user_id' => $request->user()->id,
        ]);

        // broadcast(new QueueSkipped($queue)); // Temporarily disabled

        return response()->json([
            'message' => 'Queue skipped successfully',
            'data' => new QueueResource($queue)
        ]);
    }

    /**
     * Start serving queue
     */
    public function serve(Request $request, $id)
    {
        $queue = Queue::findOrFail($id);
        
        $queue->update([
            'status' => 'sedang dilayani',
            'served_at' => now(),
            'petugas_id' => $request->user()->id,
        ]);

        QueueHistory::create([
            'queue_id' => $queue->id,
            'action' => 'served',
            'user_id' => $request->user()->id,
        ]);

        // broadcast(new QueueUpdated($queue)); // Temporarily disabled

        return response()->json([
            'message' => 'Queue serving started',
            'data' => new QueueResource($queue)
        ]);
    }

    /**
     * Finish queue
     */
    public function finish(Request $request, $id)
    {
        $queue = Queue::findOrFail($id);
        
        $queue->update([
            'status' => 'selesai',
            'finished_at' => now(),
        ]);

        QueueHistory::create([
            'queue_id' => $queue->id,
            'action' => 'finished',
            'user_id' => $request->user()->id,
        ]);

        // broadcast(new QueueFinished($queue)); // Temporarily disabled

        return response()->json([
            'message' => 'Queue finished successfully',
            'data' => new QueueResource($queue)
        ]);
    }

    /**
     * Recall queue
     */
    public function recall(Request $request, $id)
    {
        $queue = Queue::findOrFail($id);
        
        $queue->update([
            'status' => 'dipanggil',
            'called_at' => now(),
        ]);

        QueueHistory::create([
            'queue_id' => $queue->id,
            'action' => 'called',
            'notes' => 'Recalled',
            'user_id' => $request->user()->id,
        ]);

        // broadcast(new QueueRecalled($queue)); // Temporarily disabled

        return response()->json([
            'message' => 'Queue recalled successfully',
            'data' => new QueueResource($queue)
        ]);
    }
}
