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
        Schema::table('player_stats', function (Blueprint $table) {
            $table->integer('level')->default(1)->after('last_seen_at');
            $table->bigInteger('level_xp')->default(0)->after('level')->comment('Total XP counting towards level progression');
            $table->integer('achievement_points')->default(0)->after('level_xp')->comment('Points earned from achievements');

            $table->index('level');
        });

        // Add points to achievements table
        Schema::table('achievements', function (Blueprint $table) {
            if (! Schema::hasColumn('achievements', 'points')) {
                $table->integer('points')->default(10)->after('threshold')->comment('Points awarded when unlocked');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('player_stats', function (Blueprint $table) {
            $table->dropIndex(['level']);
            $table->dropColumn(['level', 'level_xp', 'achievement_points']);
        });

        Schema::table('achievements', function (Blueprint $table) {
            if (Schema::hasColumn('achievements', 'points')) {
                $table->dropColumn('points');
            }
        });
    }
};
