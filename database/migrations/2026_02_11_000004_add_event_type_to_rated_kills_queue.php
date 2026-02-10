<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rated_kills_queue', function (Blueprint $table) {
            $table->string('event_type', 30)->default('kill')->after('kill_id');
            $table->string('player_uuid')->nullable()->after('event_type');
        });
    }

    public function down(): void
    {
        Schema::table('rated_kills_queue', function (Blueprint $table) {
            $table->dropColumn(['event_type', 'player_uuid']);
        });
    }
};
