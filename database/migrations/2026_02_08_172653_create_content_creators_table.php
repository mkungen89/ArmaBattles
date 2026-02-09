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
        Schema::create('content_creators', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('platform', 50); // twitch, youtube, tiktok, kick
            $table->string('channel_url');
            $table->string('channel_name')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_live')->default(false);
            $table->integer('follower_count')->nullable();
            $table->integer('viewer_count')->nullable();
            $table->string('stream_title')->nullable();
            $table->string('stream_thumbnail_url')->nullable();
            $table->text('bio')->nullable();
            $table->timestamp('last_live_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            $table->index('platform');
            $table->index('is_verified');
            $table->index('is_live');
            $table->index('last_live_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('content_creators');
    }
};
