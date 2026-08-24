<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_surat', 50)->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('bagian_id')->constrained('bagians')->cascadeOnDelete();
            $table->foreignId('pos_biaya_id')->constrained('pos_biayas')->cascadeOnDelete();
            $table->date('tanggal');
            $table->decimal('nilai', 15, 2);
            $table->string('terbilang', 500);
            $table->text('keterangan');
            $table->string('status', 191)->default('Draft');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vouchers');
    }
};