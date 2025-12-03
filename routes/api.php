<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\QueueController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\DisplayController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PoliController;
use App\Http\Controllers\PatientController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [RegistrationController::class, 'store']);

// Public poli list (for registration dropdown)
Route::get('/poli', function () {
    $polis = \App\Models\Poli::select('id', 'kode_poli', 'nama_poli', 'slug')->get();
    return response()->json([
        'message' => 'Poli list retrieved successfully',
        'data' => $polis
    ]);
});

// Public poli queue stats (for landing page)
Route::get('/poli/queue-stats', function () {
    $polis = \App\Models\Poli::all()->map(function ($poli) {
        $waitingCount = \App\Models\Queue::where('poli_id', $poli->id)
            ->today()
            ->where('status', 'menunggu')
            ->count();
        
        $currentQueue = \App\Models\Queue::where('poli_id', $poli->id)
            ->today()
            ->where('status', 'dipanggil')
            ->first();
            
        return [
            'id' => $poli->id,
            'kode_poli' => $poli->kode_poli,
            'nama_poli' => $poli->nama_poli,
            'waiting_count' => $waitingCount,
            'current_queue' => $currentQueue ? $currentQueue->nomor_antrean : null,
        ];
    });
    
    return response()->json([
        'message' => 'Poli queue stats retrieved successfully',
        'data' => $polis
    ]);
});

// Public display routes
Route::get('/display/{poli}', [DisplayController::class, 'show']);
Route::get('/queue/status/{nomor}', [DisplayController::class, 'checkQueueStatus']);

// Protected routes
Route::middleware(['auth:sanctum'])->group(function () {
    // Get authenticated user
    Route::get('/user', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Dashboard routes (petugas and admin)
    Route::middleware(['permission:view-dashboard'])->prefix('/dashboard')->group(function () {
        Route::get('/queues', [QueueController::class, 'dashboard'])
            ->middleware('permission:view-own-poli-queue');
        Route::get('/stats', [QueueController::class, 'stats'])
            ->middleware('permission:view-own-poli-queue');
    });

    // Queue management routes (petugas and admin)
    Route::middleware(['permission:call-queue'])->prefix('/queue')->group(function () {
        Route::post('/call-next', [QueueController::class, 'callNext'])
            ->middleware('PoliAccess');
        Route::post('/{id}/call', [QueueController::class, 'call'])
            ->middleware('QueueOwnership');
        Route::post('/{id}/skip', [QueueController::class, 'skip'])
            ->middleware('QueueOwnership');
        Route::post('/{id}/serve', [QueueController::class, 'serve'])
            ->middleware('QueueOwnership');
        Route::post('/{id}/finish', [QueueController::class, 'finish'])
            ->middleware('QueueOwnership');
        Route::post('/{id}/recall', [QueueController::class, 'recall'])
            ->middleware('QueueOwnership');
    });

    // Queue status check for patients
    Route::get('/my-queue', [DisplayController::class, 'myQueue'])
        ->middleware('permission:view-own-queue-status');

    // Reports routes
    Route::middleware(['permission:view-reports'])->prefix('/reports')->group(function () {
        Route::get('/daily', [ReportController::class, 'dailyReport']);
        Route::get('/statistics', [ReportController::class, 'statistics']);
    });

    // Admin only routes
    Route::middleware(['role:admin'])->prefix('/admin')->group(function () {
        // Admin dashboard stats
        Route::get('/stats', function () {
            $today = today()->format('Y-m-d');
            
            // Total patients
            $totalPatients = \App\Models\Patient::count();
            
            // Total poli
            $totalPoli = \App\Models\Poli::count();
            
            // Total users (petugas + admin)
            $totalUsers = \App\Models\User::count();
            
            // Today's served queues
            $todayServed = \App\Models\Queue::whereHas('registration', function ($q) use ($today) {
                $q->where('tanggal_daftar', $today);
            })->where('status', 'selesai')->count();
            
            // Today's waiting queues
            $todayWaiting = \App\Models\Queue::whereHas('registration', function ($q) use ($today) {
                $q->where('tanggal_daftar', $today);
            })->where('status', 'menunggu')->count();
            
            // Today's total queues
            $todayTotal = \App\Models\Queue::whereHas('registration', function ($q) use ($today) {
                $q->where('tanggal_daftar', $today);
            })->count();
            
            // Recent activities (today's queues)
            $recentQueues = \App\Models\Queue::with(['registration.patient', 'poli'])
                ->whereHas('registration', function ($q) use ($today) {
                    $q->where('tanggal_daftar', $today);
                })
                ->orderBy('updated_at', 'desc')
                ->limit(10)
                ->get()
                ->map(function ($queue) {
                    $type = 'queue';
                    $message = '';
                    
                    switch ($queue->status) {
                        case 'menunggu':
                            $message = "Pasien {$queue->registration->patient->nama} mendaftar ke {$queue->poli->nama_poli}";
                            break;
                        case 'dipanggil':
                            $message = "Antrian {$queue->nomor_antrean} dipanggil di {$queue->poli->nama_poli}";
                            break;
                        case 'selesai':
                            $message = "Antrian {$queue->nomor_antrean} selesai dilayani";
                            break;
                        case 'dilewati':
                            $message = "Antrian {$queue->nomor_antrean} dilewati";
                            break;
                        default:
                            $message = "Antrian {$queue->nomor_antrean} - {$queue->status}";
                    }
                    
                    return [
                        'id' => $queue->id,
                        'type' => $type,
                        'message' => $message,
                        'time' => $queue->updated_at->format('H:i'),
                    ];
                });
            
            return response()->json([
                'message' => 'Admin stats retrieved successfully',
                'data' => [
                    'total_patients' => $totalPatients,
                    'total_poli' => $totalPoli,
                    'total_users' => $totalUsers,
                    'today_served' => $todayServed,
                    'today_waiting' => $todayWaiting,
                    'today_total' => $todayTotal,
                    'recent_activities' => $recentQueues,
                ]
            ]);
        });
        
        // User management
        Route::apiResource('/users', UserController::class);
        
        // Poli management  
        Route::apiResource('/polis', PoliController::class);
        
        // Patient management
        Route::apiResource('/patients', PatientController::class);
    });
});
