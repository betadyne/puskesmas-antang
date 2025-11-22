<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RegistrationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'patient' => $this->when(
                $this->relationLoaded('patient'),
                function () {
                    return [
                        'id' => $this->patient->id,
                        'nik' => $this->patient->nik,
                        'nama' => $this->patient->nama,
                        'no_bpjs' => $this->patient->no_bpjs,
                        'tgl_lahir' => $this->patient->tgl_lahir->format('d-m-Y'),
                        'jenis_kelamin' => $this->patient->jenis_kelamin,
                        'jenis_kelamin_text' => $this->patient->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan',
                        'no_hp' => $this->patient->no_hp,
                        'email' => $this->patient->email,
                        'alamat' => $this->patient->alamat,
                    ];
                }
            ),
            'poli' => $this->when(
                $this->relationLoaded('poli'),
                function () {
                    return [
                        'id' => $this->poli->id,
                        'kode_poli' => $this->poli->kode_poli,
                        'nama_poli' => $this->poli->nama_poli,
                    ];
                }
            ),
            'queue' => $this->when(
                $this->relationLoaded('queue'),
                function () {
                    return $this->queue ? new QueueResource($this->queue) : null;
                }
            ),
            'tanggal_daftar' => $this->tanggal_daftar,
            'tanggal_daftar_formatted' => \Carbon\Carbon::parse($this->tanggal_daftar)->format('d F Y'),
            'cara_daftar' => $this->cara_daftar,
            'cara_daftar_text' => ucfirst($this->cara_daftar),
            'status' => $this->status,
            'status_text' => $this->getStatusText($this->status),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    private function getStatusText($status)
    {
        $statusTexts = [
            'terdaftar' => 'Terdaftar',
            'dibatalkan' => 'Dibatalkan',
            'selesai' => 'Selesai',
        ];

        return $statusTexts[$status] ?? $status;
    }
}
