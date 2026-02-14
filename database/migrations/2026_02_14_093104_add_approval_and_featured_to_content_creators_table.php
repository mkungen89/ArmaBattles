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
        Schema::table('content_creators', function (Blueprint $table) {
            if (!Schema::hasColumn('content_creators', 'is_approved')) {
                $table->boolean('is_approved')->default(true)->after('is_verified');
            }
            if (!Schema::hasColumn('content_creators', 'is_featured')) {
                $table->boolean('is_featured')->default(false)->after('is_approved');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('content_creators', function (Blueprint $table) {
            if (Schema::hasColumn('content_creators', 'is_approved')) {
                $table->dropColumn('is_approved');
            }
            if (Schema::hasColumn('content_creators', 'is_featured')) {
                $table->dropColumn('is_featured');
            }
        });
    }
};
