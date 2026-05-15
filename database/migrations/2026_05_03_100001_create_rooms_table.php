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
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->string('code', 6)->unique();
            $table->foreignId('host_player_id')->constrained('players')->cascadeOnDelete();
            $table->enum('status', ['waiting', 'playing', 'voting', 'ended'])->default('waiting');
            $table->string('category')->nullable();
            $table->string('word')->nullable();
            $table->string('impostor_word')->nullable();
            $table->unsignedInteger('round_number')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
