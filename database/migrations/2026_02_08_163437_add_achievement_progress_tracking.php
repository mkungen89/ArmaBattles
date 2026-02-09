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
        // Add badge_path and points to achievements table
        Schema::table('achievements', function (Blueprint $table) {
            $table->string('badge_path')->nullable()->after('icon');
            $table->integer('points')->default(10)->after('threshold');
        });

        // Create achievement_progress table for tracking progress towards unearned achievements
        Schema::create('achievement_progress', function (Blueprint $table) {
            $table->id();
            $table->string('player_uuid')->index();
            $table->foreignId('achievement_id')->constrained()->onDelete('cascade');
            $table->integer('current_value')->default(0);
            $table->integer('target_value');
            $table->decimal('percentage', 5, 2)->default(0.00);
            $table->timestamps();
            $table->unique(['player_uuid', 'achievement_id']);

            $table->index('percentage');
        });

        // Create achievement_showcases table for pinned achievements
        Schema::create('achievement_showcases', function (Blueprint $table) {
            $table->id();
            $table->string('player_uuid')->unique();
            $table->json('pinned_achievements'); // Array of up to 3 achievement IDs
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('achievement_showcases');
        Schema::dropIfExists('achievement_progress');

        Schema::table('achievements', function (Blueprint $table) {
            $table->dropColumn(['badge_path', 'points']);
        });
    }
};
