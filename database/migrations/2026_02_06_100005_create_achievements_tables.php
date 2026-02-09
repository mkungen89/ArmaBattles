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
        Schema::create('achievements', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->text('description');
            $table->string('icon', 50)->default('star'); // lucide icon name
            $table->string('color', 30)->default('green'); // tailwind color
            $table->string('category'); // combat, support, activity, special
            $table->string('stat_field')->nullable(); // which player_stats field to check
            $table->unsignedBigInteger('threshold')->nullable(); // value needed
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('player_achievements', function (Blueprint $table) {
            $table->id();
            $table->string('player_uuid')->index();
            $table->foreignId('achievement_id')->constrained()->onDelete('cascade');
            $table->timestamp('earned_at');
            $table->timestamps();
            $table->unique(['player_uuid', 'achievement_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('player_achievements');
        Schema::dropIfExists('achievements');
    }
};
