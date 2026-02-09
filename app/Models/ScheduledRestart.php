<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScheduledRestart extends Model
{
    protected $fillable = [
        'server_id',
        'is_enabled',
        'schedule_type',
        'restart_time',
        'days_of_week',
        'cron_expression',
        'warning_minutes',
        'warning_message',
        'last_executed_at',
        'next_execution_at',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'days_of_week' => 'array',
        'warning_minutes' => 'integer',
        'last_executed_at' => 'datetime',
        'next_execution_at' => 'datetime',
    ];

    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }

    public function isDue(): bool
    {
        return $this->is_enabled
            && $this->next_execution_at
            && $this->next_execution_at->lte(now());
    }

    public function calculateNextExecution(): void
    {
        $next = match ($this->schedule_type) {
            'daily' => $this->calculateNextDaily(),
            'weekly' => $this->calculateNextWeekly(),
            'custom' => $this->calculateNextFromCron(),
            default => null,
        };

        $this->update(['next_execution_at' => $next]);
    }

    protected function calculateNextDaily(): ?Carbon
    {
        if (!$this->restart_time) {
            return null;
        }

        $next = Carbon::today()->setTimeFromTimeString($this->restart_time);
        if ($next->lte(now())) {
            $next->addDay();
        }

        return $next;
    }

    protected function calculateNextWeekly(): ?Carbon
    {
        if (!$this->restart_time || empty($this->days_of_week)) {
            return null;
        }

        $days = collect($this->days_of_week)->sort()->values();
        $now = now();
        $todayDow = $now->dayOfWeekIso; // 1=Monday, 7=Sunday

        // Check remaining days this week (including today)
        foreach ($days as $day) {
            if ($day > $todayDow) {
                return Carbon::today()->next((int) $day)->setTimeFromTimeString($this->restart_time);
            }
            if ($day == $todayDow) {
                $candidate = Carbon::today()->setTimeFromTimeString($this->restart_time);
                if ($candidate->gt($now)) {
                    return $candidate;
                }
            }
        }

        // Wrap to first day next week
        $firstDay = $days->first();
        return Carbon::today()->next((int) $firstDay)->setTimeFromTimeString($this->restart_time);
    }

    protected function calculateNextFromCron(): ?Carbon
    {
        if (!$this->cron_expression) {
            return null;
        }

        try {
            $cron = new \Cron\CronExpression($this->cron_expression);
            return Carbon::instance($cron->getNextRunDate());
        } catch (\Exception) {
            return null;
        }
    }
}
