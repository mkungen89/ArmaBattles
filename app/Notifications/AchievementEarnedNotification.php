<?php

namespace App\Notifications;

use App\Models\Achievement;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AchievementEarnedNotification extends Notification
{
    use Queueable;

    public function __construct(protected Achievement $achievement) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'achievement_earned',
            'message' => "You earned the \"{$this->achievement->name}\" achievement!",
            'achievement_id' => $this->achievement->id,
            'achievement_name' => $this->achievement->name,
            'achievement_icon' => $this->achievement->icon,
            'achievement_color' => $this->achievement->color,
        ];
    }
}
