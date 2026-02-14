<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;

class PushNotificationService
{
    protected WebPush $webPush;

    public function __construct()
    {
        $auth = [
            'VAPID' => [
                'subject' => config('services.vapid.subject', 'mailto:admin@armabattles.com'),
                'publicKey' => config('services.vapid.public_key'),
                'privateKey' => config('services.vapid.private_key'),
            ],
        ];

        $this->webPush = new WebPush($auth);
    }

    /**
     * Send push notification to a user
     */
    public function sendToUser(User $user, string $title, string $body, ?array $data = null): bool
    {
        try {
            // Check if user has push notifications enabled
            $preferences = $user->notification_preferences ?? [];
            if (!($preferences['push_enabled'] ?? false)) {
                return false;
            }

            // Get subscription
            $subscriptionData = $preferences['push_subscription'] ?? null;
            if (!$subscriptionData) {
                return false;
            }

            // Create subscription object
            $subscription = Subscription::create([
                'endpoint' => $subscriptionData['endpoint'],
                'keys' => [
                    'p256dh' => $subscriptionData['keys']['p256dh'] ?? '',
                    'auth' => $subscriptionData['keys']['auth'] ?? '',
                ],
            ]);

            // Prepare notification payload
            $payload = json_encode([
                'title' => $title,
                'body' => $body,
                'icon' => '/images/icons/icon-192x192.png',
                'badge' => '/images/icons/icon-96x96.png',
                'data' => array_merge($data ?? [], [
                    'timestamp' => now()->timestamp,
                    'url' => url('/'),
                ]),
            ]);

            // Send notification
            $report = $this->webPush->sendOneNotification($subscription, $payload);

            // Check if successful
            if ($report->isSuccess()) {
                Log::info("Push notification sent to user {$user->id}");
                return true;
            }

            // Handle errors
            if ($report->isSubscriptionExpired()) {
                Log::warning("Push subscription expired for user {$user->id}");
                $this->removeExpiredSubscription($user);
                return false;
            }

            Log::error("Push notification failed for user {$user->id}: " . $report->getReason());
            return false;
        } catch (\Exception $e) {
            Log::error("Push notification error for user {$user->id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send push notification to multiple users
     */
    public function sendToUsers(array $users, string $title, string $body, ?array $data = null): int
    {
        $sent = 0;

        foreach ($users as $user) {
            if ($this->sendToUser($user, $title, $body, $data)) {
                $sent++;
            }
        }

        return $sent;
    }

    /**
     * Remove expired subscription from user preferences
     */
    protected function removeExpiredSubscription(User $user): void
    {
        $preferences = $user->notification_preferences ?? [];
        unset($preferences['push_subscription']);
        $preferences['push_enabled'] = false;

        $user->update([
            'notification_preferences' => $preferences,
        ]);
    }

    /**
     * Send achievement unlocked notification
     */
    public function sendAchievementUnlocked(User $user, string $achievementName): bool
    {
        return $this->sendToUser(
            $user,
            '🏆 Achievement Unlocked!',
            "You earned: {$achievementName}",
            [
                'type' => 'achievement',
                'url' => route('achievements.index'),
            ]
        );
    }

    /**
     * Send level up notification
     */
    public function sendLevelUp(User $user, int $newLevel): bool
    {
        return $this->sendToUser(
            $user,
            '⬆️ Level Up!',
            "Congratulations! You reached level {$newLevel}",
            [
                'type' => 'level_up',
                'level' => $newLevel,
                'url' => route('profile.show'),
            ]
        );
    }

    /**
     * Send match reminder notification
     */
    public function sendMatchReminder(User $user, string $matchInfo, string $timeUntil): bool
    {
        return $this->sendToUser(
            $user,
            '⏰ Match Reminder',
            "Your match starts in {$timeUntil}: {$matchInfo}",
            [
                'type' => 'match_reminder',
                'url' => route('tournaments.index'),
            ]
        );
    }

    /**
     * Send team invitation notification
     */
    public function sendTeamInvitation(User $user, string $teamName): bool
    {
        return $this->sendToUser(
            $user,
            '👥 Team Invitation',
            "You've been invited to join {$teamName}",
            [
                'type' => 'team_invitation',
                'url' => route('teams.index'),
            ]
        );
    }

    /**
     * Send tournament starting notification
     */
    public function sendTournamentStarting(User $user, string $tournamentName): bool
    {
        return $this->sendToUser(
            $user,
            '🎮 Tournament Starting',
            "{$tournamentName} is about to begin!",
            [
                'type' => 'tournament_starting',
                'url' => route('tournaments.index'),
            ]
        );
    }
}
