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
        Schema::table('tournaments', function (Blueprint $table) {
            if (!Schema::hasColumn('tournaments', 'auto_start_enabled')) {
                $table->boolean('auto_start_enabled')->default(false)->after('status');
            }
            if (!Schema::hasColumn('tournaments', 'auto_start_threshold')) {
                $table->integer('auto_start_threshold')->nullable()->after('auto_start_enabled');
            }
            if (!Schema::hasColumn('tournaments', 'auto_started_at')) {
                $table->timestamp('auto_started_at')->nullable()->after('auto_start_threshold');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tournaments', function (Blueprint $table) {
            $columns = ['auto_start_enabled', 'auto_start_threshold', 'auto_started_at'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('tournaments', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
