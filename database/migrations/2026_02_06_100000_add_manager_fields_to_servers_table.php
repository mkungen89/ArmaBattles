<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('servers', function (Blueprint $table) {
            $table->string('manager_url')->nullable()->after('last_updated_at');
            $table->string('manager_key')->nullable()->after('manager_url');
            $table->boolean('is_managed')->default(false)->after('manager_key');
        });
    }

    public function down(): void
    {
        Schema::table('servers', function (Blueprint $table) {
            $table->dropColumn(['manager_url', 'manager_key', 'is_managed']);
        });
    }
};
