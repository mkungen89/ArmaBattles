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
        Schema::create('match_check_ins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('match_id')->constrained('tournament_matches')->onDelete('cascade');
            $table->foreignId('team_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamp('checked_in_at');
            $table->timestamps();

            $table->unique(['match_id', 'team_id']);
        });

        // Add scheduling fields to tournament_matches
        Schema::table('tournament_matches', function (Blueprint $table) {
            $table->timestamp('check_in_opens_at')->nullable()->after('scheduled_at');
            $table->timestamp('check_in_closes_at')->nullable()->after('check_in_opens_at');
            $table->boolean('team1_checked_in')->default(false)->after('check_in_closes_at');
            $table->boolean('team2_checked_in')->default(false)->after('team1_checked_in');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('match_check_ins');

        Schema::table('tournament_matches', function (Blueprint $table) {
            $table->dropColumn(['check_in_opens_at', 'check_in_closes_at', 'team1_checked_in', 'team2_checked_in']);
        });
    }
};
