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
        Schema::create('pastors', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('title')->nullable(); // e.g. Rev., Ptr., Bishop
            $table->string('role')->nullable(); // e.g. Senior Pastor, Associate Pastor
            $table->text('bio')->nullable();
            $table->string('photo_path')->nullable();
            $table->string('contact_number')->nullable();
            $table->string('email')->nullable();
            $table->date('date_started')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pastors');
    }
};
