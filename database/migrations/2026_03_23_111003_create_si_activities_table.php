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
        Schema::create('si_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('si_activity_category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('activity_id')->nullable()->constrained()->nullOnDelete()->comment('Optional link to main activities table');
            $table->string('title');
            $table->string('speaker')->nullable();
            $table->string('topic')->nullable();
            $table->string('memory_verse')->nullable();
            $table->date('conducted_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('si_activities');
    }
};
