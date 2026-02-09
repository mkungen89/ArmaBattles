<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

class ActivityFeedUpdated implements ShouldBroadcastNow
{
    use Dispatchable;

    public function __construct(
        public string $type,
        public array $data,
    ) {}

    public function broadcastOn(): Channel
    {
        return new Channel('server.global');
    }

    public function broadcastAs(): string
    {
        return 'activity.new';
    }

    public function broadcastWith(): array
    {
        return [
            'type' => $this->type,
            'data' => $this->data,
            'timestamp' => now()->toIso8601String(),
        ];
    }
}
