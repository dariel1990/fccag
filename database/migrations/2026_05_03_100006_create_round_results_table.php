<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('round_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained('rooms')->cascadeOnDelete();
            $table->unsignedInteger('round_number');
            $table->string('category');
            $table->string('real_word');
            $table->string('impostor_word')->nullable();
            $table->foreignId('impostor_player_id')->constrained('players')->cascadeOnDelete();
            $table->foreignId('eliminated_player_id')->nullable()->constrained('players')->nullOnDelete();
            $table->enum('outcome', ['caught', 'escaped', 'tie']);
            $table->timestamps();

            $table->unique(['room_id', 'round_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('round_results');
    }
};
