<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class KaryawanUserSeeder extends Seeder
{
    public function run(): void
    {
        $roleUser = DB::table('roles')->where('name', 'User')->value('id');
        $bagianDefault = DB::table('bagians')->where('kode_bagian', 'OPS')->value('id');

        $karyawanData = [
            ['nama' => 'Ani Oktaviani', 'email' => 'niezha422007@gmail.com', 'username' => 'niezha422007', 'bagian' => 'SDM'],
            ['nama' => 'Setyo Utoro', 'email' => 'setyo.utoro27@gmail.com', 'username' => 'setyo.utoro27', 'bagian' => 'PMS'],
            ['nama' => 'Abdurrohman, ST', 'email' => 'abdur@ibp.co.id', 'username' => 'abdur', 'bagian' => 'OPS'],
            ['nama' => 'Rudy Lizwaril, Drs. SE. MM. AK', 'email' => 'rudy.lizwaril@yahoo.co.id', 'username' => 'rudy.lizwaril', 'bagian' => 'OPS'],
            ['nama' => 'Gema Alfarisi Deri, ST', 'email' => 'gema.alfarisi@ibp.co.id', 'username' => 'gema.alfarisi', 'bagian' => 'PMS'],
            ['nama' => 'Dadang Mukti', 'email' => 'dadang.mukti@ibp.co.id', 'username' => 'dadang.mukti', 'bagian' => 'OPS'],
            ['nama' => 'A. Herdiawan', 'email' => 'aherdiawan@ymail.com', 'username' => 'aherdiawan', 'bagian' => 'PMS'],
            ['nama' => 'Dadang Jamaludin', 'email' => 'dadang_ibp@yahoo.com', 'username' => 'dadang_ibp', 'bagian' => 'OPS'],
            ['nama' => 'Iwan Setya Permana', 'email' => 'iwansetyap@gmail.com', 'username' => 'iwansetyap', 'bagian' => 'OPS'],
            ['nama' => 'Endah Kartika Rahayu', 'email' => 'ekr0303@yahoo.co.id', 'username' => 'ekr0303', 'bagian' => 'KEU'],
            ['nama' => 'Heru Rizky Amboinawaty', 'email' => 'qqhra@yahoo.com', 'username' => 'qqhra', 'bagian' => 'KEU'],
            ['nama' => 'Ismayanti', 'email' => 'isma.y29@yahoo.com', 'username' => 'isma.y29', 'bagian' => 'SDM'],
            ['nama' => 'Edi Djunaedi Permana', 'email' => 'edijunaedi55567@gmail.com', 'username' => 'edijunaedi55567', 'bagian' => 'TLH'],
            ['nama' => 'Tarto', 'email' => 'tartoyanto@gmail.com', 'username' => 'tartoyanto', 'bagian' => 'TLH'],
            ['nama' => 'Didi Rasdi', 'email' => 'didi.rasdi@gmail.com', 'username' => 'didi.rasdi', 'bagian' => 'TLH'],
            ['nama' => 'Erwin Fardiansyah', 'email' => 'erwin.fardiansyah@gmail.com', 'username' => 'erwin.fardiansyah', 'bagian' => 'OPS'],
        ];

        foreach ($karyawanData as $item) {
            $karyawanId = DB::table('karyawans')->where('email', $item['email'])->value('id');

            if (!$karyawanId) {
                $karyawanId = DB::table('karyawans')->insertGetId([
                    'nama' => $item['nama'],
                    'email' => $item['email'],
                    'status' => 'Aktif',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $bagianId = DB::table('bagians')->where('kode_bagian', $item['bagian'])->value('id') ?: $bagianDefault;

            DB::table('users')->updateOrInsert(
                ['username' => $item['username']],
                [
                    'bagian_id' => $bagianId,
                    'role_id' => $roleUser,
                    'name' => $item['nama'],
                    'email' => $item['email'],
                    'password' => Hash::make('password'),
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            DB::table('pengguna_karyawans')->updateOrInsert(
                ['username' => $item['username']],
                [
                    'karyawan_id' => $karyawanId,
                    'password' => Hash::make('password'),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
