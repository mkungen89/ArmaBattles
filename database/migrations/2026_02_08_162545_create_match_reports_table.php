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
        Schema::create('match_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('match_id')->constrained('tournament_matches')->cascadeOnDelete();
            $table->foreignId('referee_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('winning_team_id')->nullable()->constrained('teams')->nullOnDelete();
            $table->integer('team1_score')->default(0);
            $table->integer('team2_score')->default(0);
            $table->text('notes')->nullable();
            $table->json('incidents')->nullable(); // Store any incidents (disputes, rule violations, etc.)
            $table->string('status')->default('submitted'); // submitted, approved, disputed
            $table->timestamp('reported_at');
            $table->timestamps();

            $table->index('match_id');
            $table->index('referee_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('match_reports');
    }
};
