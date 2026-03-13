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
        Schema::table('people', function (Blueprint $table): void {
            $table->foreignId('department_id')
                ->nullable()
                ->after('classification_id')
                ->constrained('departments')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('people', function (Blueprint $table): void {
            $table->dropForeignIdFor(\App\Models\Department::class);
            $table->dropColumn('department_id');
        });
    }
};
