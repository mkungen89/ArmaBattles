<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LinkArmaIdNotification extends Notification
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'link_arma_id',
            'category' => 'general',
            'message' => 'Link your Arma Reforger ID in Profile Settings to track your kills, stats, and more!',
            'url' => route('profile.settings'),
        ];
    }
}
