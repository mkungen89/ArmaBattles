<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop all existing tables that we're replacing
        Schema::dropIfExists('server_status');
        Schema::dropIfExists('game_sessions');
        Schema::dropIfExists('gm_actions');
        Schema::dropIfExists('squad_changes');
        Schema::dropIfExists('chat_messages');
        Schema::dropIfExists('vehicle_events');
        Schema::dropIfExists('weapon_usage');
        Schema::dropIfExists('supply_deliveries');
        Schema::dropIfExists('base_captures');
        Schema::dropIfExists('healing_events');
        Schema::dropIfExists('player_stats');
        Schema::dropIfExists('player_sessions');
        Schema::dropIfExists('player_damage');
        Schema::dropIfExists('player_kills');
        Schema::dropIfExists('game_server_status');
        Schema::dropIfExists('game_events');
        Schema::dropIfExists('kill_logs');

        // Player Kills (with weapon details)
        Schema::create('player_kills', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('server_id');
            $table->string('killer_name');
            $table->uuid('killer_uuid')->nullable()->index();
            $table->string('killer_faction')->nullable();
            $table->string('victim_type'); // AI, PLAYER
            $table->string('victim_name')->nullable();
            $table->uuid('victim_uuid')->nullable()->index();
            $table->string('weapon_name');
            $table->string('weapon_type')->nullable();
            $table->decimal('kill_distance', 10, 2)->default(0);
            $table->boolean('is_team_kill')->default(false);
            $table->boolean('is_headshot')->default(false);
            $table->string('event_type')->default('UNKNOWN');
            $table->timestamp('killed_at');
            $table->timestamps();

            $table->index(['killer_uuid', 'killed_at']);
            $table->index(['weapon_name']);
            $table->index(['server_id', 'killed_at']);
        });

        // Player Damage Events (with headshot detection)
        Schema::create('player_damage', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('server_id');
            $table->string('attacker_name');
            $table->uuid('attacker_uuid')->nullable()->index();
            $table->string('victim_name');
            $table->uuid('victim_uuid')->nullable()->index();
            $table->string('weapon_name');
            $table->decimal('damage', 8, 2);
            $table->string('hit_zone')->nullable(); // Head, Torso, Arms, Legs
            $table->boolean('is_friendly_fire')->default(false);
            $table->boolean('is_headshot')->default(false);
            $table->timestamp('damaged_at');
            $table->timestamps();

            $table->index(['attacker_uuid', 'damaged_at']);
            $table->index(['hit_zone']);
        });

        // Player Sessions (connections/disconnections)
        Schema::create('player_sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('server_id');
            $table->string('player_name');
            $table->uuid('player_uuid')->nullable()->index();
            $table->string('platform')->nullable(); // windows, xbox, playstation
            $table->string('ip_address')->nullable();
            $table->timestamp('connected_at');
            $table->timestamp('disconnected_at')->nullable();
            $table->unsignedInteger('playtime_seconds')->default(0);
            $table->timestamps();

            $table->index(['player_uuid', 'connected_at']);
        });

        // Player Stats (aggregated)
        Schema::create('player_stats', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('server_id');
            $table->uuid('player_uuid')->index();
            $table->string('player_name');
            $table->unsignedInteger('kills')->default(0);
            $table->unsignedInteger('deaths')->default(0);
            $table->unsignedInteger('headshots')->default(0);
            $table->unsignedInteger('team_kills')->default(0);
            $table->unsignedInteger('playtime_seconds')->default(0);
            $table->decimal('total_distance', 12, 2)->default(0);
            $table->unsignedInteger('shots_fired')->default(0);
            $table->unsignedInteger('grenades_thrown')->default(0);
            $table->unsignedInteger('heals_given')->default(0);
            $table->unsignedInteger('heals_received')->default(0);
            $table->unsignedInteger('bases_captured')->default(0);
            $table->unsignedInteger('supplies_delivered')->default(0);
            $table->unsignedInteger('vehicles_destroyed')->default(0);
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();

            $table->unique(['server_id', 'player_uuid']);
        });

        // Healing Events
        Schema::create('healing_events', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('server_id');
            $table->string('healer_name');
            $table->uuid('healer_uuid')->nullable()->index();
            $table->string('patient_name');
            $table->uuid('patient_uuid')->nullable()->index();
            $table->string('item_used'); // bandage, morphine, tourniquet, saline
            $table->decimal('health_restored', 8, 2)->default(0);
            $table->boolean('is_self_heal')->default(false);
            $table->timestamp('healed_at');
            $table->timestamps();
        });

        // Base Captures
        Schema::create('base_captures', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('server_id');
            $table->string('base_name');
            $table->string('capturing_faction');
            $table->string('losing_faction')->nullable();
            $table->json('participating_players')->nullable(); // Array of player UUIDs
            $table->string('capture_type')->default('capture'); // capture, contest, neutralize
            $table->timestamp('captured_at');
            $table->timestamps();

            $table->index(['server_id', 'captured_at']);
        });

        // Supply Deliveries
        Schema::create('supply_deliveries', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('server_id');
            $table->string('player_name');
            $table->uuid('player_uuid')->nullable()->index();
            $table->string('supply_type');
            $table->unsignedInteger('amount')->default(1);
            $table->string('destination')->nullable();
            $table->timestamp('delivered_at');
            $table->timestamps();
        });

        // Weapon Usage (shots, grenades, explosives)
        Schema::create('weapon_usage', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('server_id');
            $table->string('player_name');
            $table->uuid('player_uuid')->nullable()->index();
            $table->string('action_type'); // shot_fired, grenade_thrown, explosive_placed, mortar_fired
            $table->string('weapon_name');
            $table->string('weapon_type')->nullable();
            $table->unsignedInteger('count')->default(1);
            $table->timestamp('used_at');
            $table->timestamps();

            $table->index(['player_uuid', 'action_type']);
        });

        // Vehicle Events
        Schema::create('vehicle_events', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('server_id');
            $table->string('player_name')->nullable();
            $table->uuid('player_uuid')->nullable()->index();
            $table->string('vehicle_type');
            $table->string('vehicle_name');
            $table->string('event_type'); // entered, exited, destroyed, created
            $table->string('destroyed_by')->nullable();
            $table->uuid('destroyed_by_uuid')->nullable();
            $table->string('destruction_weapon')->nullable();
            $table->timestamp('event_at');
            $table->timestamps();
        });

        // Chat Messages
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('server_id');
            $table->string('player_name');
            $table->uuid('player_uuid')->nullable()->index();
            $table->string('channel'); // global, team, squad, private, vehicle
            $table->text('message');
            $table->string('recipient_name')->nullable(); // For private messages
            $table->uuid('recipient_uuid')->nullable();
            $table->timestamp('sent_at');
            $table->timestamps();

            $table->index(['server_id', 'sent_at']);
            $table->index(['channel']);
        });

        // Squad/Group Changes
        Schema::create('squad_changes', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('server_id');
            $table->string('player_name');
            $table->uuid('player_uuid')->nullable()->index();
            $table->string('action_type'); // joined, left, promoted, demoted, created
            $table->string('squad_name')->nullable();
            $table->string('faction')->nullable();
            $table->string('role')->nullable(); // leader, member
            $table->timestamp('changed_at');
            $table->timestamps();
        });

        // Game Master Actions
        Schema::create('gm_actions', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('server_id');
            $table->string('gm_name');
            $table->uuid('gm_uuid')->nullable()->index();
            $table->string('action_type'); // spawn, delete, teleport, heal, kill, etc.
            $table->string('target_type')->nullable(); // player, vehicle, object, ai
            $table->string('target_name')->nullable();
            $table->json('action_details')->nullable();
            $table->timestamp('action_at');
            $table->timestamps();
        });

        // Game Sessions (start/end)
        Schema::create('game_sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('server_id');
            $table->string('game_mode');
            $table->string('map_name')->nullable();
            $table->string('scenario')->nullable();
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->string('winner_faction')->nullable();
            $table->json('final_scores')->nullable();
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->unsignedInteger('peak_players')->default(0);
            $table->timestamps();
        });

        // Server Status (periodic snapshots)
        Schema::create('server_status', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('server_id');
            $table->unsignedInteger('players_online')->default(0);
            $table->unsignedInteger('max_players')->default(64);
            $table->unsignedInteger('ai_count')->default(0);
            $table->decimal('fps', 6, 2)->nullable();
            $table->unsignedInteger('memory_mb')->nullable();
            $table->unsignedInteger('uptime_seconds')->default(0);
            $table->timestamp('recorded_at');
            $table->timestamps();

            $table->index(['server_id', 'recorded_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('server_status');
        Schema::dropIfExists('game_sessions');
        Schema::dropIfExists('gm_actions');
        Schema::dropIfExists('squad_changes');
        Schema::dropIfExists('chat_messages');
        Schema::dropIfExists('vehicle_events');
        Schema::dropIfExists('weapon_usage');
        Schema::dropIfExists('supply_deliveries');
        Schema::dropIfExists('base_captures');
        Schema::dropIfExists('healing_events');
        Schema::dropIfExists('player_stats');
        Schema::dropIfExists('player_sessions');
        Schema::dropIfExists('player_damage');
        Schema::dropIfExists('player_kills');
    }
};
