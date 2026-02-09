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
            $table->string('steam_id')->unique()->nullable()->after('id');
            $table->string('avatar')->nullable()->after('email');
            $table->string('avatar_full')->nullable()->after('avatar');
            $table->string('profile_url')->nullable()->after('avatar_full');
            $table->enum('role', ['member', 'moderator', 'admin'])->default('member')->after('profile_url');
            $table->boolean('is_banned')->default(false)->after('role');
            $table->text('ban_reason')->nullable()->after('is_banned');
            $table->timestamp('banned_at')->nullable()->after('ban_reason');
            $table->string('password')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'steam_id',
                'avatar',
                'avatar_full',
                'profile_url',
                'role',
                'is_banned',
                'ban_reason',
                'banned_at',
            ]);
        });
    }
};
