<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('penggajians', function (Blueprint $table) {
            $table->id();
            $table->foreignId('karyawan_id')->constrained('karyawans')->onDelete('cascade');
            $table->unsignedTinyInteger('bulan');
            $table->unsignedSmallInteger('tahun');
            $table->decimal('gaji_pokok', 15, 2)->default(0);
            $table->decimal('tunjangan', 15, 2)->default(0);
            $table->decimal('potongan', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->string('status')->default('Pending');
            $table->timestamps();
            $table->unique(['karyawan_id','bulan','tahun']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('penggajians');
    }
};
