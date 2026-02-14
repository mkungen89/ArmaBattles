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
            $table->string('twitch_id')->nullable()->unique()->after('google_email');
            $table->string('twitch_username')->nullable()->after('twitch_id');
            $table->string('twitch_email')->nullable()->after('twitch_username');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['twitch_id', 'twitch_username', 'twitch_email']);
        });
    }
};
