<?php

namespace App\Notifications;

use App\Models\Team;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ApplicationResultNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected Team $team,
        protected string $result,
        protected ?string $reason = null
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $message = $this->result === 'accepted'
            ? "Your application to {$this->team->name} has been accepted! Welcome to the platoon."
            : "Your application to {$this->team->name} has been declined.".($this->reason ? " Reason: {$this->reason}" : '');

        return [
            'type' => 'application_result',
            'category' => 'team',
            'team_id' => $this->team->id,
            'team_name' => $this->team->name,
            'result' => $this->result,
            'reason' => $this->reason,
            'message' => $message,
            'url' => $this->result === 'accepted' ? route('teams.show', $this->team) : route('teams.index'),
        ];
    }
}
