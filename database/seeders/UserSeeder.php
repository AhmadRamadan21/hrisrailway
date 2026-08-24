<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $roleSuperAdmin = DB::table('roles')->where('name', 'Super Admin')->value('id');
        $roleAdmin      = DB::table('roles')->where('name', 'Admin')->value('id');
        $roleUser       = DB::table('roles')->where('name', 'User')->value('id');

        $bagianIT       = DB::table('bagians')->where('kode_bagian', 'IT')->value('id');
        $bagianKeuangan = DB::table('bagians')->where('kode_bagian', 'KEU')->value('id');
        $bagianSek      = DB::table('bagians')->where('kode_bagian', 'SEK')->value('id');

        $users = [
            [
                'bagian_id' => $bagianIT,
                'role_id'   => $roleSuperAdmin,
                'name'      => 'Super Admin',
                'username'  => 'superadmin',
                'email'     => 'superadmin@ibp.com',
                'password'  => Hash::make('password'),
                'status'    => 'active',
            ],
            [
                'bagian_id' => $bagianKeuangan,
                'role_id'   => $roleAdmin,
                'name'      => 'Admin Keuangan',
                'username'  => 'admin',
                'email'     => 'admin@ibp.com',
                'password'  => Hash::make('password'),
                'status'    => 'active',
            ],
            [
                'bagian_id' => $bagianSek,
                'role_id'   => $roleUser,
                'name'      => 'User Sekretariat',
                'username'  => 'user',
                'email'     => 'user@ibp.com',
                'password'  => Hash::make('password'),
                'status'    => 'active',
            ],
        ];

        foreach ($users as $user) {
            DB::table('users')->insertOrIgnore(array_merge($user, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}