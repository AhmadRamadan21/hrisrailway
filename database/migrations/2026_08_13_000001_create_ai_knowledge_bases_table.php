<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('ai_knowledge_bases')) {
            Schema::create('ai_knowledge_bases', function (Blueprint $table) {
                $table->id();
                $table->string('topik');
                $table->text('keywords'); // kata kunci koma-terpisah e.g. "seragam, baju, pakaian"
                $table->text('jawaban');  // rincian jawaban AI
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_knowledge_bases');
    }
};
