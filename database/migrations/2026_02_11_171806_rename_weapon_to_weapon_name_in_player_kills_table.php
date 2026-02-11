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
        Schema::table('player_kills', function (Blueprint $table) {
            // Check if 'weapon' column exists and rename to 'weapon_name'
            if (Schema::hasColumn('player_kills', 'weapon')) {
                $table->renameColumn('weapon', 'weapon_name');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('player_kills', function (Blueprint $table) {
            // Rename back from 'weapon_name' to 'weapon'
            if (Schema::hasColumn('player_kills', 'weapon_name')) {
                $table->renameColumn('weapon_name', 'weapon');
            }
        });
    }
};
