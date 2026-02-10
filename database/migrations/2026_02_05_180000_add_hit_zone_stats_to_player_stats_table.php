<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('player_stats', function (Blueprint $table) {
            if (! Schema::hasColumn('player_stats', 'hits_head')) {
                $table->unsignedInteger('hits_head')->default(0)->after('headshots');
            }
            if (! Schema::hasColumn('player_stats', 'hits_torso')) {
                $table->unsignedInteger('hits_torso')->default(0)->after('hits_head');
            }
            if (! Schema::hasColumn('player_stats', 'hits_arms')) {
                $table->unsignedInteger('hits_arms')->default(0)->after('hits_torso');
            }
            if (! Schema::hasColumn('player_stats', 'hits_legs')) {
                $table->unsignedInteger('hits_legs')->default(0)->after('hits_arms');
            }
            if (! Schema::hasColumn('player_stats', 'total_hits')) {
                $table->unsignedInteger('total_hits')->default(0)->after('hits_legs');
            }
            if (! Schema::hasColumn('player_stats', 'total_damage_dealt')) {
                $table->decimal('total_damage_dealt', 12, 2)->default(0)->after('total_hits');
            }
        });
    }

    public function down(): void
    {
        Schema::table('player_stats', function (Blueprint $table) {
            $columns = ['hits_head', 'hits_torso', 'hits_arms', 'hits_legs', 'total_hits', 'total_damage_dealt'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('player_stats', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
