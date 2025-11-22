<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Registration extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'tanggal_daftar',
        'poli_tujuan',
        'cara_daftar',
        'status',
    ];

    protected $casts = [
        'tanggal_daftar' => 'date',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function poli(): BelongsTo
    {
        return $this->belongsTo(Poli::class, 'poli_tujuan');
    }

    public function queue(): HasOne
    {
        return $this->hasOne(Queue::class);
    }

    public function getFormattedDateAttribute()
    {
        return $this->tanggal_daftar->format('d-m-Y');
    }

    public function scopeToday($query)
    {
        return $query->where('tanggal_daftar', today()->format('Y-m-d'));
    }

    public function scopeByPoli($query, $poliId)
    {
        return $query->where('poli_tujuan', $poliId);
    }
}
