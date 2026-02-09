<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('players', function (Blueprint $table) {
            // Add new columns for extended stats
            $table->integer('kills')->default(0)->after('total_playtime');
            $table->integer('deaths')->default(0)->after('kills');
            $table->integer('xp')->default(0)->after('deaths');
            $table->decimal('distance_traveled', 12, 2)->default(0)->after('xp');
            $table->integer('score')->default(0)->after('distance_traveled');
            $table->integer('sessions')->default(0)->after('score');
            $table->integer('server_id')->nullable()->after('sessions');
        });
    }

    public function down(): void
    {
        Schema::table('players', function (Blueprint $table) {
            $table->dropColumn(['kills', 'deaths', 'xp', 'distance_traveled', 'score', 'sessions', 'server_id']);
        });
    }
};
