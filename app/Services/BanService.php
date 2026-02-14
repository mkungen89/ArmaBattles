<?php

namespace App\Services;

use App\Models\User;
use App\Models\BanHistory;
use Carbon\Carbon;

class BanService
{
    /**
     * Ban a user permanently
     */
    public function banPermanently(User $user, ?string $reason = null, ?User $admin = null): void
    {
        $user->ban($reason, null, 'permanent', $admin);
    }

    /**
     * Ban a user temporarily
     */
    public function banTemporarily(User $user, Carbon $until, ?string $reason = null, ?User $admin = null): void
    {
        $user->ban($reason, $until, 'temporary', $admin);
    }

    /**
     * Ban a user by hardware ID
     */
    public function banByHardwareId(string $hardwareId, ?string $reason = null, ?User $admin = null): void
    {
        $user = User::where('hardware_id', $hardwareId)->first();

        if ($user) {
            $user->ban($reason, null, 'hardware', $admin);
        } else {
            // Create a ban history entry for unknown users with this hardware ID
            BanHistory::create([
                'user_id' => null,
                'action' => 'banned',
                'reason' => $reason,
                'ban_type' => 'hardware',
                'hardware_id' => $hardwareId,
                'actioned_by' => $admin?->id,
            ]);
        }
    }

    /**
     * Ban a user by IP address
     */
    public function banByIpAddress(string $ipAddress, ?string $reason = null, ?User $admin = null): void
    {
        $user = User::where('ip_address', $ipAddress)->first();

        if ($user) {
            $user->ban($reason, null, 'ip_range', $admin);
        } else {
            // Create a ban history entry for unknown users with this IP
            BanHistory::create([
                'user_id' => null,
                'action' => 'banned',
                'reason' => $reason,
                'ban_type' => 'ip_range',
                'ip_address' => $ipAddress,
                'actioned_by' => $admin?->id,
            ]);
        }
    }

    /**
     * Unban a user
     */
    public function unban(User $user, ?User $admin = null): void
    {
        $user->unban($admin);
    }

    /**
     * Process expired temporary bans
     */
    public function processExpiredBans(): int
    {
        $expiredBans = User::where('is_banned', true)
            ->whereNotNull('banned_until')
            ->where('banned_until', '<=', now())
            ->get();

        foreach ($expiredBans as $user) {
            $user->update([
                'is_banned' => false,
                'ban_reason' => null,
                'banned_at' => null,
                'banned_until' => null,
            ]);

            // Log to ban history
            BanHistory::create([
                'user_id' => $user->id,
                'action' => 'temp_ban_expired',
            ]);
        }

        return $expiredBans->count();
    }

    /**
     * Check if user should be auto-banned (3 strikes rule)
     */
    public function checkAutoBan(User $user): bool
    {
        if ($user->ban_count >= 3 && !$user->is_banned) {
            $this->banPermanently(
                $user,
                'Automated ban: 3 strikes rule - User has been banned 3 or more times previously.',
                null
            );
            return true;
        }

        return false;
    }

    /**
     * Check if hardware ID is banned
     */
    public function isHardwareIdBanned(string $hardwareId): bool
    {
        // Check if any user with this hardware ID is banned
        if (User::where('hardware_id', $hardwareId)->where('is_banned', true)->exists()) {
            return true;
        }

        // Check ban history for hardware ID bans
        return BanHistory::where('ban_type', 'hardware')
            ->where('hardware_id', $hardwareId)
            ->where('action', 'banned')
            ->exists();
    }

    /**
     * Check if IP address is banned
     */
    public function isIpAddressBanned(string $ipAddress): bool
    {
        // Check if any user with this IP is banned
        if (User::where('ip_address', $ipAddress)->where('is_banned', true)->exists()) {
            return true;
        }

        // Check ban history for IP bans
        return BanHistory::where('ban_type', 'ip_range')
            ->where('ip_address', $ipAddress)
            ->where('action', 'banned')
            ->exists();
    }

    /**
     * Get all banned hardware IDs
     */
    public function getBannedHardwareIds(): array
    {
        $userHardwareIds = User::where('is_banned', true)
            ->whereNotNull('hardware_id')
            ->pluck('hardware_id')
            ->toArray();

        $historyHardwareIds = BanHistory::where('ban_type', 'hardware')
            ->where('action', 'banned')
            ->whereNotNull('hardware_id')
            ->pluck('hardware_id')
            ->toArray();

        return array_unique(array_merge($userHardwareIds, $historyHardwareIds));
    }

    /**
     * Get all banned IP addresses
     */
    public function getBannedIpAddresses(): array
    {
        $userIps = User::where('is_banned', true)
            ->whereNotNull('ip_address')
            ->pluck('ip_address')
            ->toArray();

        $historyIps = BanHistory::where('ban_type', 'ip_range')
            ->where('action', 'banned')
            ->whereNotNull('ip_address')
            ->pluck('ip_address')
            ->toArray();

        return array_unique(array_merge($userIps, $historyIps));
    }

    /**
     * Import bans from external list
     */
    public function importBans(array $bans, string $source, ?User $admin = null): int
    {
        $imported = 0;

        foreach ($bans as $ban) {
            // Expected format: ['type' => 'steam_id|hardware|ip', 'value' => '...', 'reason' => '...']
            match ($ban['type']) {
                'steam_id' => $this->importSteamIdBan($ban['value'], $ban['reason'] ?? "Imported from {$source}", $admin),
                'hardware' => $this->banByHardwareId($ban['value'], $ban['reason'] ?? "Imported from {$source}", $admin),
                'ip' => $this->banByIpAddress($ban['value'], $ban['reason'] ?? "Imported from {$source}", $admin),
                default => null,
            };

            $imported++;
        }

        return $imported;
    }

    /**
     * Import ban by Steam ID
     */
    private function importSteamIdBan(string $steamId, string $reason, ?User $admin = null): void
    {
        $user = User::where('steam_id', $steamId)->first();

        if ($user && !$user->is_banned) {
            $this->banPermanently($user, $reason, $admin);
        }
    }
}
