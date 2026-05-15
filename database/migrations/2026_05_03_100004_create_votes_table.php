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
        Schema::create('votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained('rooms')->cascadeOnDelete();
            $table->foreignId('voter_player_id')->constrained('players')->cascadeOnDelete();
            $table->foreignId('target_player_id')->constrained('players')->cascadeOnDelete();
            $table->unsignedInteger('round_number');
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['room_id', 'voter_player_id', 'round_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('votes');
    }
};
