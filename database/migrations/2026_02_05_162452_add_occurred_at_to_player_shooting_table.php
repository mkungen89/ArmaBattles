<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $tables = [
        'player_kills',
        'player_sessions',
        'player_grenades',
        'player_shooting',
        'player_healing_rjs',
        'supply_deliveries_rjs',
        'vehicle_events_rjs',
        'server_status',
        'game_sessions',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach ($this->tables as $table) {
            if (Schema::hasTable($table) && !Schema::hasColumn($table, 'occurred_at')) {
                Schema::table($table, function (Blueprint $table) {
                    $table->timestamp('occurred_at')->nullable();
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach ($this->tables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'occurred_at')) {
                Schema::table($table, function (Blueprint $table) {
                    $table->dropColumn('occurred_at');
                });
            }
        }
    }
};
