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
        Schema::table('scrim_invitations', function (Blueprint $table) {
            if (Schema::hasColumn('scrim_invitations', 'scrim_match_id')) {
                $table->foreignId('scrim_match_id')->nullable()->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('scrim_invitations', function (Blueprint $table) {
            if (Schema::hasColumn('scrim_invitations', 'scrim_match_id')) {
                $table->foreignId('scrim_match_id')->nullable(false)->change();
            }
        });
    }
};
