<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('penggajians', function (Blueprint $table) {
            if (!Schema::hasColumn('penggajians', 'makan_transport')) {
                $table->decimal('makan_transport', 15, 2)->default(0)->after('gaji_pokok');
                $table->decimal('tj_jabatan', 15, 2)->default(0)->after('makan_transport');
                $table->decimal('bonus', 15, 2)->default(0)->after('tj_jabatan');
                $table->decimal('thr', 15, 2)->default(0)->after('bonus');
                $table->decimal('pot_bpjs', 15, 2)->default(0)->after('thr');
                $table->decimal('pot_dplk', 15, 2)->default(0)->after('pot_bpjs');
                $table->decimal('pot_koperasi', 15, 2)->default(0)->after('pot_dplk');
                $table->decimal('pot_absensi', 15, 2)->default(0)->after('pot_koperasi');
                $table->decimal('pot_lainnya', 15, 2)->default(0)->after('pot_absensi');
            }
        });
    }

    public function down(): void
    {
        Schema::table('penggajians', function (Blueprint $table) {
            $table->dropColumn([
                'makan_transport', 'tj_jabatan', 'bonus', 'thr',
                'pot_bpjs', 'pot_dplk', 'pot_koperasi', 'pot_absensi', 'pot_lainnya'
            ]);
        });
    }
};
