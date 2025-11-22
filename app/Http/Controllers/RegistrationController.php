<?php

namespace App\Http\Controllers;

use App\Events\NewRegistration;
use App\Http\Resources\RegistrationResource;
use App\Http\Resources\QueueResource;
use App\Models\Patient;
use App\Models\Poli;
use App\Models\Queue;
use App\Models\Registration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class RegistrationController extends Controller
{
    /**
     * Store a new registration (online registration)
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nik' => 'required|string|max:16|unique:patients,nik',
            'nama' => 'required|string|max:255',
            'no_bpjs' => 'nullable|string|max:50',
            'tgl_lahir' => 'required|date|before:today',
            'jenis_kelamin' => 'required|in:L,P',
            'no_hp' => 'required|string|max:15',
            'email' => 'nullable|email|max:255',
            'alamat' => 'required|string|max:500',
            'poli_tujuan' => 'required|exists:polis,id',
            'cara_daftar' => 'required|in:online,offline',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Create or find patient
            $patient = Patient::create([
                'nik' => $request->nik,
                'nama' => $request->nama,
                'no_bpjs' => $request->no_bpjs,
                'tgl_lahir' => $request->tgl_lahir,
                'jenis_kelamin' => $request->jenis_kelamin,
                'no_hp' => $request->no_hp,
                'email' => $request->email,
                'alamat' => $request->alamat,
            ]);

            // Get poli information
            $poli = Poli::findOrFail($request->poli_tujuan);

            // Create registration
            $registration = Registration::create([
                'patient_id' => $patient->id,
                'tanggal_daftar' => now()->format('Y-m-d'),
                'poli_tujuan' => $request->poli_tujuan,
                'cara_daftar' => $request->cara_daftar,
                'status' => 'terdaftar',
            ]);

            // Generate queue number
            $queueNumber = $this->generateQueueNumber($request->poli_tujuan);

            // Create queue
            $queue = Queue::create([
                'registration_id' => $registration->id,
                'nomor_antrean' => $queueNumber,
                'poli_id' => $request->poli_tujuan,
                'status' => 'menunggu',
            ]);

            // Create queue history
            $queue->histories()->create([
                'action' => 'created',
                'notes' => 'Registration created via ' . $request->cara_daftar,
            ]);

            DB::commit();

            // broadcast(new NewRegistration($queue)); // Temporarily disabled due to queue size issue

            return response()->json([
                'message' => 'Registration successful',
                'data' => [
                    'registration' => new RegistrationResource($registration),
                    'queue' => new QueueResource($queue),
                    'patient' => $patient,
                    'poli' => $poli,
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollback();

            return response()->json([
                'message' => 'Registration failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate queue number with business logic
     * Format: [Kode Poli][Nomor 3 digit] -> A001, B025
     */
    private function generateQueueNumber($poliId)
    {
        // Get poli information for code
        $poli = Poli::findOrFail($poliId);
        $poliCode = $poli->kode_poli;

        // Use pessimistic locking to prevent race conditions
        $lastQueue = Queue::lockForUpdate()
            ->whereHas('registration', function ($query) {
                $query->where('tanggal_daftar', today()->format('Y-m-d'));
            })
            ->where('poli_id', $poliId)
            ->orderByRaw('CAST(SUBSTRING(nomor_antrean, 2) AS UNSIGNED) DESC')
            ->first();

        if ($lastQueue) {
            // Extract numeric part and increment
            $numericPart = (int) substr($lastQueue->nomor_antrean, 1);
            $newNumber = $numericPart + 1;
        } else {
            // First queue for this poli today
            $newNumber = 1;
        }

        // Format with leading zeros (3 digits)
        $formattedNumber = str_pad($newNumber, 3, '0', STR_PAD_LEFT);

        return $poliCode . $formattedNumber;
    }

    /**
     * Display registration details
     */
    public function show(Request $request, Registration $registration)
    {
        $registration->load(['patient', 'poli', 'queue.histories']);

        return response()->json([
            'message' => 'Registration retrieved successfully',
            'data' => new RegistrationResource($registration)
        ]);
    }

    /**
     * Update registration status (admin only)
     */
    public function updateStatus(Request $request, Registration $registration)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:terdaftar,dibatalkan,selesai',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $oldStatus = $registration->status;
        $registration->update(['status' => $request->status]);

        return response()->json([
            'message' => 'Registration status updated',
            'data' => [
                'old_status' => $oldStatus,
                'new_status' => $registration->status,
                'registration' => new RegistrationResource($registration)
            ]
        ]);
    }

    /**
     * Get today's registrations
     */
    public function today(Request $request)
    {
        $user = $request->user();

        $query = Registration::with(['patient', 'poli', 'queue'])
            ->where('tanggal_daftar', today()->format('Y-m-d'));

        // If petugas, only show their poli
        if (!$user->hasRole('admin') && $user->poli_id) {
            $query->where('poli_tujuan', $user->poli_id);
        }

        $registrations = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'message' => 'Today\'s registrations retrieved successfully',
            'data' => RegistrationResource::collection($registrations)
        ]);
    }
}
