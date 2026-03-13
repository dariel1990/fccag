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
        Schema::table('people', function (Blueprint $table) {
            $table->dropColumn(['cell_group', 'ministry']);
            $table->foreignId('cell_group_id')->nullable()->constrained('cell_groups')->nullOnDelete()->after('address');
            $table->foreignId('classification_id')->nullable()->constrained('classifications')->nullOnDelete()->after('cell_group_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('people', function (Blueprint $table) {
            $table->dropConstrainedForeignId('cell_group_id');
            $table->dropConstrainedForeignId('classification_id');
            $table->string('cell_group')->nullable();
            $table->string('ministry')->nullable();
        });
    }
};
