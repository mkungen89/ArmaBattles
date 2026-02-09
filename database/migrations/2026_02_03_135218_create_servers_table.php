<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('servers', function (Blueprint $table) {
            $table->id();
            $table->string('battlemetrics_id')->unique();
            $table->string('name');
            $table->string('ip')->nullable();
            $table->integer('port')->nullable();
            $table->integer('query_port')->nullable();
            $table->string('map')->nullable();
            $table->string('scenario')->nullable();
            $table->integer('players')->default(0);
            $table->integer('max_players')->default(128);
            $table->string('status')->default('offline');
            $table->string('country')->nullable();
            $table->string('country_code')->nullable();
            $table->string('game_version')->nullable();
            $table->string('game_build')->nullable();
            $table->boolean('is_official')->default(false);
            $table->boolean('is_joinable')->default(true);
            $table->boolean('is_visible')->default(true);
            $table->boolean('is_password_protected')->default(false);
            $table->boolean('battleye_enabled')->default(true);
            $table->boolean('crossplay_enabled')->default(false);
            $table->json('supported_platforms')->nullable();
            $table->string('direct_join_code')->nullable();
            $table->integer('rank')->nullable();
            $table->timestamp('session_started_at')->nullable();
            $table->timestamp('last_updated_at')->nullable();
            $table->timestamps();

            $table->index('battlemetrics_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('servers');
    }
};
