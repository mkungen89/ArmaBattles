<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Kills table
        if (! Schema::hasTable('kills')) {
            Schema::create('kills', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('server_id');
                $table->string('killer_name')->nullable();
                $table->string('killer_uuid')->nullable()->index();
                $table->string('killer_faction')->nullable();
                $table->integer('killer_id')->nullable();
                $table->string('killer_platform', 50)->nullable();
                $table->string('killer_role')->nullable();
                $table->json('killer_position')->nullable();
                $table->boolean('killer_in_vehicle')->default(false);
                $table->string('killer_vehicle')->nullable();
                $table->string('killer_vehicle_prefab')->nullable();
                $table->string('victim_name')->nullable();
                $table->string('victim_uuid')->nullable()->index();
                $table->string('victim_faction')->nullable();
                $table->integer('victim_id')->nullable();
                $table->boolean('victim_is_ai')->default(false);
                $table->string('victim_role')->nullable();
                $table->json('victim_position')->nullable();
                $table->string('victim_platform', 50)->nullable();
                $table->string('ai_type')->nullable();
                $table->string('weapon_name')->nullable();
                $table->string('weapon_type')->nullable();
                $table->string('damage_type')->nullable();
                $table->decimal('kill_distance', 10, 2)->nullable();
                $table->boolean('is_team_kill')->default(false);
                $table->string('event_type', 50)->nullable();
                $table->timestamp('occurred_at')->nullable();
                $table->timestamps();

                $table->index(['server_id', 'occurred_at']);
                $table->index('weapon_name');
            });
        }

        // Damage Events (batch hit zone data)
        if (! Schema::hasTable('damage_events')) {
            Schema::create('damage_events', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('server_id');
                $table->string('damage_type', 50)->nullable();
                $table->decimal('damage_amount', 10, 4)->nullable();
                $table->string('hit_zone_name', 50)->nullable()->index();
                $table->string('killer_name')->nullable();
                $table->string('killer_uuid')->nullable()->index();
                $table->integer('killer_id')->nullable();
                $table->string('killer_faction')->nullable();
                $table->string('victim_name')->nullable();
                $table->string('victim_uuid')->nullable()->index();
                $table->integer('victim_id')->nullable();
                $table->string('victim_faction')->nullable();
                $table->string('weapon_name')->nullable();
                $table->decimal('distance', 10, 2)->nullable();
                $table->boolean('is_friendly_fire')->default(false);
                $table->timestamp('occurred_at')->nullable();
                $table->timestamps();

                $table->index(['server_id', 'occurred_at']);
            });
        }

        // Connections (player connect/disconnect)
        if (! Schema::hasTable('connections')) {
            Schema::create('connections', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('server_id');
                $table->string('event_type', 50); // CONNECT, DISCONNECT
                $table->string('player_name');
                $table->string('player_uuid')->nullable()->index();
                $table->integer('player_id')->nullable();
                $table->string('player_platform', 50)->nullable();
                $table->string('player_faction')->nullable();
                $table->string('profile_name')->nullable();
                $table->timestamp('occurred_at')->nullable();
                $table->timestamps();

                $table->index(['server_id', 'occurred_at']);
            });
        }

        // Base Events (capture/seized)
        if (! Schema::hasTable('base_events')) {
            Schema::create('base_events', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('server_id');
                $table->string('event_type', 50); // BASE_SEIZED, BASE_CAPTURE
                $table->string('base_name')->nullable();
                $table->json('position')->nullable();
                // Individual player
                $table->string('player_name')->nullable();
                $table->string('player_uuid')->nullable()->index();
                $table->integer('player_id')->nullable();
                $table->string('player_faction')->nullable();
                $table->integer('xp_awarded')->nullable();
                // Team event
                $table->string('capturing_faction')->nullable();
                $table->string('previous_faction')->nullable();
                $table->integer('player_count')->nullable();
                $table->string('player_ids')->nullable();
                $table->text('player_names')->nullable();
                $table->timestamp('occurred_at')->nullable();
                $table->timestamps();

                $table->index(['server_id', 'occurred_at']);
            });
        }

        // Building Events
        if (! Schema::hasTable('building_events')) {
            Schema::create('building_events', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('server_id');
                $table->string('event_type', 50);
                $table->string('player_name');
                $table->string('player_uuid')->nullable()->index();
                $table->integer('player_id')->nullable();
                $table->string('player_platform', 50)->nullable();
                $table->string('player_faction')->nullable();
                $table->string('composition_name')->nullable();
                $table->string('composition_type')->nullable();
                $table->integer('prefab_id')->nullable();
                $table->string('provider')->nullable();
                $table->json('position')->nullable();
                $table->timestamp('occurred_at')->nullable();
                $table->timestamps();

                $table->index(['server_id', 'occurred_at']);
            });
        }

        // Consciousness Events (knocked/unconscious)
        if (! Schema::hasTable('consciousness_events')) {
            Schema::create('consciousness_events', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('server_id');
                $table->string('event_type', 50); // CONSCIOUSNESS_CHANGED, KNOCKED
                $table->string('player_name');
                $table->string('player_uuid')->nullable()->index();
                $table->integer('player_id')->nullable();
                $table->string('player_faction')->nullable();
                $table->string('state', 50)->nullable(); // CONSCIOUS, UNCONSCIOUS
                $table->json('position')->nullable();
                // Knocker info
                $table->string('knocker_name')->nullable();
                $table->string('knocker_uuid')->nullable();
                $table->integer('knocker_id')->nullable();
                $table->string('knocker_faction')->nullable();
                $table->boolean('is_friendly_knock')->default(false);
                $table->boolean('is_self_knock')->default(false);
                $table->timestamp('occurred_at')->nullable();
                $table->timestamps();

                $table->index(['server_id', 'occurred_at']);
            });
        }

        // Group Events (squad join/leave)
        if (! Schema::hasTable('group_events')) {
            Schema::create('group_events', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('server_id');
                $table->string('event_type', 50); // GROUP_JOINED, GROUP_LEFT
                $table->string('player_name');
                $table->string('player_uuid')->nullable()->index();
                $table->integer('player_id')->nullable();
                $table->string('player_faction')->nullable();
                $table->string('group_name');
                $table->timestamp('occurred_at')->nullable();
                $table->timestamps();

                $table->index(['server_id', 'occurred_at']);
            });
        }

        // XP Events
        if (! Schema::hasTable('xp_events')) {
            Schema::create('xp_events', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('server_id');
                $table->string('player_name');
                $table->string('player_uuid')->nullable()->index();
                $table->integer('player_id')->nullable();
                $table->string('player_faction')->nullable();
                $table->string('reward_type', 100); // ENEMY_KILL, BASE_SEIZED, etc.
                $table->integer('reward_type_raw')->nullable();
                $table->integer('xp_amount');
                $table->timestamp('occurred_at')->nullable();
                $table->timestamps();

                $table->index(['server_id', 'occurred_at']);
                $table->index('reward_type');
            });
        }

        // Chat Events
        if (! Schema::hasTable('chat_events')) {
            Schema::create('chat_events', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('server_id');
                $table->string('player_name');
                $table->string('player_uuid')->nullable()->index();
                $table->integer('player_id')->nullable();
                $table->text('message');
                $table->string('channel', 50)->nullable();
                $table->timestamp('occurred_at')->nullable();
                $table->timestamps();

                $table->index(['server_id', 'occurred_at']);
            });
        }

        // Editor Actions (GM actions)
        if (! Schema::hasTable('editor_actions')) {
            Schema::create('editor_actions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('server_id');
                $table->string('player_name');
                $table->string('player_uuid')->nullable()->index();
                $table->integer('player_id')->nullable();
                $table->string('action');
                $table->string('hovered_entity_component_name')->nullable();
                $table->integer('hovered_entity_component_owner_id')->nullable();
                $table->string('selected_entity_components_owners_ids', 500)->nullable();
                $table->string('selected_entity_components_names', 500)->nullable();
                $table->timestamp('occurred_at')->nullable();
                $table->timestamps();

                $table->index(['server_id', 'occurred_at']);
            });
        }

        // GM Sessions (enter/exit)
        if (! Schema::hasTable('gm_sessions')) {
            Schema::create('gm_sessions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('server_id');
                $table->string('event_type', 50); // GM_ENTER, GM_EXIT
                $table->string('player_name');
                $table->string('player_uuid')->nullable()->index();
                $table->integer('player_id')->nullable();
                $table->integer('duration')->nullable(); // seconds, only for GM_EXIT
                $table->timestamp('occurred_at')->nullable();
                $table->timestamps();

                $table->index(['server_id', 'occurred_at']);
            });
        }

        // Players table for stats tracking
        if (! Schema::hasTable('players')) {
            Schema::create('players', function (Blueprint $table) {
                $table->id();
                $table->string('uuid')->unique();
                $table->string('name');
                $table->boolean('is_online')->default(false);
                $table->timestamp('last_seen')->nullable();
                $table->string('platform', 50)->nullable();
                $table->unsignedBigInteger('kills')->default(0);
                $table->unsignedBigInteger('deaths')->default(0);
                $table->unsignedBigInteger('total_xp')->default(0);
                $table->decimal('total_walking_distance', 12, 2)->default(0);
                $table->decimal('total_vehicle_distance', 12, 2)->default(0);
                $table->unsignedBigInteger('total_shots_fired')->default(0);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('gm_sessions');
        Schema::dropIfExists('editor_actions');
        Schema::dropIfExists('chat_events');
        Schema::dropIfExists('xp_events');
        Schema::dropIfExists('group_events');
        Schema::dropIfExists('consciousness_events');
        Schema::dropIfExists('building_events');
        Schema::dropIfExists('base_events');
        Schema::dropIfExists('connections');
        Schema::dropIfExists('damage_events');
        Schema::dropIfExists('kills');
    }
};
