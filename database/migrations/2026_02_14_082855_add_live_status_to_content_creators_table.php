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
        Schema::table('content_creators', function (Blueprint $table) {
            if (!Schema::hasColumn('content_creators', 'live_platform')) {
                $table->string('live_platform')->nullable()->after('is_live');
            }
            if (!Schema::hasColumn('content_creators', 'live_title')) {
                $table->string('live_title')->nullable()->after('live_platform');
            }
            if (!Schema::hasColumn('content_creators', 'live_viewers')) {
                $table->integer('live_viewers')->nullable()->after('live_title');
            }
            if (!Schema::hasColumn('content_creators', 'live_started_at')) {
                $table->timestamp('live_started_at')->nullable()->after('live_viewers');
                $table->index('live_started_at');
            }
            if (!Schema::hasColumn('content_creators', 'live_checked_at')) {
                $table->timestamp('live_checked_at')->nullable()->after('live_started_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('content_creators', function (Blueprint $table) {
            if (Schema::hasColumn('content_creators', 'live_started_at')) {
                $table->dropIndex(['live_started_at']);
            }

            $columns = ['live_platform', 'live_title', 'live_viewers', 'live_started_at', 'live_checked_at'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('content_creators', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
