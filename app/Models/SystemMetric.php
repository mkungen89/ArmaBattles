<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemMetric extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'cache_hits',
        'cache_misses',
        'jobs_processed',
        'jobs_failed',
        'queue_size',
        'memory_usage_mb',
        'cpu_load_1m',
        'disk_usage_percent',
        'api_requests_count',
        'api_p50_ms',
        'api_p95_ms',
        'api_p99_ms',
        'recorded_at',
    ];

    protected function casts(): array
    {
        return [
            'recorded_at' => 'datetime',
            'memory_usage_mb' => 'decimal:2',
            'cpu_load_1m' => 'decimal:2',
            'disk_usage_percent' => 'decimal:2',
        ];
    }
}
