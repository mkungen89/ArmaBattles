<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rated_kills_queue', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('kill_id');
            $table->string('killer_uuid');
            $table->string('victim_uuid');
            $table->boolean('is_headshot')->default(false);
            $table->boolean('is_team_kill')->default(false);
            $table->decimal('kill_distance', 10, 2)->default(0);
            $table->string('weapon_name')->nullable();
            $table->integer('server_id')->nullable();
            $table->timestamp('killed_at');
            $table->boolean('processed')->default(false);
            $table->timestamps();

            $table->index(['processed', 'killed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rated_kills_queue');
    }
};
