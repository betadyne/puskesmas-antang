<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

// Queue channels for specific poli (public)
Broadcast::channel('queue.{poliId}', function ($user, $poliId) {
    // Allow authenticated users or make this channel public for TV display
    return true;
});

// Display channels for TV displays (public)
Broadcast::channel('display.{poliId}', function ($user, $poliId) {
    // Public channel for TV displays - no authentication required
    return true;
});

// Admin dashboard channel (private, for admin/petugas)
Broadcast::channel('dashboard.poli.{poliId}', function ($user, $poliId) {
    // Only allow admin or petugas assigned to this poli
    if ($user->hasRole('admin')) {
        return true;
    }
    
    if ($user->hasRole('petugas') && $user->poli_id == $poliId) {
        return ['id' => $user->id, 'name' => $user->name, 'poli_id' => $user->poli_id];
    }
    
    return false;
});

// Private channel for individual queue updates
Broadcast::channel('queue.{queueId}.updates', function ($user, $queueId) {
    $queue = \App\Models\Queue::find($queueId);
    
    if (!$queue) {
        return false;
    }
    
    // Allow admin to subscribe to any queue
    if ($user->hasRole('admin')) {
        return ['id' => $user->id, 'name' => $user->name, 'role' => 'admin'];
    }
    
    // Allow petugas to subscribe to queues from their poli
    if ($user->hasRole('petugas') && $user->poli_id == $queue->poli_id) {
        return ['id' => $user->id, 'name' => $user->name, 'role' => 'petugas', 'poli_id' => $user->poli_id];
    }
    
    // Allow patient to subscribe to their own queue
    if ($user->hasRole('pasien')) {
        $patient = \App\Models\Patient::where('email', $user->email)->first();
        if ($patient && $queue->registration && $queue->registration->patient_id == $patient->id) {
            return ['id' => $user->id, 'name' => $user->name, 'role' => 'pasien'];
        }
    }
    
    return false;
});

// Presence channel for staff status tracking
Broadcast::channel('presence-staff', function ($user) {
    // Only allow admin and petugas to join this presence channel
    if ($user->hasRole('admin') || $user->hasRole('petugas')) {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'role' => $user->getRoleNames()->first(),
            'poli_id' => $user->poli_id,
            'poli' => $user->poli,
        ];
    }
    
    return false;
});
