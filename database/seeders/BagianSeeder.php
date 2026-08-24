<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BagianSeeder extends Seeder
{
    public function run(): void
    {
        $bagians = [
            ['nama_bagian' => 'IT',          'kode_bagian' => 'IT'],
            ['nama_bagian' => 'SEKRETARIAT', 'kode_bagian' => 'SEK'],
            ['nama_bagian' => 'OPERASIONAL', 'kode_bagian' => 'OPS'],
            ['nama_bagian' => 'SDM',         'kode_bagian' => 'SDM'],
            ['nama_bagian' => 'PEMASARAN',   'kode_bagian' => 'PMS'],
            ['nama_bagian' => 'LOGISTIK',    'kode_bagian' => 'LOG'],
            ['nama_bagian' => 'PRODUKSI',    'kode_bagian' => 'PRD'],
            ['nama_bagian' => 'KEUANGAN',    'kode_bagian' => 'KEU'],
            ['nama_bagian' => 'AKUNTANSI',   'kode_bagian' => 'AKT'],
        ];

        foreach ($bagians as $bagian) {
            DB::table('bagians')->insertOrIgnore([
                'nama_bagian' => $bagian['nama_bagian'],
                'kode_bagian' => $bagian['kode_bagian'],
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }
    }
}