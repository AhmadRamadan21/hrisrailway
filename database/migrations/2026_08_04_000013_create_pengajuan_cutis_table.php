<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pengajuan_cutis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('karyawan_id')->constrained('karyawans')->onDelete('cascade');
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->integer('durasi');
            $table->text('alasan')->nullable();
            $table->string('status')->default('Menunggu');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pengajuan_cutis');
    }
};
