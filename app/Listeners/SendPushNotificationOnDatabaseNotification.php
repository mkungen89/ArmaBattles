<?php

namespace App\Listeners;

use App\Services\PushNotificationService;
use Illuminate\Notifications\Events\NotificationSent;
use Illuminate\Support\Facades\Log;

class SendPushNotificationOnDatabaseNotification
{
    protected PushNotificationService $pushService;

    public function __construct(PushNotificationService $pushService)
    {
        $this->pushService = $pushService;
    }

    /**
     * Handle the event.
     */
    public function handle(NotificationSent $event): void
    {
        // Only process database notifications
        if ($event->channel !== 'database') {
            return;
        }

        // Only process User notifiables
        if (! $event->notifiable instanceof \App\Models\User) {
            return;
        }

        $user = $event->notifiable;
        $notificationData = $event->response;

        // Check if user has push notifications enabled
        $preferences = $user->notification_preferences ?? [];
        if (!($preferences['push_enabled'] ?? false)) {
            return;
        }

        // Extract notification details
        $type = $notificationData['type'] ?? 'general';
        $message = $notificationData['message'] ?? 'You have a new notification';
        $url = $notificationData['url'] ?? route('notifications.index');

        // Map notification types to titles
        $title = $this->getTitleForType($type);

        // Send push notification
        try {
            $this->pushService->sendToUser(
                $user,
                $title,
                $message,
                [
                    'type' => $type,
                    'url' => $url,
                    'notification_id' => $event->notification->id ?? null,
                ]
            );
        } catch (\Exception $e) {
            Log::error("Failed to send push notification: " . $e->getMessage());
        }
    }

    /**
     * Get notification title based on type
     */
    protected function getTitleForType(string $type): string
    {
        return match ($type) {
            'achievement_earned' => '🏆 Achievement Unlocked!',
            'level_up' => '⬆️ Level Up!',
            'match_reminder' => '⏰ Match Reminder',
            'match_scheduled' => '📅 Match Scheduled',
            'team_invitation' => '👥 Team Invitation',
            'team_application' => '📝 Team Application',
            'application_result' => '✅ Application Result',
            'video_submitted' => '🎥 Video Submitted',
            'video_approved' => '✅ Video Approved',
            'video_rejected' => '❌ Video Rejected',
            'rank_up' => '🎖️ Rank Up!',
            default => '🔔 Notification',
        };
    }
}
