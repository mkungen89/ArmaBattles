<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'pgsql') {
            // PostgreSQL: ALTER COLUMN TYPE
            DB::statement('ALTER TABLE achievement_progress ALTER COLUMN current_value TYPE numeric(12,2) USING current_value::numeric(12,2)');
            DB::statement('ALTER TABLE achievement_progress ALTER COLUMN target_value TYPE numeric(12,2) USING target_value::numeric(12,2)');
        } else {
            // SQLite/MySQL: Recreate table (SQLite doesn't support ALTER COLUMN TYPE)
            Schema::table('achievement_progress', function (Blueprint $table) {
                $table->decimal('current_value', 12, 2)->change();
                $table->decimal('target_value', 12, 2)->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE achievement_progress ALTER COLUMN current_value TYPE integer USING current_value::integer');
            DB::statement('ALTER TABLE achievement_progress ALTER COLUMN target_value TYPE integer USING target_value::integer');
        } else {
            Schema::table('achievement_progress', function (Blueprint $table) {
                $table->integer('current_value')->change();
                $table->integer('target_value')->change();
            });
        }
    }
};
