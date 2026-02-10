<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fix column compatibility between ReforgerJS table migrations and StatsController.
     *
     * The original migrations created `event_time` NOT NULL columns, but
     * StatsController writes to `occurred_at`. This migration adds the
     * `occurred_at` column and makes `event_time` nullable on PostgreSQL.
     *
     * Also adds missing columns to supply_deliveries that StatsController writes.
     */
    public function up(): void
    {
        $isPgsql = DB::getDriverName() === 'pgsql';

        // Only run ALTER TABLE on PostgreSQL (production).
        // SQLite tests get the fixed schema from updated original migrations.
        if (! $isPgsql) {
            return;
        }

        $tables = ['player_distance', 'player_shooting', 'player_grenades', 'player_healing_rjs'];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                if (! Schema::hasColumn($table, 'occurred_at')) {
                    Schema::table($table, function (Blueprint $blueprint) {
                        $blueprint->timestamp('occurred_at')->nullable();
                    });
                }

                if (Schema::hasColumn($table, 'event_time')) {
                    DB::statement("ALTER TABLE {$table} ALTER COLUMN event_time DROP NOT NULL");
                }
            }
        }

        // Make weapons nullable in player_shooting
        if (Schema::hasTable('player_shooting') && Schema::hasColumn('player_shooting', 'weapons')) {
            DB::statement('ALTER TABLE player_shooting ALTER COLUMN weapons DROP NOT NULL');
        }

        // Make heal_type nullable in player_healing_rjs
        if (Schema::hasTable('player_healing_rjs') && Schema::hasColumn('player_healing_rjs', 'heal_type')) {
            DB::statement('ALTER TABLE player_healing_rjs ALTER COLUMN heal_type DROP NOT NULL');
        }

        // Add missing columns to supply_deliveries
        if (Schema::hasTable('supply_deliveries')) {
            Schema::table('supply_deliveries', function (Blueprint $table) {
                if (! Schema::hasColumn('supply_deliveries', 'supply_type')) {
                    $table->string('supply_type')->nullable();
                }
                if (! Schema::hasColumn('supply_deliveries', 'amount')) {
                    $table->integer('amount')->nullable();
                }
                if (! Schema::hasColumn('supply_deliveries', 'delivered_at')) {
                    $table->timestamp('delivered_at')->nullable();
                }
            });
        }
    }

    public function down(): void
    {
        // Not reversing since these are compatibility fixes
    }
};
