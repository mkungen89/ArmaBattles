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
        Schema::create('moderator_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // User being noted
            $table->foreignId('moderator_id')->constrained('users')->onDelete('cascade'); // Moderator making note
            $table->text('note');
            $table->string('category')->nullable(); // positive, negative, neutral, watchlist
            $table->boolean('is_flagged')->default(false); // Important notes
            $table->timestamps();

            $table->index('user_id');
            $table->index('moderator_id');
            $table->index('category');
            $table->index('is_flagged');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('moderator_notes');
    }
};
