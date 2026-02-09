<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('supply_deliveries', function (Blueprint $table) {
            if (!Schema::hasColumn('supply_deliveries', 'player_id')) {
                $table->integer('player_id')->nullable()->after('player_uuid');
            }
            if (!Schema::hasColumn('supply_deliveries', 'player_faction')) {
                $table->string('player_faction')->nullable()->after('player_id');
            }
            if (!Schema::hasColumn('supply_deliveries', 'position')) {
                $table->json('position')->nullable()->after('player_faction');
            }
            if (!Schema::hasColumn('supply_deliveries', 'estimated_amount')) {
                $table->integer('estimated_amount')->nullable()->after('position');
            }
            if (!Schema::hasColumn('supply_deliveries', 'xp_awarded')) {
                $table->integer('xp_awarded')->nullable()->after('estimated_amount');
            }
            if (!Schema::hasColumn('supply_deliveries', 'occurred_at')) {
                $table->timestamp('occurred_at')->nullable()->after('xp_awarded');
            }
        });

        // Add index if not exists
        if (!Schema::hasIndex('supply_deliveries', 'supply_deliveries_player_uuid_index')) {
            Schema::table('supply_deliveries', function (Blueprint $table) {
                $table->index('player_uuid');
            });
        }
    }

    public function down(): void
    {
        Schema::table('supply_deliveries', function (Blueprint $table) {
            $columns = ['player_id', 'player_faction', 'position', 'estimated_amount', 'xp_awarded', 'occurred_at'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('supply_deliveries', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
