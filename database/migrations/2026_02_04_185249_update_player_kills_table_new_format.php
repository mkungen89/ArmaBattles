<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop the old table and recreate with new schema
        Schema::dropIfExists('player_kills');

        Schema::create('player_kills', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('server_id');
            $table->string('killer_name');
            $table->string('killer_uuid')->nullable()->index();
            $table->string('killer_faction')->nullable();
            $table->string('victim_type'); // AI, PLAYER
            $table->string('victim_name')->nullable();
            $table->string('victim_uuid')->nullable()->index();
            $table->string('weapon_name');
            $table->string('weapon_type')->nullable();
            $table->string('weapon_source')->nullable();
            $table->string('sight_name')->nullable();
            $table->string('attachments', 500)->nullable();
            $table->string('grenade_type')->nullable();
            $table->decimal('kill_distance', 10, 2)->default(0);
            $table->boolean('is_team_kill')->default(false);
            $table->string('event_type')->default('UNKNOWN');
            $table->timestamp('killed_at');
            $table->timestamps();

            $table->index(['killer_uuid', 'killed_at']);
            $table->index(['weapon_name']);
            $table->index(['server_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('player_kills');

        // Recreate old schema
        Schema::create('player_kills', function (Blueprint $table) {
            $table->id();
            $table->integer('server_id')->index();
            $table->string('player_uuid', 255)->index();
            $table->integer('kills_total')->default(0);
            $table->integer('kills_delta')->default(0);
            $table->string('kill_type', 50)->default('unknown');
            $table->timestamp('timestamp')->index();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['player_uuid', 'server_id']);
        });
    }
};
