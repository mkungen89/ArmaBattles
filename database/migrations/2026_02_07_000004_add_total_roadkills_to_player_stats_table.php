<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('player_stats', function (Blueprint $table) {
            if (!Schema::hasColumn('player_stats', 'total_roadkills')) {
                $table->unsignedInteger('total_roadkills')->default(0)->after('team_kills');
            }
        });
    }

    public function down(): void
    {
        Schema::table('player_stats', function (Blueprint $table) {
            if (Schema::hasColumn('player_stats', 'total_roadkills')) {
                $table->dropColumn('total_roadkills');
            }
        });
    }
};
