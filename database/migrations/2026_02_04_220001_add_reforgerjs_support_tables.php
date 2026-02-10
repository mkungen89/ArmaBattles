<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Update player_kills with vehicle info
        Schema::table('player_kills', function (Blueprint $table) {
            $table->boolean('killer_in_vehicle')->default(false)->after('killer_faction');
            $table->string('killer_vehicle')->nullable()->after('killer_in_vehicle');
            $table->string('killer_vehicle_prefab')->nullable()->after('killer_vehicle');
            $table->json('killer_position')->nullable()->after('killer_vehicle_prefab');
            $table->json('victim_position')->nullable()->after('victim_uuid');
            $table->string('ai_type')->nullable()->after('victim_type');
            $table->string('killer_role')->nullable()->after('ai_type');
            $table->string('killer_platform')->nullable()->after('killer_role');
            $table->string('damage_type')->nullable()->after('killer_platform');
        });

        // Player distance/playtime tracking
        Schema::create('player_distance', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('server_id');
            $table->string('player_name');
            $table->string('player_uuid');
            $table->integer('player_id')->nullable();
            $table->string('player_platform')->nullable();
            $table->string('player_faction')->nullable();
            $table->decimal('walking_distance', 12, 2)->default(0);
            $table->decimal('walking_time_seconds', 12, 2)->default(0);
            $table->decimal('total_vehicle_distance', 12, 2)->default(0);
            $table->decimal('total_vehicle_time_seconds', 12, 2)->default(0);
            $table->json('vehicles')->nullable(); // Detailed vehicle usage
            $table->boolean('is_final_log')->default(false);
            $table->timestamp('event_time')->nullable();
            $table->timestamp('occurred_at')->nullable();
            $table->timestamps();

            $table->index(['player_uuid', 'event_time']);
            $table->index('server_id');
        });

        // Grenades thrown
        Schema::create('player_grenades', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('server_id');
            $table->string('player_name');
            $table->string('player_uuid');
            $table->integer('player_id')->nullable();
            $table->string('player_platform')->nullable();
            $table->string('player_faction')->nullable();
            $table->string('grenade_type')->nullable();
            $table->json('position')->nullable();
            $table->timestamp('event_time')->nullable();
            $table->timestamp('occurred_at')->nullable();
            $table->timestamps();

            $table->index(['player_uuid', 'event_time']);
            $table->index('server_id');
        });

        // Shots fired
        Schema::create('player_shooting', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('server_id');
            $table->string('player_name');
            $table->string('player_uuid');
            $table->integer('player_id')->nullable();
            $table->string('player_platform')->nullable();
            $table->string('player_faction')->nullable();
            $table->string('weapons')->nullable(); // "M21 SWS: 10"
            $table->integer('total_rounds')->default(0);
            $table->timestamp('event_time')->nullable();
            $table->timestamp('occurred_at')->nullable();
            $table->timestamps();

            $table->index(['player_uuid', 'event_time']);
            $table->index('server_id');
        });

        // Player healing (ReforgerJS format - different from healing_events)
        Schema::create('player_healing_rjs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('server_id');
            $table->string('healer_name');
            $table->string('healer_uuid')->nullable();
            $table->integer('healer_id')->nullable();
            $table->string('target_name')->nullable();
            $table->string('target_uuid')->nullable();
            $table->integer('target_id')->nullable();
            $table->string('heal_type')->nullable(); // bandage, morphine, tourniquet
            $table->decimal('heal_amount', 8, 2)->nullable();
            $table->boolean('is_self_heal')->default(false);
            $table->timestamp('event_time')->nullable();
            $table->timestamp('occurred_at')->nullable();
            $table->timestamps();

            $table->index(['healer_uuid', 'event_time']);
            $table->index('server_id');
        });

        // Vehicle events (ReforgerJS format - different from existing)
        Schema::create('vehicle_events_rjs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('server_id');
            $table->string('event_type'); // VEHICLE_DESTROYED, VEHICLE_ENTERED
            $table->string('vehicle_name');
            $table->string('vehicle_prefab')->nullable();
            $table->string('player_name')->nullable();
            $table->string('player_uuid')->nullable();
            $table->json('position')->nullable();
            $table->string('destroyed_by')->nullable();
            $table->timestamp('event_time');
            $table->timestamps();

            $table->index(['event_type', 'event_time']);
            $table->index('server_id');
        });

        // Supply deliveries (ReforgerJS format - different from existing)
        Schema::create('supply_deliveries_rjs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('server_id');
            $table->string('player_name');
            $table->string('player_uuid');
            $table->string('supply_type');
            $table->integer('amount');
            $table->string('base_name')->nullable();
            $table->timestamp('event_time');
            $table->timestamps();

            $table->index(['player_uuid', 'event_time']);
            $table->index('server_id');
        });
    }

    public function down(): void
    {
        Schema::table('player_kills', function (Blueprint $table) {
            $table->dropColumn([
                'killer_in_vehicle',
                'killer_vehicle',
                'killer_vehicle_prefab',
                'killer_position',
                'victim_position',
                'ai_type',
                'killer_role',
                'killer_platform',
                'damage_type',
            ]);
        });

        Schema::dropIfExists('player_distance');
        Schema::dropIfExists('player_grenades');
        Schema::dropIfExists('player_shooting');
        Schema::dropIfExists('player_healing_rjs');
        Schema::dropIfExists('vehicle_events_rjs');
        Schema::dropIfExists('supply_deliveries_rjs');
    }
};
