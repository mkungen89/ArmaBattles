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
        Schema::create('tournament_matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tournament_id')->constrained()->onDelete('cascade');

            $table->integer('round');
            $table->integer('match_number');
            $table->string('bracket')->default('main');

            $table->foreignId('team1_id')->nullable()->constrained('teams')->nullOnDelete();
            $table->foreignId('team2_id')->nullable()->constrained('teams')->nullOnDelete();
            $table->foreignId('winner_id')->nullable()->constrained('teams')->nullOnDelete();

            $table->unsignedBigInteger('winner_goes_to')->nullable();
            $table->unsignedBigInteger('loser_goes_to')->nullable();

            $table->integer('team1_score')->nullable();
            $table->integer('team2_score')->nullable();

            $table->enum('status', ['pending', 'scheduled', 'in_progress', 'completed', 'disputed', 'cancelled'])->default('pending');
            $table->enum('match_type', ['best_of_1', 'best_of_3', 'best_of_5'])->default('best_of_1');

            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->foreign('winner_goes_to')->references('id')->on('tournament_matches')->nullOnDelete();
            $table->foreign('loser_goes_to')->references('id')->on('tournament_matches')->nullOnDelete();

            $table->index(['tournament_id', 'round', 'bracket']);
            $table->index(['tournament_id', 'status']);
            $table->index('scheduled_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tournament_matches');
    }
};
