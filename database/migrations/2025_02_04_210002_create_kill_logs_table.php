<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kill_logs', function (Blueprint $table) {
            $table->id();
            $table->integer('server_id')->nullable()->index();

            // Killer info
            $table->string('killer_id', 255)->nullable()->index();
            $table->string('killer_name', 255)->nullable();
            $table->boolean('killer_is_ai')->default(false);

            // Victim info
            $table->string('victim_id', 255)->nullable()->index();
            $table->string('victim_name', 255)->nullable();
            $table->boolean('victim_is_ai')->default(false);

            // Weapon info
            $table->string('weapon', 255)->nullable();
            $table->string('weapon_prefab', 500)->nullable();

            // Additional info
            $table->boolean('is_friendly_fire')->default(false);
            $table->decimal('distance', 10, 2)->nullable();
            $table->json('killer_position')->nullable();
            $table->json('victim_position')->nullable();

            $table->timestamp('event_timestamp')->nullable()->index();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['killer_id', 'event_timestamp']);
            $table->index(['victim_id', 'event_timestamp']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kill_logs');
    }
};
