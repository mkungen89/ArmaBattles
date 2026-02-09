<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('anticheat_stats', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('server_id');
            $table->unsignedInteger('active_players')->default(0);
            $table->unsignedInteger('online_players')->default(0);
            $table->unsignedInteger('registered_players')->default(0);
            $table->unsignedInteger('potential_cheaters')->default(0);
            $table->json('banned_players')->nullable();
            $table->json('confirmed_cheaters')->nullable();
            $table->json('potentials_list')->nullable();
            $table->json('top_movement')->nullable();
            $table->json('top_collision')->nullable();
            $table->timestamp('event_time')->nullable();
            $table->timestamps();

            $table->index(['server_id', 'event_time']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('anticheat_stats');
    }
};
