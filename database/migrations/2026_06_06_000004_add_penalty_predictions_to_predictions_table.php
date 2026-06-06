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
        Schema::table('predictions', function (Blueprint $table) {
            $table->unsignedTinyInteger('pen_home_score')->nullable()->after('away_score');
            $table->unsignedTinyInteger('pen_away_score')->nullable()->after('pen_home_score');
            $table->unsignedTinyInteger('pen_points')->nullable()->after('points');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('predictions', function (Blueprint $table) {
            $table->dropColumn(['pen_home_score', 'pen_away_score', 'pen_points']);
        });
    }
};
