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
        Schema::create('rank_logos', function (Blueprint $table) {
            $table->id();
            $table->integer('rank')->unique()->comment('Rank number 1-50');
            $table->string('name')->comment('Rank name (e.g., Recruit, Private)');
            $table->integer('era')->comment('Era number 1-10');
            $table->integer('min_level')->comment('Starting level for this rank');
            $table->integer('max_level')->comment('Ending level for this rank');
            $table->string('logo_path')->nullable()->comment('Path to rank logo image');
            $table->string('color', 7)->default('#22c55e')->comment('Era-specific color hex');
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['era', 'rank']);
            $table->index('min_level');
            $table->index('max_level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rank_logos');
    }
};
