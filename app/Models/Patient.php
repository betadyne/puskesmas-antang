<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Patient extends Model
{
    use HasFactory;

    protected $fillable = [
        'nik',
        'nama',
        'no_bpjs',
        'tgl_lahir',
        'jenis_kelamin',
        'no_hp',
        'alamat',
    ];

    protected $casts = [
        'tgl_lahir' => 'date',
    ];

    public function registrations()
    {
        return $this->hasMany(Registration::class);
    }

    public function queues()
    {
        return $this->hasManyThrough(Queue::class, Registration::class);
    }

    public function getAgeAttribute()
    {
        return $this->tgl_lahir->age;
    }
}
