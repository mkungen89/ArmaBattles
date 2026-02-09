<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('player_reports', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('server_id');
            $table->string('reporter_name');
            $table->string('reporter_uuid')->nullable();
            $table->unsignedInteger('reporter_id')->nullable();
            $table->string('target_name');
            $table->text('reason')->nullable();
            $table->string('channel')->nullable();
            $table->enum('status', ['pending', 'reviewed', 'resolved', 'dismissed'])->default('pending');
            $table->text('admin_notes')->nullable();
            $table->unsignedBigInteger('handled_by')->nullable();
            $table->timestamp('handled_at')->nullable();
            $table->timestamp('reported_at');
            $table->timestamps();

            $table->index('status');
            $table->index('target_name');
            $table->index('reporter_uuid');
            $table->index('reported_at');

            $table->foreign('handled_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('player_reports');
    }
};
