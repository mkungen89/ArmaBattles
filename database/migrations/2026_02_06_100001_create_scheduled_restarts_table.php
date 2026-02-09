<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scheduled_restarts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('server_id')->constrained('servers')->onDelete('cascade');
            $table->boolean('is_enabled')->default(true);
            $table->string('schedule_type', 20); // daily, weekly, custom
            $table->time('restart_time')->nullable();
            $table->json('days_of_week')->nullable();
            $table->string('cron_expression')->nullable();
            $table->integer('warning_minutes')->default(5);
            $table->string('warning_message')->nullable();
            $table->timestamp('last_executed_at')->nullable();
            $table->timestamp('next_execution_at')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scheduled_restarts');
    }
};
