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
        Schema::create('player_roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // medic, rifleman, sniper, engineer, squad_leader, etc.
            $table->string('display_name');
            $table->text('description')->nullable();
            $table->string('icon')->nullable(); // Lucide icon name
            $table->string('category')->nullable(); // infantry, support, specialist, leadership
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('category');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('player_roles');
    }
};
