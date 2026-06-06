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
        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->string('external_key')->unique();
            $table->string('round');
            $table->string('group_name')->nullable();
            $table->string('ground')->nullable();
            $table->string('team1');
            $table->string('team2');
            $table->dateTime('kickoff_at');
            $table->unsignedTinyInteger('home_score')->nullable();
            $table->unsignedTinyInteger('away_score')->nullable();
            $table->boolean('is_finished')->default(false);
            $table->timestamps();

            $table->index('kickoff_at');
            $table->index('is_finished');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('games');
    }
};
