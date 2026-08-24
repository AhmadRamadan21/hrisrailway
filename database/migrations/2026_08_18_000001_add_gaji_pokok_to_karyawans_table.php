<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('karyawans', function (Blueprint $table) {
            if (!Schema::hasColumn('karyawans', 'gaji_pokok')) {
                $table->decimal('gaji_pokok', 15, 2)->nullable()->default(3977678)->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('karyawans', function (Blueprint $table) {
            if (Schema::hasColumn('karyawans', 'gaji_pokok')) {
                $table->dropColumn('gaji_pokok');
            }
        });
    }
};
