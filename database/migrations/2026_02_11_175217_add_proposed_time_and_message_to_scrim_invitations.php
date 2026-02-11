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
            if (! Schema::hasColumn('scrim_invitations', 'proposed_time')) {
                $table->timestamp('proposed_time')->nullable()->after('invited_team_id');
            }
            if (! Schema::hasColumn('scrim_invitations', 'message')) {
                $table->text('message')->nullable()->after('proposed_time');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('scrim_invitations', function (Blueprint $table) {
            if (Schema::hasColumn('scrim_invitations', 'proposed_time')) {
                $table->dropColumn('proposed_time');
            }
            if (Schema::hasColumn('scrim_invitations', 'message')) {
                $table->dropColumn('message');
            }
        });
    }
};
