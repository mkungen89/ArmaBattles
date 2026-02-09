<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;

class ServerStatusUpdated implements ShouldBroadcast
{
    use Dispatchable;

    public function __construct(
        public int $serverId,
        public ?int $players = null,
        public ?int $maxPlayers = null,
        public ?string $map = null,
        public ?string $serverName = null,
    ) {}

    public function broadcastOn(): Channel
    {
        return new Channel("server.{$this->serverId}");
    }

    public function broadcastAs(): string
    {
        return 'status.updated';
    }

    public function broadcastWith(): array
    {
        return array_filter([
            'server_id' => $this->serverId,
            'players' => $this->players,
            'max_players' => $this->maxPlayers,
            'map' => $this->map,
            'server_name' => $this->serverName,
            'timestamp' => now()->toIso8601String(),
        ], fn ($v) => $v !== null);
    }
}
