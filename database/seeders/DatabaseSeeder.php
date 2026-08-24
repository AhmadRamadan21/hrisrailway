<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            BagianSeeder::class,
            DivisiSeeder::class,
            JabatanSeeder::class,
            UserSeeder::class,
            PosBiayaSeeder::class,
        ]);
    }
}