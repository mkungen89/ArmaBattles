<?php

namespace App\Notifications;

use App\Models\Team;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TeamApplicationNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected Team $team,
        protected User $applicant
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'team_application',
            'category' => 'team',
            'team_id' => $this->team->id,
            'team_name' => $this->team->name,
            'applicant_id' => $this->applicant->id,
            'applicant_name' => $this->applicant->name,
            'message' => "{$this->applicant->name} has applied to join {$this->team->name}",
            'url' => route('teams.my'),
        ];
    }
}
