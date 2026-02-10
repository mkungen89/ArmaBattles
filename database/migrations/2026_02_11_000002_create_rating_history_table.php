<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rating_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_rating_id')->constrained('player_ratings')->cascadeOnDelete();
            $table->string('player_uuid');
            $table->decimal('rating_before', 8, 2);
            $table->decimal('rating_after', 8, 2);
            $table->decimal('rd_before', 8, 2);
            $table->decimal('rd_after', 8, 2);
            $table->decimal('volatility_before', 8, 6);
            $table->decimal('volatility_after', 8, 6);
            $table->string('rank_tier_before')->nullable();
            $table->string('rank_tier_after')->nullable();
            $table->integer('period_kills')->default(0);
            $table->integer('period_deaths')->default(0);
            $table->integer('period_encounters')->default(0);
            $table->integer('season')->default(1);
            $table->timestamp('period_start')->nullable();
            $table->timestamp('period_end')->nullable();
            $table->timestamps();

            $table->index(['player_uuid', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rating_history');
    }
};
