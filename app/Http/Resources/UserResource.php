<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'name' => $this->name,
            'email' => $this->email,
            'poli' => $this->when($this->poli, function () {
                return [
                    'id' => $this->poli->id,
                    'kode_poli' => $this->poli->kode_poli,
                    'nama_poli' => $this->poli->nama_poli,
                    'slug' => $this->poli->slug,
                ];
            }),
            'roles' => $this->getRoleNames(),
            'email_verified_at' => $this->email_verified_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
