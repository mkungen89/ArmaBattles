<?php

namespace App\Notifications;

use App\Models\RankLogo;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class RankUpNotification extends Notification
{
    use Queueable;

    public int $newRank;

    public RankLogo $rankInfo;

    /**
     * Create a new notification instance.
     */
    public function __construct(int $newRank, RankLogo $rankInfo)
    {
        $this->newRank = $newRank;
        $this->rankInfo = $rankInfo;
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
            'type' => 'rank_up',
            'rank' => $this->newRank,
            'rank_name' => $this->rankInfo->name,
            'era' => $this->rankInfo->era,
            'color' => $this->rankInfo->color,
            'logo_url' => $this->rankInfo->logo_url,
            'message' => "Congratulations! You've been promoted to {$this->rankInfo->name} (Rank {$this->newRank})!",
        ];
    }
}
