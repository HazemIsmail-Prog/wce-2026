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
        Schema::table('games', function (Blueprint $table) {
            $table->boolean('went_to_penalties')->default(false)->after('is_finished');
            $table->unsignedTinyInteger('pen_home_score')->nullable()->after('went_to_penalties');
            $table->unsignedTinyInteger('pen_away_score')->nullable()->after('pen_home_score');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('games', function (Blueprint $table) {
            $table->dropColumn(['went_to_penalties', 'pen_home_score', 'pen_away_score']);
        });
    }
};
