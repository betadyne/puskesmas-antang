<?php

namespace App\Http\Controllers;

use App\Http\Resources\QueueResource;
use App\Models\Poli;
use App\Models\Queue;
use Illuminate\Http\Request;

class DisplayController extends Controller
{
    /**
     * Display queue data for TV display
     */
    public function show(Request $request, $poli)
    {
        // Find poli by code or slug
        $poliModel = Poli::where('kode_poli', $poli)->orWhere('slug', $poli)->first();
        
        if (!$poliModel) {
            return response()->json([
                'message' => 'Poli tidak ditemukan',
                'data' => null
            ], 404);
        }

        // Get current queue being served
        $currentQueue = Queue::with(['registration.patient'])
            ->where('poli_id', $poliModel->id)
            ->today()
            ->where('status', 'dipanggil')
            ->orderBy('called_at', 'desc')
            ->first();

        // Get next 5 waiting queues
        $waitingQueues = Queue::with(['registration.patient'])
            ->where('poli_id', $poliModel->id)
            ->today()
            ->where('status', 'menunggu')
            ->orderBy('created_at')
            ->limit(5)
            ->get();

        // Get recent completed/skipped queues for display
        $recentQueues = Queue::with(['registration.patient'])
            ->where('poli_id', $poliModel->id)
            ->today()
            ->whereIn('status', ['selesai', 'dilewati'])
            ->orderBy('updated_at', 'desc')
            ->limit(3)
            ->get();

        return response()->json([
            'message' => 'Display data retrieved successfully',
            'data' => [
                'poli' => $poliModel,
                'current_queue' => $currentQueue ? new QueueResource($currentQueue) : null,
                'waiting_queues' => QueueResource::collection($waitingQueues),
                'recent_queues' => QueueResource::collection($recentQueues),
                'statistics' => [
                    'total_waiting' => Queue::today()->where('poli_id', $poliModel->id)->where('status', 'menunggu')->count(),
                    'total_served' => Queue::today()->where('poli_id', $poliModel->id)->where('status', 'selesai')->count(),
                    'total_skipped' => Queue::today()->where('poli_id', $poliModel->id)->where('status', 'dilewati')->count(),
                ]
            ]
        ]);
    }

    /**
     * Check queue status by number
     */
    public function checkQueueStatus(Request $request, $nomor)
    {
        $queue = Queue::with(['registration.patient', 'poli'])
            ->where('nomor_antrean', $nomor)
            ->today()
            ->first();

        if (!$queue) {
            return response()->json([
                'message' => 'Nomor antrean tidak ditemukan',
                'data' => null
            ], 404);
        }

        return response()->json([
            'message' => 'Queue status retrieved successfully',
            'data' => [
                'queue' => new QueueResource($queue),
                'status_text' => $this->getStatusText($queue->status),
                'position_in_queue' => $this->getQueuePosition($queue),
                'estimated_wait_time' => $this->getEstimatedWaitTime($queue),
            ]
        ]);
    }

    /**
     * Get current user's queue (for authenticated patients)
     */
    public function myQueue(Request $request)
    {
        $user = $request->user();
        
        // Find patient associated with this user (if email matches)
        $patient = \App\Models\Patient::where('email', $user->email)->first();
        
        if (!$patient) {
            return response()->json([
                'message' => 'No patient data found for this user',
                'data' => null
            ], 404);
        }

        // Find active queue for this patient today
        $queue = Queue::with(['registration.patient', 'poli'])
            ->whereHas('registration', function ($query) use ($patient) {
                $query->where('patient_id', $patient->id)
                      ->where('tanggal_daftar', today()->format('Y-m-d'));
            })
            ->first();

        if (!$queue) {
            return response()->json([
                'message' => 'No active queue found for today',
                'data' => null
            ], 404);
        }

        return response()->json([
            'message' => 'User queue retrieved successfully',
            'data' => [
                'queue' => new QueueResource($queue),
                'status_text' => $this->getStatusText($queue->status),
                'position_in_queue' => $this->getQueuePosition($queue),
                'estimated_wait_time' => $this->getEstimatedWaitTime($queue),
            ]
        ]);
    }

    private function getStatusText($status)
    {
        $statusTexts = [
            'menunggu' => 'Menunggu',
            'dipanggil' => 'Sedang Dipanggil',
            'sedang dilayani' => 'Sedang Dilayani',
            'selesai' => 'Selesai',
            'dilewati' => 'Dilewati',
        ];

        return $statusTexts[$status] ?? $status;
    }

    private function getQueuePosition($queue)
    {
        if ($queue->status !== 'menunggu') {
            return null;
        }

        $position = Queue::today()
            ->where('poli_id', $queue->poli_id)
            ->where('status', 'menunggu')
            ->where('created_at', '<=', $queue->created_at)
            ->count();

        return $position;
    }

    private function getEstimatedWaitTime($queue)
    {
        if ($queue->status !== 'menunggu') {
            return null;
        }

        // Calculate average service time for this poli today (using raw calculation)
        $finishedQueues = Queue::today()
            ->where('poli_id', $queue->poli_id)
            ->where(function($query) {
                $query->whereNotNull('served_at')
                      ->whereNotNull('finished_at');
            })
            ->get();
            
        $avgServiceTime = 15; // Default 15 minutes
        
        if ($finishedQueues->isNotEmpty()) {
            $totalServiceTime = $finishedQueues->sum(function($q) {
                return $q->served_at && $q->finished_at 
                    ? $q->served_at->diffInMinutes($q->finished_at) 
                    : 0;
            });
            $avgServiceTime = $totalServiceTime / $finishedQueues->count();
        }

        $queuesAhead = $this->getQueuePosition($queue) - 1;
        
        return $queuesAhead * $avgServiceTime; // in minutes
    }
}
