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
        Schema::table('si_members', function (Blueprint $table) {
            $table->json('spiritual_assessments')->nullable()->after('status');
            $table->json('activity_assessments')->nullable()->after('spiritual_assessments');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('si_members', function (Blueprint $table) {
            $table->dropColumn(['spiritual_assessments', 'activity_assessments']);
        });
    }
};
