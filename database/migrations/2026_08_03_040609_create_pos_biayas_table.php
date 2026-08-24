<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pos_biayas', function (Blueprint $table) {
            $table->id();
            $table->string('nama_biaya', 200)->unique();
            $table->decimal('anggaran', 15, 2)->default(0);
            $table->decimal('realisasi', 15, 2)->default(0);
            $table->decimal('sisa', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_biayas');
    }
};