<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Change role column from enum to string to support new roles
        // Works on PostgreSQL, MySQL, and SQLite
        Schema::table('users', function (Blueprint $table) {
            // For PostgreSQL/MySQL: change to string
            // For SQLite: this is a no-op if already string
            $table->string('role', 50)->default('member')->change();
        });

        // Update any users with 'user' role to 'member' for consistency
        DB::table('users')->where('role', 'user')->update(['role' => 'member']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert any new roles to member
        DB::table('users')->whereIn('role', ['gm', 'referee', 'observer', 'caster'])
            ->update(['role' => 'member']);

        // Note: Cannot revert string back to enum without data loss risk
        // Leave as string type
    }
};
