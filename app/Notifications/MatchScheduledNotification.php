<?php

namespace App\Notifications;

use App\Models\TournamentMatch;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class MatchScheduledNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected TournamentMatch $match
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'match_scheduled',
            'category' => 'match',
            'match_id' => $this->match->id,
            'tournament_id' => $this->match->tournament_id,
            'tournament_name' => $this->match->tournament->name,
            'team1_name' => $this->match->team1?->name,
            'team2_name' => $this->match->team2?->name,
            'scheduled_at' => $this->match->scheduled_at?->toISOString(),
            'message' => "Match scheduled: {$this->match->team1?->name} vs {$this->match->team2?->name}",
            'url' => route('tournaments.match', $this->match),
        ];
    }
}
