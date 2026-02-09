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
        Schema::create('scrim_invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scrim_match_id')->constrained('scrim_matches')->cascadeOnDelete();
            $table->foreignId('inviting_team_id')->constrained('teams')->cascadeOnDelete();
            $table->foreignId('invited_team_id')->constrained('teams')->cascadeOnDelete();
            $table->string('status', 50)->default('pending'); // pending, accepted, declined
            $table->timestamp('responded_at')->nullable();
            $table->timestamp('expires_at');
            $table->timestamps();

            $table->index('status');
            $table->index(['invited_team_id', 'status']);
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scrim_invitations');
    }
};
