<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('absensis', 'distance')) {
            Schema::table('absensis', function (Blueprint $table) {
                $table->integer('distance')->nullable()->after('status')->comment('Jarak GPS dalam meter');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('absensis', 'distance')) {
            Schema::table('absensis', function (Blueprint $table) {
                $table->dropColumn('distance');
            });
        }
    }
};
