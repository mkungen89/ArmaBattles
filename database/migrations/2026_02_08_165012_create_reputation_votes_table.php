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
        Schema::create('reputation_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('voter_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('target_id')->constrained('users')->cascadeOnDelete();
            $table->enum('vote_type', ['positive', 'negative']);
            $table->enum('category', ['teamwork', 'leadership', 'sportsmanship', 'general'])->default('general');
            $table->text('comment')->nullable();
            $table->timestamps();

            // Prevent duplicate votes within 24 hours
            $table->unique(['voter_id', 'target_id']);
            $table->index('target_id');
            $table->index(['voter_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reputation_votes');
    }
};
