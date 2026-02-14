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
            if (!Schema::hasColumn('users', 'looking_for_team')) {
                $table->boolean('looking_for_team')->default(false)->after('profile_visibility');
            }
            if (!Schema::hasColumn('users', 'preferred_roles')) {
                $table->json('preferred_roles')->nullable()->after('looking_for_team'); // Array of role IDs
            }
            if (!Schema::hasColumn('users', 'playstyle')) {
                $table->string('playstyle')->nullable()->after('preferred_roles'); // casual, competitive, milsim
            }
            if (!Schema::hasColumn('users', 'region')) {
                $table->string('region')->nullable()->after('playstyle'); // NA, EU, APAC
            }
            if (!Schema::hasColumn('users', 'availability')) {
                $table->string('availability')->nullable()->after('region'); // weekdays, weekends, both
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columns = ['looking_for_team', 'preferred_roles', 'playstyle', 'region', 'availability'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
