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
        DB::statement('ALTER TABLE curated_answers ADD COLUMN embedding vector(1536)');

        Schema::table('pending_questions', function (Blueprint $table) {
            $table->foreign('curated_answer_id')->references('id')->on('curated_answers')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pending_questions', function (Blueprint $table) {
            $table->dropForeign(['curated_answer_id']);
        });

        DB::statement('ALTER TABLE curated_answers DROP COLUMN embedding');
    }
};
