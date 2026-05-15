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
        Schema::create('word_packs', function (Blueprint $table) {
            $table->id();
            $table->string('category');
            $table->string('word');
            $table->timestamp('created_at')->useCurrent();

            $table->index('category');
            $table->unique(['category', 'word']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('word_packs');
    }
};
