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
        Schema::create('player_reputations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->integer('total_score')->default(0);
            $table->integer('positive_votes')->default(0);
            $table->integer('negative_votes')->default(0);
            $table->integer('teamwork_count')->default(0);
            $table->integer('leadership_count')->default(0);
            $table->integer('sportsmanship_count')->default(0);
            $table->timestamp('last_decay_at')->nullable();
            $table->timestamps();

            $table->index('total_score');
            $table->index(['total_score', 'positive_votes']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('player_reputations');
    }
};
