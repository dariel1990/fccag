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
        Schema::table('si_attendances', function (Blueprint $table) {
            $table->text('remarks')->nullable()->change();
            $table->text('recommendation')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('si_attendances', function (Blueprint $table) {
            $table->string('remarks')->nullable()->change();
            $table->string('recommendation')->nullable()->change();
        });
    }
};
