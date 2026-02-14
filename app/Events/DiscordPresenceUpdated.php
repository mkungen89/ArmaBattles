<?php

namespace App\Events;

use App\Models\DiscordRichPresence;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DiscordPresenceUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $userId,
        public ?string $activityStatus,
        public ?string $activityState,
        public ?string $startedAt,
        public bool $enabled
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('App.Models.User.' . $this->userId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'presence.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'activity_status' => $this->activityStatus,
            'activity_state' => $this->activityState,
            'started_at' => $this->startedAt,
            'enabled' => $this->enabled,
        ];
    }
}
