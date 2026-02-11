<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LevelUpNotification extends Notification
{
    use Queueable;

    public int $newLevel;

    public array $tier;

    /**
     * Create a new notification instance.
     */
    public function __construct(int $newLevel, array $tier)
    {
        $this->newLevel = $newLevel;
        $this->tier = $tier;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'level_up',
            'level' => $this->newLevel,
            'tier' => $this->tier['label'],
            'tier_color' => $this->tier['color'],
            'message' => "Congratulations! You've reached Level {$this->newLevel} - {$this->tier['label']}!",
        ];
    }
}
