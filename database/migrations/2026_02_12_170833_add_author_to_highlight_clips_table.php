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
        Schema::table('highlight_clips', function (Blueprint $table) {
            $table->string('author')->nullable()->after('platform')->comment('Channel/Creator name from the video platform');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('highlight_clips', function (Blueprint $table) {
            $table->dropColumn('author');
        });
    }
};
