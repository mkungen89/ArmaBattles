<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_metrics', function (Blueprint $table) {
            $table->id();
            $table->integer('cache_hits')->default(0);
            $table->integer('cache_misses')->default(0);
            $table->integer('jobs_processed')->default(0);
            $table->integer('jobs_failed')->default(0);
            $table->integer('queue_size')->default(0);
            $table->decimal('memory_usage_mb', 8, 2)->default(0);
            $table->decimal('cpu_load_1m', 5, 2)->default(0);
            $table->decimal('disk_usage_percent', 5, 2)->default(0);
            $table->integer('api_requests_count')->default(0);
            $table->smallInteger('api_p50_ms')->default(0);
            $table->smallInteger('api_p95_ms')->default(0);
            $table->smallInteger('api_p99_ms')->default(0);
            $table->timestamp('recorded_at');

            $table->index('recorded_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_metrics');
    }
};
