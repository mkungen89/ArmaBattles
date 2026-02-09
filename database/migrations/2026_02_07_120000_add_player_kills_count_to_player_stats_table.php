<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('player_stats', function (Blueprint $table) {
            if (!Schema::hasColumn('player_stats', 'player_kills_count')) {
                $table->unsignedInteger('player_kills_count')->default(0)->after('kills');
            }
        });

        // Backfill from player_kills table: count kills where victim is not AI
        if (Schema::hasTable('player_kills')) {
            DB::statement("
                UPDATE player_stats SET player_kills_count = (
                    SELECT COUNT(*) FROM player_kills
                    WHERE player_kills.killer_uuid = player_stats.player_uuid
                    AND (player_kills.victim_type IS NULL OR player_kills.victim_type != 'AI')
                )
            ");
        }
    }

    public function down(): void
    {
        Schema::table('player_stats', function (Blueprint $table) {
            if (Schema::hasColumn('player_stats', 'player_kills_count')) {
                $table->dropColumn('player_kills_count');
            }
        });
    }
};
