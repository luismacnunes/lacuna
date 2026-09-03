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
        Schema::table('chunks', function (Blueprint $table) {
        $table->float('map_x')->nullable();
        $table->float('map_y')->nullable();
        });

        Schema::table('questions', function (Blueprint $table) {
            $table->float('map_x')->nullable();
            $table->float('map_y')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chunks', function (Blueprint $table) {
        $table->dropColumn(['map_x', 'map_y']);
        });

        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn(['map_x', 'map_y']);
        });
    }
};
