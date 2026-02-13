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
            $table->integer('duration_seconds')->nullable()->after('thumbnail_url');
            $table->index('duration_seconds');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('highlight_clips', function (Blueprint $table) {
            $table->dropIndex(['duration_seconds']);
            $table->dropColumn('duration_seconds');
        });
    }
};
