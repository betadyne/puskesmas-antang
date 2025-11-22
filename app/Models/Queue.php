<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Queue extends Model
{
    use HasFactory;

    protected $fillable = [
        'registration_id',
        'nomor_antrean',
        'poli_id',
        'status',
        'called_at',
        'served_at',
        'finished_at',
        'petugas_id',
    ];

    protected $casts = [
        'called_at' => 'datetime',
        'served_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }

    public function poli(): BelongsTo
    {
        return $this->belongsTo(Poli::class);
    }

    public function petugas(): BelongsTo
    {
        return $this->belongsTo(User::class, 'petugas_id');
    }

    public function histories(): HasMany
    {
        return $this->hasMany(QueueHistory::class);
    }

    public function getWaitTimeAttribute()
    {
        if ($this->served_at && $this->created_at) {
            return $this->served_at->diffInMinutes($this->created_at);
        }
        return null;
    }

    public function getServiceTimeAttribute()
    {
        if ($this->finished_at && $this->served_at) {
            return $this->finished_at->diffInMinutes($this->served_at);
        }
        return null;
    }

    public function scopeByPoli($query, $poliId)
    {
        return $query->where('poli', $poliId);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeToday($query)
    {
        return $query->whereHas('registration', function ($query) {
            $query->where('tanggal_daftar', today()->format('Y-m-d'));
        });
    }

    public function scopeWaiting($query)
    {
        return $query->where('status', 'menunggu');
    }

    public function scopeCalled($query)
    {
        return $query->where('status', 'dipanggil');
    }

    public function scopeBeingServed($query)
    {
        return $query->where('status', 'sedang dilayani');
    }
}
