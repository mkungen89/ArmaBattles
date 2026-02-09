<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $changes = [
        'chat_messages' => ['player_uuid', 'recipient_uuid'],
        'gm_actions' => ['gm_uuid'],
        'healing_events' => ['healer_uuid', 'patient_uuid'],
        'player_damage' => ['attacker_uuid', 'victim_uuid'],
        'player_kills' => ['killer_uuid', 'victim_uuid'],
        'player_sessions' => ['player_uuid'],
        'player_stats' => ['player_uuid'],
        'squad_changes' => ['player_uuid'],
        'supply_deliveries' => ['player_uuid'],
        'vehicle_events' => ['player_uuid', 'destroyed_by_uuid'],
        'weapon_usage' => ['player_uuid'],
    ];

    public function up(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        foreach ($this->changes as $table => $columns) {
            foreach ($columns as $column) {
                DB::statement("ALTER TABLE {$table} ALTER COLUMN {$column} TYPE VARCHAR(255)");
            }
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        // Note: Converting back to UUID may fail if non-UUID data exists
        foreach ($this->changes as $table => $columns) {
            foreach ($columns as $column) {
                DB::statement("ALTER TABLE {$table} ALTER COLUMN {$column} TYPE UUID USING {$column}::uuid");
            }
        }
    }
};
