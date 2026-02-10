<?php

namespace App\Listeners;

use App\Events\NewNotification;
use Illuminate\Notifications\Events\NotificationSent;

class BroadcastNotificationCreated
{
    public function handle(NotificationSent $event): void
    {
        if ($event->channel !== 'database') {
            return;
        }

        $user = $event->notifiable;

        if (! method_exists($user, 'getKey')) {
            return;
        }

        $notification = $event->notification;
        $data = method_exists($notification, 'toArray')
            ? $notification->toArray($user)
            : [];

        $metadata = [];
        if (($data['type'] ?? '') === 'achievement_earned') {
            $metadata = [
                'achievement_name' => $data['achievement_name'] ?? null,
                'achievement_icon' => $data['achievement_icon'] ?? null,
                'achievement_color' => $data['achievement_color'] ?? null,
            ];
        }

        NewNotification::dispatch(
            $user->getKey(),
            $data['message'] ?? $data['title'] ?? 'New notification',
            $data['category'] ?? 'general',
            $data['action_url'] ?? $data['url'] ?? null,
            $metadata,
        );
    }
}
