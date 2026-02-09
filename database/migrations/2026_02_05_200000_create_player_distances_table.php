<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('player_distance')) {
            return;
        }

        Schema::create('player_distance', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('server_id');
            $table->string('player_name');
            $table->string('player_uuid')->nullable()->index();
            $table->integer('player_id')->nullable();
            $table->string('player_platform', 50)->nullable();
            $table->string('player_faction')->nullable();
            $table->decimal('walking_distance', 12, 2)->nullable();
            $table->decimal('walking_time_seconds', 12, 2)->nullable();
            $table->decimal('total_vehicle_distance', 12, 2)->nullable();
            $table->decimal('total_vehicle_time_seconds', 12, 2)->nullable();
            $table->json('vehicles')->nullable();
            $table->boolean('is_final_log')->default(false);
            $table->timestamp('occurred_at')->nullable();
            $table->timestamps();

            $table->index(['server_id', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('player_distance');
    }
};
