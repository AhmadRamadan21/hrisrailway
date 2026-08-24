<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JabatanSeeder extends Seeder
{
    public function run(): void
    {
        $jabatans = [
            ['nama_jabatan' => 'KOMISARIS', 'divisi' => 'BOC'],
            ['nama_jabatan' => 'KOMISARIS UTAMA', 'divisi' => 'BOC'],
            ['nama_jabatan' => 'DIREKTUR OPERASIONAL', 'divisi' => 'BOD'],
            ['nama_jabatan' => 'DIREKTUR UTAMA', 'divisi' => 'BOD'],
            ['nama_jabatan' => 'MANAGER', 'divisi' => 'KEUANGAN & AKUNTANSI'],
            ['nama_jabatan' => 'STAF AKUNTANSI DAN PELAPORAN', 'divisi' => 'KEUANGAN & AKUNTANSI'],
            ['nama_jabatan' => 'MANAGER', 'divisi' => 'OPERASIONAL'],
            ['nama_jabatan' => 'MANAGER', 'divisi' => 'OPERASIONAL'],
            ['nama_jabatan' => 'STAF', 'divisi' => 'OPERASIONAL'],
            ['nama_jabatan' => 'MANAGER', 'divisi' => 'PEMASARAN & UMUM'],
            ['nama_jabatan' => 'STAF', 'divisi' => 'PEMASARAN & UMUM'],
            ['nama_jabatan' => 'MANAGER', 'divisi' => 'SDM'],
            ['nama_jabatan' => 'SEKRETARIS DIREKSI', 'divisi' => 'SDM'],
            ['nama_jabatan' => 'OFFICE BOY', 'divisi' => 'TLH'],
            ['nama_jabatan' => 'OFFICE BOY', 'divisi' => 'TLH'],
            ['nama_jabatan' => 'SUPIR', 'divisi' => 'TLH'],
        ];

        foreach ($jabatans as $jabatan) {
            $divisiId = DB::table('divisis')->where('nama_divisi', $jabatan['divisi'])->value('id');
            if (!$divisiId) {
                continue;
            }

            $exists = DB::table('jabatans')
                ->where('nama_jabatan', $jabatan['nama_jabatan'])
                ->where('divisi_id', $divisiId)
                ->exists();

            if ($exists) {
                continue;
            }

            DB::table('jabatans')->insert([
                'nama_jabatan' => $jabatan['nama_jabatan'],
                'divisi_id' => $divisiId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
