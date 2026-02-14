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
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'banned_until')) {
                $table->timestamp('banned_until')->nullable()->after('banned_at');
            }
            if (!Schema::hasColumn('users', 'hardware_id')) {
                $table->string('hardware_id')->nullable()->after('banned_at');
            }
            if (!Schema::hasColumn('users', 'ban_count')) {
                $table->integer('ban_count')->default(0)->after('banned_at');
            }
            if (!Schema::hasColumn('users', 'ip_address')) {
                $table->string('ip_address')->nullable()->after('banned_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columns = ['banned_until', 'hardware_id', 'ban_count', 'ip_address'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
