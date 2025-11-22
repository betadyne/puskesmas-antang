<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
 use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Poli extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode_poli',
        'nama_poli',
        'slug',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function registrations()
    {
        return $this->hasMany(Registration::class, 'poli_tujuan');
    }

    public function queues()
    {
        return $this->hasMany(Queue::class);
    }

    public function todayQueues()
    {
        return $this->queues()
            ->whereHas('registration', function ($query) {
                $query->where('tanggal_daftar', today()->format('Y-m-d'));
            });
    }

    public function generateQueueNumber()
    {
        $today = today();
        $lastQueue = $this->queues()
            ->whereYear('created_at', $today->year)
            ->whereMonth('created_at', $today->month)
            ->whereDay('created_at', $today->day)
            ->orderBy('nomor_antrean', 'desc')
            ->first();

        $lastNumber = $lastQueue ? (int)str_replace($this->kode_poli, '', $lastQueue->nomor_antrean) : 0;
        $newNumber = $lastNumber + 1;

        return $this->kode_poli . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
    }
}
