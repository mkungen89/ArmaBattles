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
        Schema::create('player_warnings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('moderator_id')->constrained('users')->onDelete('cascade');
            $table->string('warning_type'); // spam, toxicity, cheating_accusation, inappropriate_behavior
            $table->text('reason');
            $table->text('evidence')->nullable(); // Chat logs, screenshots, etc.
            $table->string('severity')->default('low'); // low, medium, high, critical
            $table->boolean('auto_ban_triggered')->default(false);
            $table->timestamp('expires_at')->nullable(); // Warnings can expire
            $table->timestamps();

            $table->index('user_id');
            $table->index('warning_type');
            $table->index('severity');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('player_warnings');
    }
};
