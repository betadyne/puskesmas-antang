<?php

namespace App\Events;

use App\Models\Queue;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class QueueRecalled implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $queue;

    /**
     * Create a new event instance.
     */
    public function __construct(Queue $queue)
    {
        $this->queue = $queue;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('queue.' . $this->queue->poli),
            new Channel('display.' . $this->queue->poli),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'queue.recalled';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'queue' => [
                'id' => $this->queue->id,
                'nomor_antrean' => $this->queue->nomor_antrean,
                'poli' => $this->queue->poli,
                'status' => $this->queue->status,
                'called_at' => $this->queue->called_at,
                'patient' => [
                    'nama' => $this->queue->registration->patient->nama,
                ],
            ],
            'message' => 'Nomor antrean ' . $this->queue->nomor_antrean . ' dipanggil kembali',
        ];
    }
}
