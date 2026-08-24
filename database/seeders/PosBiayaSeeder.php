<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PosBiayaSeeder extends Seeder
{
    public function run(): void
    {
        $posBiayas = [
            ['nama_biaya' => 'Biaya Operasional Kantor', 'anggaran' => 150000000],
            ['nama_biaya' => 'Biaya Perjalanan Dinas',   'anggaran' => 50000000],
            ['nama_biaya' => 'Biaya Konsumsi',           'anggaran' => 25000000],
            ['nama_biaya' => 'Biaya Listrik & Air',      'anggaran' => 60000000],
            ['nama_biaya' => 'Biaya Internet & Telp',    'anggaran' => 30000000],
        ];

        foreach ($posBiayas as $pos) {
            DB::table('pos_biayas')->insertOrIgnore([
                'nama_biaya' => $pos['nama_biaya'],
                'anggaran'   => $pos['anggaran'],
                'realisasi'  => 0,
                'sisa'       => $pos['anggaran'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}