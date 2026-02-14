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
        Schema::create('recruitment_listings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('team_id')->nullable()->constrained()->onDelete('cascade'); // Team looking for players
            $table->string('listing_type'); // player_looking_for_team, team_looking_for_players
            $table->text('message');
            $table->json('preferred_roles')->nullable(); // Array of role IDs
            $table->string('playstyle')->nullable(); // casual, competitive, milsim
            $table->string('region')->nullable(); // NA, EU, APAC, etc.
            $table->string('availability')->nullable(); // weekdays, weekends, both
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index('listing_type');
            $table->index('is_active');
            $table->index('is_featured');
            $table->index('playstyle');
            $table->index('region');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recruitment_listings');
    }
};
