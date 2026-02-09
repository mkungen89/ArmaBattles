<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('player_distance', function (Blueprint $table) {
            if (!Schema::hasColumn('player_distance', 'occurred_at')) {
                $table->timestamp('occurred_at')->nullable()->after('is_final_log');
            }
        });

        // Copy data from event_time to occurred_at if event_time exists
        if (Schema::hasColumn('player_distance', 'event_time')) {
            DB::table('player_distance')->whereNull('occurred_at')->update([
                'occurred_at' => DB::raw('event_time')
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('player_distance', function (Blueprint $table) {
            $table->dropColumn('occurred_at');
        });
    }
};
