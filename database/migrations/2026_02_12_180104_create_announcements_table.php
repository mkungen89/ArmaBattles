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
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable()->comment('Admin reference title');
            $table->text('message')->comment('Announcement message to display');
            $table->enum('type', ['info', 'warning', 'success', 'danger'])->default('info');
            $table->boolean('is_active')->default(true);
            $table->timestamp('starts_at')->nullable()->comment('When to start showing the announcement');
            $table->timestamp('ends_at')->nullable()->comment('When to stop showing the announcement');
            $table->timestamps();

            $table->index(['is_active', 'starts_at', 'ends_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};
