<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->string('header_image')->nullable()->after('logo_url');
            $table->string('avatar_path')->nullable()->after('header_image');
            $table->json('social_links')->nullable()->after('avatar_path');
            $table->string('website')->nullable()->after('social_links');
        });
    }

    public function down(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->dropColumn(['header_image', 'avatar_path', 'social_links', 'website']);
        });
    }
};
