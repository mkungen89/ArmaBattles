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

        NewNotification::dispatch(
            $user->getKey(),
            $data['message'] ?? $data['title'] ?? 'New notification',
            $data['category'] ?? 'general',
            $data['action_url'] ?? $data['url'] ?? null,
        );
    }
}
