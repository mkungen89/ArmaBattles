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
        Schema::create('tournaments', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->text('rules')->nullable();
            $table->string('banner_url')->nullable();

            $table->enum('format', ['single_elimination', 'double_elimination', 'round_robin', 'swiss']);
            $table->enum('status', ['draft', 'registration_open', 'registration_closed', 'in_progress', 'completed', 'cancelled'])->default('draft');

            $table->integer('max_teams')->default(16);
            $table->integer('min_teams')->default(4);
            $table->integer('team_size')->default(5);
            $table->integer('swiss_rounds')->nullable();

            $table->timestamp('registration_starts_at')->nullable();
            $table->timestamp('registration_ends_at')->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();

            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('winner_team_id')->nullable()->constrained('teams')->nullOnDelete();
            $table->foreignId('server_id')->nullable()->constrained('servers')->nullOnDelete();

            $table->boolean('is_featured')->default(false);
            $table->boolean('require_approval')->default(true);

            $table->timestamps();

            $table->index(['status', 'starts_at']);
            $table->index('is_featured');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tournaments');
    }
};
