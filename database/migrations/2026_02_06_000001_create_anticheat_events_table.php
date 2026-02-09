<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('anticheat_events', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('server_id');
            $table->string('event_type'); // ENFORCEMENT_ACTION, ENFORCEMENT_SKIPPED, LIFESTATE, SPAWN_GRACE, OTHER, UNKNOWN
            $table->string('player_name')->nullable();
            $table->unsignedInteger('player_id')->nullable();
            $table->boolean('is_admin')->default(false);
            $table->string('reason')->nullable();
            $table->text('raw')->nullable();
            $table->timestamp('event_time')->nullable();
            $table->timestamps();

            $table->index(['server_id', 'event_time']);
            $table->index(['event_type']);
            $table->index(['player_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('anticheat_events');
    }
};
