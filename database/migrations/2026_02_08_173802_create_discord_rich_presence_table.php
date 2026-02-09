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
        Schema::create('discord_rich_presence', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('discord_user_id', 100)->nullable(); // Discord snowflake ID
            $table->string('current_activity', 100)->nullable(); // 'playing', 'watching_tournament', 'browsing'
            $table->text('activity_details')->nullable(); // JSON with server name, tournament name, etc.
            $table->foreignId('server_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('tournament_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('started_at')->nullable();
            $table->boolean('enabled')->default(true);
            $table->timestamp('last_updated_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'enabled']);
            $table->index('current_activity');
            $table->index('last_updated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discord_rich_presence');
    }
};
