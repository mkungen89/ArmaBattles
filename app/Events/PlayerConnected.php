<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

class PlayerConnected implements ShouldBroadcastNow
{
    use Dispatchable;

    public function __construct(
        public int $serverId,
        public string $eventType,
        public string $playerName,
        public ?string $playerUuid,
        public ?string $playerFaction,
    ) {}

    public function broadcastOn(): Channel
    {
        return new Channel("server.{$this->serverId}");
    }

    public function broadcastAs(): string
    {
        return 'player.connected';
    }

    public function broadcastWith(): array
    {
        return [
            'event_type' => $this->eventType,
            'player_name' => $this->playerName,
            'player_uuid' => $this->playerUuid,
            'player_faction' => $this->playerFaction,
            'timestamp' => now()->toIso8601String(),
        ];
    }
}
