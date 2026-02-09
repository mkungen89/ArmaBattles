<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('player_stats', function (Blueprint $table) {
            if (!Schema::hasColumn('player_stats', 'xp_total')) {
                $table->unsignedBigInteger('xp_total')->default(0)->after('vehicles_destroyed');
            }
        });
    }

    public function down(): void
    {
        Schema::table('player_stats', function (Blueprint $table) {
            if (Schema::hasColumn('player_stats', 'xp_total')) {
                $table->dropColumn('xp_total');
            }
        });
    }
};
