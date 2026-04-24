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
        Schema::create('setlist_songs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('setlist_id')->constrained()->cascadeOnDelete();
            $table->foreignId('song_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('order')->default(0);
            $table->string('key_override', 3)->nullable();
            $table->string('notes')->nullable();
            $table->timestamps();

            $table->unique(['setlist_id', 'song_id']);
            $table->index(['setlist_id', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('setlist_songs');
    }
};
