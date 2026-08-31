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
        Schema::table('curated_answers', function (Blueprint $table) {
            $table->timestamp('flagged_at')->nullable();
            $table->foreignId('flagged_by_document_id')->nullable()->constrained('documents')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('curated_answers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('flagged_by_document_id');
            $table->dropConstrainedForeignId('reviewed_by');
            $table->dropColumn(['flagged_at', 'reviewed_at']);
        });
    }
};
