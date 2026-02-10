<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('news_articles', function (Blueprint $table) {
            $table->string('source')->default('community')->after('slug');
            $table->string('external_url')->nullable()->after('featured_image');
            $table->string('external_slug')->nullable()->unique()->after('external_url');
            $table->string('category')->nullable()->after('external_slug');
        });

        // Make author_id nullable (official articles have no local author)
        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            Schema::table('news_articles', function (Blueprint $table) {
                $table->dropForeign(['author_id']);
            });
            DB::statement('ALTER TABLE news_articles ALTER COLUMN author_id DROP NOT NULL');
            Schema::table('news_articles', function (Blueprint $table) {
                $table->foreign('author_id')->references('id')->on('users')->cascadeOnDelete();
            });
        } else {
            // SQLite: recreate with nullable
            Schema::table('news_articles', function (Blueprint $table) {
                $table->unsignedBigInteger('author_id')->nullable()->change();
            });
        }

        // Make content nullable (official articles may only have excerpt)
        Schema::table('news_articles', function (Blueprint $table) {
            $table->text('content')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('news_articles', function (Blueprint $table) {
            $table->dropUnique(['external_slug']);
            $table->dropColumn(['source', 'external_url', 'external_slug', 'category']);
        });
    }
};
