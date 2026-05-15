<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->unsignedTinyInteger('total_rounds')->default(3)->after('round_number');
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE rooms MODIFY COLUMN status ENUM('waiting','playing','voting','round_end','finished','ended') NOT NULL DEFAULT 'waiting'");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("UPDATE rooms SET status = 'ended' WHERE status IN ('round_end','finished')");
            DB::statement("ALTER TABLE rooms MODIFY COLUMN status ENUM('waiting','playing','voting','ended') NOT NULL DEFAULT 'waiting'");
        }

        Schema::table('rooms', function (Blueprint $table) {
            $table->dropColumn('total_rounds');
        });
    }
};
