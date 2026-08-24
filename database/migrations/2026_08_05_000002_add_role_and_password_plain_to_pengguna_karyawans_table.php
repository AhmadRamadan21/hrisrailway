<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengguna_karyawans', function (Blueprint $table) {
            if (!Schema::hasColumn('pengguna_karyawans', 'role')) {
                $table->string('role')->default('user')->after('username');
            }
            if (!Schema::hasColumn('pengguna_karyawans', 'password_plain')) {
                // Menyimpan password asli (plaintext) supaya bisa ditampilkan di tabel.
                // PERHATIAN: ini praktik yang kurang aman, pertimbangkan untuk
                // menghapus kolom ini / menyembunyikan tampilannya di production.
                $table->string('password_plain')->nullable()->after('password');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pengguna_karyawans', function (Blueprint $table) {
            $table->dropColumn(['role', 'password_plain']);
        });
    }
};