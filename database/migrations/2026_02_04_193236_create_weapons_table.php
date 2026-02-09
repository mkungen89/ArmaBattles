<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weapons', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // The weapon name from game server
            $table->string('display_name')->nullable(); // Optional friendly name
            $table->string('image_path')->nullable(); // Path to weapon image
            $table->string('weapon_type')->nullable(); // rifle, pistol, explosive, etc.
            $table->string('category')->nullable(); // Primary, Secondary, Equipment
            $table->timestamps();

            $table->index('name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weapons');
    }
};
