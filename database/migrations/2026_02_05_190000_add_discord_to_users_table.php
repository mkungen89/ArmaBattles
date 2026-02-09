<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'discord_id')) {
                $table->string('discord_id')->nullable()->after('player_uuid');
            }
            if (!Schema::hasColumn('users', 'discord_username')) {
                $table->string('discord_username')->nullable()->after('discord_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'discord_id')) {
                $table->dropColumn('discord_id');
            }
            if (Schema::hasColumn('users', 'discord_username')) {
                $table->dropColumn('discord_username');
            }
        });
    }
};
