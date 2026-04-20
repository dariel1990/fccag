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
        Schema::create('si_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('caregiver_id')->constrained('people')->cascadeOnDelete();
            $table->string('name');
            $table->string('sex');
            $table->string('ph_id')->nullable()->unique()->comment('Program/PH ID assigned to the child');
            $table->string('address')->nullable();
            $table->string('status')->default('active');
            $table->date('enrolled_at');
            $table->date('exited_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('si_members');
    }
};
