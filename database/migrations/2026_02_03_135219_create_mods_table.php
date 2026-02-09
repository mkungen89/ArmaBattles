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
        Schema::create('mods', function (Blueprint $table) {
            $table->id();
            $table->string('workshop_id')->unique();
            $table->string('name');
            $table->string('author')->nullable();
            $table->string('author_url')->nullable();
            $table->string('version')->nullable();
            $table->text('description')->nullable();
            $table->string('workshop_url')->nullable();
            $table->string('thumbnail_url')->nullable();
            $table->bigInteger('file_size')->nullable();
            $table->integer('subscriptions')->default(0);
            $table->timestamp('workshop_updated_at')->nullable();
            $table->timestamps();

            $table->index('workshop_id');
            $table->index('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mods');
    }
};
