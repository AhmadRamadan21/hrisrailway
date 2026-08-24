<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengguna_karyawans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('karyawan_id')->unique()->constrained('karyawans')->cascadeOnDelete();
            $table->string('username')->unique();
            $table->string('password');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengguna_karyawans');
    }
};