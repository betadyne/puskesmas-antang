<?php

namespace Database\Seeders;

use App\Models\Poli;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PoliSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $polis = [
            ['kode_poli' => 'A', 'nama_poli' => 'Poli Umum'],
            ['kode_poli' => 'B', 'nama_poli' => 'Poli Gigi'],
            ['kode_poli' => 'C', 'nama_poli' => 'Poli KIA'],
            ['kode_poli' => 'D', 'nama_poli' => 'Poli Lansia'],
            ['kode_poli' => 'E', 'nama_poli' => 'Poli Gizi'],
        ];

        foreach ($polis as $poli) {
            Poli::create([
                'kode_poli' => $poli['kode_poli'],
                'nama_poli' => $poli['nama_poli'],
                'slug' => Str::slug($poli['nama_poli']),
            ]);
        }
    }
}
