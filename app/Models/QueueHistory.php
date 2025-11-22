<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QueueHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'queue_id',
        'action',
        'notes',
        'user_id',
    ];

    public function queue(): BelongsTo
    {
        return $this->belongsTo(Queue::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getActionLabelAttribute()
    {
        return match($this->action) {
            'created' => 'Dibuat',
            'called' => 'Dipanggil',
            'served' => 'Dilayani',
            'finished' => 'Selesai',
            'skipped' => 'Dilewati',
            default => ucfirst($this->action),
        };
    }
}
