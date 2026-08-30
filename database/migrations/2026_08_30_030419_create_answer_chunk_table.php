<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('answer_chunk', function (Blueprint $table) {
            $table->foreignId('answer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('chunk_id')->constrained()->cascadeOnDelete();
            $table->float('similarity');
            $table->primary(['answer_id', 'chunk_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('answer_chunk');
    }
};
