<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QueueResource extends JsonResource
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
            'nomor_antrean' => $this->nomor_antrean,
            'status' => $this->status,
            'status_text' => $this->getStatusText($this->status),
            'poli' => [
                'id' => $this->poli->id,
                'kode_poli' => $this->poli->kode_poli,
                'nama_poli' => $this->poli->nama_poli,
            ],
            'patient' => $this->when(
                $this->relationLoaded('registration') && $this->registration->relationLoaded('patient'),
                function () {
                    return [
                        'id' => $this->registration->patient->id,
                        'nik' => $this->registration->patient->nik,
                        'nama' => $this->registration->patient->nama,
                        'no_hp' => $this->registration->patient->no_hp,
                    ];
                }
            ),
            'petugas' => $this->when(
                $this->relationLoaded('petugas'),
                function () {
                    return $this->petugas ? [
                        'id' => $this->petugas->id,
                        'name' => $this->petugas->name,
                    ] : null;
                }
            ),
            'wait_time' => $this->wait_time,
            'service_time' => $this->service_time,
            'timestamps' => [
                'created_at' => $this->created_at->format('H:i'),
                'called_at' => $this->called_at?->format('H:i'),
                'served_at' => $this->served_at?->format('H:i'),
                'finished_at' => $this->finished_at?->format('H:i'),
            ],
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    private function getStatusText($status)
    {
        $statusTexts = [
            'menunggu' => 'Menunggu',
            'dipanggil' => 'Sedang Dipanggil',
            'sedang dilayani' => 'Sedang Dilayani',
            'selesai' => 'Selesai',
            'dilewati' => 'Dilewati',
        ];

        return $statusTexts[$status] ?? $status;
    }
}
