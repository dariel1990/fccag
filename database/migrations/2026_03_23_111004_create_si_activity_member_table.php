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
        Schema::create('si_activity_member', function (Blueprint $table) {
            $table->id();
            $table->foreignId('si_activity_id')->constrained()->cascadeOnDelete();
            $table->foreignId('si_member_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['si_activity_id', 'si_member_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('si_activity_member');
    }
};
