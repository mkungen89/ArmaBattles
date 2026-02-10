<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('player_ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('player_uuid')->unique();
            $table->decimal('rating', 8, 2)->default(1500);
            $table->decimal('rating_deviation', 8, 2)->default(350);
            $table->decimal('volatility', 8, 6)->default(0.06);
            $table->string('rank_tier')->default('unranked');
            $table->integer('ranked_kills')->default(0);
            $table->integer('ranked_deaths')->default(0);
            $table->integer('games_played')->default(0);
            $table->integer('placement_games')->default(0);
            $table->boolean('is_placed')->default(false);
            $table->decimal('peak_rating', 8, 2)->default(1500);
            $table->decimal('season_start_rating', 8, 2)->nullable();
            $table->integer('current_season')->default(1);
            $table->timestamp('opted_in_at')->nullable();
            $table->timestamp('last_rated_at')->nullable();
            $table->timestamps();

            $table->index('rating');
            $table->index('rank_tier');
            $table->index(['rating', 'is_placed']);
            $table->index('player_uuid');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('player_ratings');
    }
};
