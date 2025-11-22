<?php

namespace App\Events;

use App\Models\Registration;
use App\Models\Queue;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewRegistration implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $registration;
    public $queue;

    /**
     * Create a new event instance.
     */
    public function __construct(Queue $queue)
    {
        $this->queue = $queue;
        $this->registration = $queue->registration;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('queue.' . $this->queue->poli_id),
            new Channel('display.' . $this->queue->poli_id),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'registration.created';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'queue_id' => $this->queue->id,
            'nomor_antrean' => $this->queue->nomor_antrean,
            'poli_id' => $this->queue->poli_id,
            'poli_nama' => $this->queue->poli->nama_poli,
            'status' => $this->queue->status,
            'patient_nama' => $this->registration->patient->nama,
            'created_at' => $this->queue->created_at->toISOString(),
            'message' => 'Pasien baru mendaftar untuk ' . $this->queue->nomor_antrean,
        ];
    }
}
