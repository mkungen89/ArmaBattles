<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

class BaseEventOccurred implements ShouldBroadcastNow
{
    use Dispatchable;

    public function __construct(
        public int $serverId,
        public string $eventType,
        public ?string $baseName,
        public ?string $playerName,
        public ?string $capturingFaction,
    ) {}

    public function broadcastOn(): Channel
    {
        return new Channel("server.{$this->serverId}");
    }

    public function broadcastAs(): string
    {
        return 'base.event';
    }

    public function broadcastWith(): array
    {
        return [
            'event_type' => $this->eventType,
            'base_name' => $this->baseName,
            'player_name' => $this->playerName,
            'capturing_faction' => $this->capturingFaction,
            'timestamp' => now()->toIso8601String(),
        ];
    }
}
