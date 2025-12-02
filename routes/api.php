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
        // User management
        Route::apiResource('/users', UserController::class);
        
        // Poli management  
        Route::apiResource('/polis', PoliController::class);
        
        // Patient management
        Route::apiResource('/patients', PatientController::class);
    });
});
