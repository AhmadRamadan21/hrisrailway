<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DivisiSeeder extends Seeder
{
    public function run(): void
    {
        $divisis = [
            ['nama_divisi' => 'BOC'],
            ['nama_divisi' => 'BOD'],
            ['nama_divisi' => 'KEUANGAN & AKUNTANSI'],
            ['nama_divisi' => 'OPERASIONAL'],
            ['nama_divisi' => 'PEMASARAN & UMUM'],
            ['nama_divisi' => 'SDM'],
            ['nama_divisi' => 'TLH'],
        ];

        foreach ($divisis as $divisi) {
            DB::table('divisis')->insertOrIgnore([
                'nama_divisi' => $divisi['nama_divisi'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
