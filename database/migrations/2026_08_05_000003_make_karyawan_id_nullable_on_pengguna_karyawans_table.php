<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Pakai raw SQL (bukan $table->foreignId(...)->change()) supaya
        // tidak perlu install package doctrine/dbal tambahan.
        if (Schema::hasColumn('pengguna_karyawans', 'karyawan_id')) {
            DB::statement('ALTER TABLE pengguna_karyawans MODIFY karyawan_id BIGINT UNSIGNED NULL');
        }
    }

    public function down(): void
    {
        // Isi dulu baris yang NULL sebelum di-set NOT NULL lagi, kalau perlu rollback.
        DB::statement('ALTER TABLE pengguna_karyawans MODIFY karyawan_id BIGINT UNSIGNED NOT NULL');
    }
};