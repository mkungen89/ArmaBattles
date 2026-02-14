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
        Schema::table('chat_events', function (Blueprint $table) {
            if (!Schema::hasColumn('chat_events', 'is_flagged')) {
                $table->boolean('is_flagged')->default(false);
            }
            if (!Schema::hasColumn('chat_events', 'flagged_by')) {
                $table->foreignId('flagged_by')->nullable()->constrained('users')->onDelete('set null');
            }
            if (!Schema::hasColumn('chat_events', 'flag_reason')) {
                $table->text('flag_reason')->nullable();
            }
            if (!Schema::hasColumn('chat_events', 'reviewed_at')) {
                $table->timestamp('reviewed_at')->nullable();
            }
            if (!Schema::hasColumn('chat_events', 'reviewed_by')) {
                $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chat_events', function (Blueprint $table) {
            $columns = ['is_flagged', 'flagged_by', 'flag_reason', 'reviewed_at', 'reviewed_by'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('chat_events', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
