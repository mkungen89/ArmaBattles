<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

class KillFeedUpdated implements ShouldBroadcastNow
{
    use Dispatchable;

    public function __construct(
        public int $serverId,
        public string $killerName,
        public ?string $victimName,
        public ?string $weaponName,
        public float $distance,
        public bool $isHeadshot,
        public bool $isTeamKill,
        public bool $isRoadkill,
        public string $victimType,
        public int $killId,
    ) {}

    public function broadcastOn(): Channel
    {
        return new Channel("server.{$this->serverId}");
    }

    public function broadcastAs(): string
    {
        return 'kill.new';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->killId,
            'killer_name' => $this->killerName,
            'victim_name' => $this->victimName,
            'weapon_name' => $this->weaponName,
            'distance' => $this->distance,
            'is_headshot' => $this->isHeadshot,
            'is_team_kill' => $this->isTeamKill,
            'is_roadkill' => $this->isRoadkill,
            'victim_type' => $this->victimType,
            'timestamp' => now()->toIso8601String(),
        ];
    }
}
