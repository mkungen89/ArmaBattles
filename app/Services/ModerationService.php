<?php

namespace App\Services;

use App\Models\PlayerWarning;
use App\Models\User;
use Carbon\Carbon;

class ModerationService
{
    /**
     * Issue a warning to a player
     */
    public function warnPlayer(
        User $user,
        User $moderator,
        string $type,
        string $reason,
        string $severity = 'low',
        ?string $evidence = null,
        ?Carbon $expiresAt = null
    ): PlayerWarning {
        $warning = PlayerWarning::create([
            'user_id' => $user->id,
            'moderator_id' => $moderator->id,
            'warning_type' => $type,
            'reason' => $reason,
            'evidence' => $evidence,
            'severity' => $severity,
            'expires_at' => $expiresAt,
        ]);

        // Check if user should be auto-banned (3 active warnings)
        $activeWarnings = $user->warnings()->active()->count();

        if ($activeWarnings >= 3) {
            $user->ban("Automated ban: 3 active warnings.", now()->addDays(7), 'temporary', $moderator);
            $warning->update(['auto_ban_triggered' => true]);
        }

        return $warning;
    }

    /**
     * Get moderation queue (flagged chat, pending reports, etc.)
     */
    public function getModerationQueue(): array
    {
        return [
            'flagged_chat' => \DB::table('chat_events')
                ->where('is_flagged', true)
                ->whereNull('reviewed_at')
                ->count(),
            'pending_reports' => \DB::table('player_reports')
                ->where('status', 'pending')
                ->count(),
            'active_warnings' => PlayerWarning::active()->count(),
        ];
    }

    /**
     * Auto-flag toxic chat messages
     */
    public function autoFlagToxicChat(): int
    {
        $toxicWords = explode(',', site_setting('blocked_chat_words', ''));
        $flagged = 0;

        foreach ($toxicWords as $word) {
            $word = trim($word);
            if (empty($word)) continue;

            $count = \DB::table('chat_events')
                ->where('is_flagged', false)
                ->where('message', 'ilike', "%{$word}%")
                ->update([
                    'is_flagged' => true,
                    'flag_reason' => 'Auto-flagged: Contains blocked word',
                ]);

            $flagged += $count;
        }

        return $flagged;
    }
}
