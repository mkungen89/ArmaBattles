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
        Schema::create('highlight_clips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('url');
            $table->string('platform', 50); // youtube, twitch, tiktok, kick
            $table->text('description')->nullable();
            $table->string('thumbnail_url')->nullable();
            $table->integer('votes')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->timestamp('featured_at')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('platform');
            $table->index('votes');
            $table->index('is_featured');
            $table->index('created_at');
        });

        // Clip votes tracking
        Schema::create('clip_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('clip_id')->constrained('highlight_clips')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'clip_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clip_votes');
        Schema::dropIfExists('highlight_clips');
    }
};
