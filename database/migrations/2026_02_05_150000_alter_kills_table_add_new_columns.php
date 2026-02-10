<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kills', function (Blueprint $table) {
            // Add new columns if they don't exist
            if (! Schema::hasColumn('kills', 'killer_uuid')) {
                $table->string('killer_uuid')->nullable()->index()->after('server_id');
            }
            if (! Schema::hasColumn('kills', 'killer_faction')) {
                $table->string('killer_faction')->nullable()->after('killer_uuid');
            }
            if (! Schema::hasColumn('kills', 'killer_platform')) {
                $table->string('killer_platform', 50)->nullable()->after('killer_faction');
            }
            if (! Schema::hasColumn('kills', 'killer_role')) {
                $table->string('killer_role')->nullable()->after('killer_platform');
            }
            if (! Schema::hasColumn('kills', 'killer_position')) {
                $table->json('killer_position')->nullable()->after('killer_role');
            }
            if (! Schema::hasColumn('kills', 'killer_in_vehicle')) {
                $table->boolean('killer_in_vehicle')->default(false)->after('killer_position');
            }
            if (! Schema::hasColumn('kills', 'killer_vehicle')) {
                $table->string('killer_vehicle')->nullable()->after('killer_in_vehicle');
            }
            if (! Schema::hasColumn('kills', 'killer_vehicle_prefab')) {
                $table->string('killer_vehicle_prefab')->nullable()->after('killer_vehicle');
            }
            if (! Schema::hasColumn('kills', 'victim_uuid')) {
                $table->string('victim_uuid')->nullable()->index()->after('victim_name');
            }
            if (! Schema::hasColumn('kills', 'victim_faction')) {
                $table->string('victim_faction')->nullable()->after('victim_uuid');
            }
            if (! Schema::hasColumn('kills', 'victim_is_ai')) {
                $table->boolean('victim_is_ai')->default(false)->after('victim_faction');
            }
            if (! Schema::hasColumn('kills', 'victim_role')) {
                $table->string('victim_role')->nullable()->after('victim_is_ai');
            }
            if (! Schema::hasColumn('kills', 'victim_position')) {
                $table->json('victim_position')->nullable()->after('victim_role');
            }
            if (! Schema::hasColumn('kills', 'victim_platform')) {
                $table->string('victim_platform', 50)->nullable()->after('victim_position');
            }
            if (! Schema::hasColumn('kills', 'ai_type')) {
                $table->string('ai_type')->nullable()->after('victim_platform');
            }
            if (! Schema::hasColumn('kills', 'weapon_name')) {
                $table->string('weapon_name')->nullable()->after('ai_type');
            }
            if (! Schema::hasColumn('kills', 'weapon_type')) {
                $table->string('weapon_type')->nullable()->after('weapon_name');
            }
            if (! Schema::hasColumn('kills', 'damage_type')) {
                $table->string('damage_type')->nullable()->after('weapon_type');
            }
            if (! Schema::hasColumn('kills', 'kill_distance')) {
                $table->decimal('kill_distance', 10, 2)->nullable()->after('damage_type');
            }
            if (! Schema::hasColumn('kills', 'is_team_kill')) {
                $table->boolean('is_team_kill')->default(false)->after('kill_distance');
            }
            if (! Schema::hasColumn('kills', 'event_type')) {
                $table->string('event_type', 50)->nullable()->after('is_team_kill');
            }
            if (! Schema::hasColumn('kills', 'occurred_at')) {
                $table->timestamp('occurred_at')->nullable()->after('event_type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('kills', function (Blueprint $table) {
            $columns = [
                'killer_uuid', 'killer_faction', 'killer_platform', 'killer_role',
                'killer_position', 'killer_in_vehicle', 'killer_vehicle', 'killer_vehicle_prefab',
                'victim_uuid', 'victim_faction', 'victim_is_ai', 'victim_role',
                'victim_position', 'victim_platform', 'ai_type', 'weapon_name',
                'weapon_type', 'damage_type', 'kill_distance', 'is_team_kill',
                'event_type', 'occurred_at',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('kills', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
